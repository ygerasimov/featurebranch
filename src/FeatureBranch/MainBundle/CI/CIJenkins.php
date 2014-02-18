<?php

/*
 * Use Jenkins to run all tasks.
 */

namespace FeatureBranch\MainBundle\CI;

use FeatureBranch\MainBundle\CI\CIInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

/**
 * Description of CIJenkins
 *
 * @author ygerasimov
 */
class CIJenkins implements CIInterface {
  protected $config_file;
  protected $connector;

  public function __construct($filepath, CIInterface $connector) {
    $this->config_file = $filepath;
    $this->connector = $connector;
  }

  public function updateBranch($branch) {
    $config = $this->getHostsConfig();

//    if (isset($config[$branch]) && $config[$branch]) {
      $this->connector->updateBranch($branch);
//    }
  }

  /**
   * Delete the host that deploys the branch.
   */
  public function deleteBranch($branch) {
    $config = $this->getHostsConfig();

    if (isset($config[$branch]) && $config[$branch]) {
      $this->connector->deleteBranch($branch);

      unset($config[$branch]);
      $this->saveHostsConfig($config);
    }
  }

  /**
   * Deploy new host for the branch.
   */
  public function createHost($branch) {
    $this->connector->createHost($branch);
    
    $config = $this->getHostsConfig();
    $config[$branch] = $branch;
    $this->saveHostsConfig($config);
  }

  /**
   * Retrieve data about hosts configuration (what branches are deployed).
   */
  protected function getHostsConfig() {
    $parser = new Parser();
    if (!file_exists($this->config_file)) {
      return array();
    }
    $hosts = $parser->parse(file_get_contents($this->config_file));
    return (array) $hosts;
  }

  protected function saveHostsConfig($config) {
    $dumper = new Dumper();
    $file_output = $dumper->dump($config);
    file_put_contents($this->config_file, $file_output);
  }
}
