<?php

namespace Genj\GoogleDriveBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @package Genj\GoogleDriveBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('genj_google_drive');

        $rootNode
            ->children()
                ->scalarNode('service_account_key_file')
                    ->info('Service account key file. See https://code.google.com/apis/console')
                    ->cannotBeEmpty()
                    ->defaultValue('%genj_google_drive.service_account_key_file%')
                ->end()
                ->scalarNode('service_account_email')
                    ->info('Service account e-mail address. See https://code.google.com/apis/console')
                    ->cannotBeEmpty()
                    ->defaultValue('%genj_google_drive.service_account_email')
                ->end()
                ->scalarNode('upload_path')
                    ->info('Upload path relative to web/ folder.')
                    ->cannotBeEmpty()
                    ->defaultValue('uploads/genjgoogledrive')
                ->end()
            ->end();

        return $treeBuilder;
    }
}

