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
        ]);

        $branch = str_replace('/', '-', $branch);

        $job_name = 'update-branch-' . $branch;
        $this->createJenkinsJob($job_name, $jenkins_config);

        $this->triggerJenkinsBuild($job_name);
    }

    public function deleteBranch($branch) {

    }

    public function createHost($branch) {

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
