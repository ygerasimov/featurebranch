<?php

namespace FeatureBranch\MainBundle\Tests\GitClass;

use FeatureBranch\MainBundle\GitClass;

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
      . ' && touch foo.txt && git init && git add . && git commit -m "init commit"';
    exec($create_repo_command);
  }

  /**
   * Delete repository.
   */
  protected function tearDown() {
    exec('rm -rf ' . $this->origin);
    parent::tearDown();
  }

  public function testFirst() {
    $this->assertTrue(1 > 0);
  }
  //put your code here
}
