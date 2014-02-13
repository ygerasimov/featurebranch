<?php

namespace FeatureBranch\MainBundle;

use FeatureBranch\MainBundle\CI\CIInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Description of GitClass
 *
 * @author ygerasimov
 */
class GitClass {
  protected $origin = '/tmp/repo_origin';
  protected $destination = '/tmp/repo_destination';
  protected $state_filename = '/tmp/repo_state.yaml';
  protected $ci;

  public function __construct(CIInterface $ci) {
    $this->ci = $ci;
  }

  /**
   * Check the current state of the repo. Pull and compare if new commits
   * were received.
   */
  public function checkState() {
    $this->pull();

    $previous_state = $this->parseState();
    $previous_state_branches = array_keys($previous_state);

    $current_state = $this->getCurrentState();
    $current_state_branches = array_keys($current_state);

    $changed_branches = array();
    $deleted_branches = array_diff($previous_state_branches, $current_state_branches);
    
    foreach (array_intersect($previous_state_branches, $current_state_branches) as $branch) {
      if ($previous_state[$branch] != $current_state[$branch]) {
        $changed_branches[] = $branch;
      }
    }

    foreach ($changed_branches as $branch) {
      $this->ci->updateBranch($branch);
    }

    foreach ($deleted_branches as $branch) {
      $this->ci->deleteBranch($branch);
    }

    $this->saveState($current_state);
  }

  /**
   * Checks state of the repo from git command.
   */
  protected function getCurrentState() {
    $command = 'git fetch > /dev/null 2>&1 && for branch in `git branch -r | grep -v HEAD | cut -d":" -f2`;do echo -e `git show --format="%h" $branch | head -n 1` \| $branch; done';
    $output = array();
    exec('cd ' . $this->destination . ' && ' . $command, $output);

    $state = array();
    foreach ($output as $output_line) {
      list($commit, $branch) = explode(' | ', $output_line);

      // This is very weird but executing command via exec() adds '-e ' in the 
      // beginning of the line. Removing it manually here.
      if (strpos($commit, '-e ') !== FALSE) {
        $commit = str_replace('-e ', '', $commit);
      }
      
      $state[$branch] = $commit;
    }

    return $state;
  }

  /**
   * Parse state yaml file.
   */
  protected function parseState() {
    $state = array();
    
    $parser = new Parser();
    try {
      $state = $parser->parse(file_get_contents($this->state_filename));
    }
    catch (Exception $e) {
      printf("Unable to parse the YAML string: %s", $e->getMessage());
    }

    return (array) $state;
  }

  /**
   * Save state of the git repo to yaml file.
   */
  protected function saveState($state) {
    $dumper = new Dumper();
    $file_output = $dumper->dump($state);
    file_put_contents($this->state_filename, $file_output);
  }

  /**
   * Git pull.
   */
  public function pull() {
    if (!file_exists($this->destination)) {
      $this->initialClone();
    }

    exec('cd ' . $this->destination . ' && git pull');
  }

  /**
   * Git clone.
   */
  protected function initialClone() {
    exec('git clone ' . $this->origin . ' ' . $this->destination);
  }
}
