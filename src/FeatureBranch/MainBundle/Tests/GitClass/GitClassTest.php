<?php

namespace FeatureBranch\MainBundle\Tests\GitClass;

use FeatureBranch\MainBundle\GitClass;
use FeatureBranch\MainBundle\CI\CIInterface;

/**
 * Description of GitClassTest
 *
 * @author ygerasimov
 */
class GitClassTest extends \PHPUnit_Framework_TestCase {
  protected $origin;
  protected $destination;
  protected $state_filename;

  /**
   * Creates a repository.
   */
  protected function setUp() {
    parent::setUp();

    $rand = rand(0, 10000);

    $this->origin = '/tmp/repo_origin_' . $rand;
    $this->destination = '/tmp/repo_destination_' . $rand;
    $this->state_filename = '/tmp/repo_sate_' . $rand . '.yaml';

    $create_repo_command = 'mkdir ' . $this->origin . ' && cd ' . $this->origin
      . ' && touch foo.txt && git init && git add . && git commit -m "init commit"'
      . ' && echo "foo" >> foo.txt && git checkout -b testbranch1 && git add .'
      . ' && git commit -m "init commit to testbranch1"';
    exec($create_repo_command);
  }

  /**
   * Delete repository.
   */
  protected function tearDown() {
    exec('rm -rf ' . $this->origin);
    parent::tearDown();
  }

  public function testListBranchesInStateFile() {
    $ci = $this->getMock('\FeatureBranch\MainBundle\CI\CILogger', array('updateBranch', 'deleteBranch'));

    $git = new GitClass($ci, $this->origin, $this->destination, $this->state_filename);
    $git->checkState();

    $state_file_content = file_get_contents($this->state_filename);

    $this->assertContains('master', $state_file_content);
    $this->assertContains('testbranch1', $state_file_content);
  }
}
