<?php

namespace FeatureBranch\MainBundle\CI;

use FeatureBranch\MainBundle\CI\CIInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Description of JenkinsConnector
 *
 * @author ygerasimov
 */
class JenkinsConnector extends ContainerAware implements CIInterface {

    public function updateBranch($branch) {
        $phing_config = $this->container->get('templating')->render(
            'MainBundle:phing.build.update.xml.twig', [
            'branch' => $branch,
        ]);

        $phing_filename = '/tmp/phing_update_' . rand(0, 10000) . '.xml';
        file_put_contents($phing_filename, $phing_config);

        $jenkins_config = $this->container->get('templating')->render(
            'MainBundle:jenkins.config.xml.twig', [
            'phing_config_filename' => $phing_filename,
        ]);

        $job_name = 'update-branch-' . $branch;
        $this->createJenkinsJob($job_name, $jenkins_config);
    }

    public function deleteBranch($branch) {

    }

    public function createHost($branch) {

    }

    protected function createJenkinsJob($job_name, $config_file) {
        $url = 'http://featurebranch.dev:8080/createItem?name=' . url_encode($job_name);

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/xml\r\n",
                'method'  => 'POST',
                'content' => $config_file,
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    }

}
