<?php

/*
 * Logs all jobs intead of triggering jobs in real CI system. To be used for debugging.
 */

namespace FeatureBranch\MainBundle\Service\CI;

use FeatureBranch\MainBundle\Service\CI\CIInterface;

/**
 * Description of CILogger
 *
 * @author ygerasimov
 */
class CILogger implements CIInterface {
  protected $logfile = '/tmp/cilogger.txt';

  public function updateBranch($branch) {
    $this->log('Update ' . $branch);
  }

  /**
   * Delete the host that deploys the branch.
   */
  public function deleteBranch($branch) {
    $this->log('Delete ' . $branch);
  }

  /**
   * Deploy new host for the branch.
   */
  public function createHost($branch) {
    $this->log('Deploy ' . $branch);
  }

  protected function log($string) {
    file_put_contents($this->logfile, $string . PHP_EOL, FILE_APPEND);
  }
}
