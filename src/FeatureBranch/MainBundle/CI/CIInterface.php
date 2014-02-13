<?php

/**
 * Continuous Integration interface.
 */
namespace FeatureBranch\MainBundle\CI;

interface CIInterface {
  /**
   * Update host that deploys branch.
   */
  public function updateBranch($branch);

  /**
   * Delete the host that deploys the branch.
   */
  public function deleteBranch($branch);

  /**
   * Deploy new host for the branch.
   */
  public function createHost($branch);
}
