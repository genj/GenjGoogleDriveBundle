<?php

namespace Genj\GoogleDriveBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @package Genj\GoogleDriveBundle\DependencyInjection
 */
class GenjGoogleDriveExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration          = new Configuration();
        $processedConfiguration = $this->processConfiguration($configuration, $configs);
        $container->setParameter('genj_google_drive.service_account_key_file', $processedConfiguration['service_account_key_file']);
        $container->setParameter('genj_google_drive.service_account_email', $processedConfiguration['service_account_email']);
        $container->setParameter('genj_google_drive.upload_path', $processedConfiguration['upload_path']);
    }
}