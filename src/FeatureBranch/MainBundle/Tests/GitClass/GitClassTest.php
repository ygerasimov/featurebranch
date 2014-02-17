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

    $ci = $this->getMock('\FeatureBranch\MainBundle\CI\CILogger', array('updateBranch', 'deleteBranch'));

    // Initial check state.
    $git = new GitClass($ci, $this->origin, $this->destination, $this->state_filename);
    $git->checkState();
  }

  /**
   * Delete repository.
   */
  protected function tearDown() {
    exec('rm -rf ' . $this->origin);
    parent::tearDown();
  }

  /**
   * Make sure our states file has two branches.
   */
  public function testListBranchesInStateFile() {
    $state_file_content = file_get_contents($this->state_filename);

    $this->assertContains('master', $state_file_content);
    $this->assertContains('testbranch1', $state_file_content);
  }

  /**
   * Test if branch got updated if commit in that branch received.
   */
  public function testUpdateBranches() {
    
    // Commit to one branch only.
    exec($this->getCommandCommitToBranch('master'));

    $ci = $this->getMock('\FeatureBranch\MainBundle\CI\CILogger', array('updateBranch', 'deleteBranch'));
    $ci->expects($this->once())
        ->method('updateBranch')
        ->with('origin/master');
    
    $git = new GitClass($ci, $this->origin, $this->destination, $this->state_filename);
    $git->checkState();

    // Commit to two branches at the same time.
    exec($this->getCommandCommitToBranch('master'));
    exec($this->getCommandCommitToBranch('testbranch1'));
    
    $ci = $this->getMock('\FeatureBranch\MainBundle\CI\CILogger', array('updateBranch', 'deleteBranch'));
    $ci->expects($this->at(0))
        ->method('updateBranch')
        ->with('origin/master');
    $ci->expects($this->at(1))
        ->method('updateBranch')
        ->with('origin/testbranch1');
    $git = new GitClass($ci, $this->origin, $this->destination, $this->state_filename);
    $git->checkState();

  }

  protected function getCommandCommitToBranch($branch) {
    return 'cd ' . $this->origin . ' && git checkout ' . $branch
      . ' && echo "test" >> foo.txt && git add .'
      . ' && git commit -m "commit to ' . $branch . '"';
  }

  public function testDeleteBranch() {
    $delete_branch_command = 'cd ' . $this->origin . ' && git checkout master && git branch -D testbranch1';
    exec($delete_branch_command);
    
    $ci = $this->getMock('\FeatureBranch\MainBundle\CI\CILogger', array('updateBranch', 'deleteBranch'));
    $ci->expects($this->once())
        ->method('deleteBranch')
        ->with('origin/testbranch1');

    $git = new GitClass($ci, $this->origin, $this->destination, $this->state_filename);
    $git->checkState();
  }
}
