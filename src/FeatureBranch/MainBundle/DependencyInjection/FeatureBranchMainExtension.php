<?php

namespace FeatureBranch\MainBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FeatureBranchMainExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter(
            'feature_branch.ci_url',
            $config['ci_url']
        );

        $filepath = $config['work_filepath'];
        $container->setParameter(
            'feature_branch.branches_hosts_config_file',
            $filepath . '/branches_hosts.yaml'
        );
        $container->setParameter(
            'feature_branch.repo_destination',
            $filepath . '/repo_destination'
        );
        $container->setParameter(
            'feature_branch.repo_state_config_file',
            $filepath . '/repo_state.yaml'
        );

        $container->setParameter(
            'feature_branch.repo_origin',
            $config['repo_origin']
        );

        $container->setParameter(
            'feature_branch.apache_root',
            $config['apache_root']
        );

        $container->setParameter(
            'feature_branch.mysql_root_login',
            $config['mysql_root_login']
        );

        $container->setParameter(
            'feature_branch.mysql_root_pass',
            $config['mysql_root_pass']
        );
    }
}
