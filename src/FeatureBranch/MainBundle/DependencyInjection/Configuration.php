<?php

namespace FeatureBranch\MainBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('feature_branch_main');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->scalarNode('apache_root')
                    ->defaultValue('/var/www')
                    ->info('Folder where we deploy projects.')
                    ->example('/var/www')
                ->end()
                ->scalarNode('repo_origin')
                    ->defaultValue('http://git.drupal.org/project/drupal.git')
                    ->info('This is original repo we should pull the code from.')
                    ->example('http://git.drupal.org/project/drupal.git')
                ->end()
                ->scalarNode('work_filepath')
                    ->defaultValue('/tmp')
                    ->info('Writable folder where we will clone repo to and store some configuration files')
                    ->example('/tmp')
                ->end()
                ->scalarNode('ci_url')
                    ->defaultValue('http://192.168.56.101.xip.io:8080')
                    ->info('URL of the Continuous Integration server')
                    ->example('http://192.168.56.101.xip.io:8080')
                ->end()
                ->scalarNode('mysql_root_login')
                    ->defaultValue('root')
                    ->info('Login of the root mysql user')
                    ->example('root')
                ->end()
                ->scalarNode('mysql_root_pass')
                    ->defaultValue('root')
                    ->info('Password of the root mysql user')
                    ->example('root')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
