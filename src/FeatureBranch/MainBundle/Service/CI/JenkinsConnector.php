<?php

namespace FeatureBranch\MainBundle\Service\CI;

use FeatureBranch\MainBundle\Service\CI\CIInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Description of JenkinsConnector
 *
 * @author ygerasimov
 */
class JenkinsConnector extends ContainerAware implements CIInterface {

    protected $host;

    public function __construct($host) {
        $this->host = $host;
    }

    /**
     * Trigger the job to update the branch.
     *
     * @param string $branch
     */
    public function updateBranch($branch) {
        $phing_config = $this->container->get('templating')->render(
            'FeatureBranchMainBundle::phing.build.update.xml.twig', [
            'branch' => $branch,
        ]);

        $phing_filename = '/tmp/phing_update_' . rand(0, 10000) . '.xml';
        file_put_contents($phing_filename, $phing_config);

        $jenkins_config = $this->container->get('templating')->render(
            'FeatureBranchMainBundle::jenkins.config.xml.twig', [
            'phing_config_filename' => $phing_filename,
            'phing_config_tasks' => 'hello',
        ]);

        $job_name = 'update-branch-' . $branch;
        $this->createJenkinsJob($job_name, $jenkins_config);

        $this->triggerJenkinsBuild($job_name);
    }

    /**
     * Trigger the job to delete the host
     *
     * @param string $branch
     */
    public function deleteBranch($branch) {
        $apache_root = $this->container->getParameter('feature_branch.apache_root');
        $mysql_root_login = $this->container->getParameter('feature_branch.mysql_root_login');
        $mysql_root_pass = $this->container->getParameter('feature_branch.mysql_root_pass');

        $phing_config = $this->container->get('templating')->render(
            'FeatureBranchMainBundle::phing.build.delete.xml.twig', [
            'branch' => $branch,
            'apache_root' => $apache_root,
            'mysql_login' => $mysql_root_login,
            'mysql_pass' => $mysql_root_pass,
        ]);

        $rand = rand(0, 10000);
        $phing_filename = '/tmp/phing_delete_' . $rand . '.xml';
        file_put_contents($phing_filename, $phing_config);

        $jenkins_config = $this->container->get('templating')->render(
            'FeatureBranchMainBundle::jenkins.config.xml.twig', [
            'phing_config_filename' => $phing_filename,
            'phing_config_tasks' => 'delete_folder delete_db',
        ]);

        $job_name = 'delete-branch-' . $branch . '-' . $rand;
        $this->createJenkinsJob($job_name, $jenkins_config);

        $this->triggerJenkinsBuild($job_name);
    }

    /**
     * Trigger job to create host.
     *
     * @param string $branch
     */
    public function createHost($branch) {
        $apache_root = $this->container->getParameter('feature_branch.apache_root');
        $repo_origin = $this->container->getParameter('feature_branch.repo_origin');
        $mysql_root_login = $this->container->getParameter('feature_branch.mysql_root_login');
        $mysql_root_pass = $this->container->getParameter('feature_branch.mysql_root_pass');

        $phing_config = $this->container->get('templating')->render(
            'FeatureBranchMainBundle::phing.build.create.xml.twig', [
            'branch' => $branch,
            'apache_root' => $apache_root,
            'repo_origin' => $repo_origin,
            'mysql_login' => $mysql_root_login,
            'mysql_pass' => $mysql_root_pass,
        ]);

        $rand = rand(0, 10000);
        $phing_filename = '/tmp/phing_create_' . $rand . '.xml';
        file_put_contents($phing_filename, $phing_config);

        $jenkins_config = $this->container->get('templating')->render(
            'FeatureBranchMainBundle::jenkins.config.xml.twig', [
            'phing_config_filename' => $phing_filename,
            'phing_config_tasks' => 'git_clone files_directory copy_settings.php create_db',
        ]);

        $job_name = 'create-branch-' . $branch . '-' . $rand;
        $this->createJenkinsJob($job_name, $jenkins_config);

        $this->triggerJenkinsBuild($job_name);
    }

    protected function createJenkinsJob($job_name, $config_file) {
        $url = $this->host . '/createItem?name=' . urlencode($job_name);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $config_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml',
            'Content-Length: ' . strlen($config_file))
        );

        $result = curl_exec($ch);
    }

    protected function triggerJenkinsBuild($job_name) {
        $url = $this->host . '/job/' . urlencode($job_name) . '/build';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml')
        );

        $result = curl_exec($ch);
    }
}
