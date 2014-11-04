<?php

namespace Genj\GoogleDriveBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SyncCommand
 *
 * @package Genj\GoogleDriveBundle\Command
 */
class SyncCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this->setName('genj:google-drive:sync')
             ->setDescription('Sync files with Google Drive');
    }

    /**
     * @param InputInterface   $input
     * @param OutputInterface $output
     *
     * @throws InvalidConfigurationException
     * @see Command
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if we have the API key
        $rootDir    = $this->getContainer()->getParameter('kernel.root_dir');
        $configDir  = $rootDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        $apiKeyFile = $configDir . $this->getContainer()->getParameter('genj_google_drive.service_account_key_file');
        if (!file_exists($apiKeyFile)) {
            throw new InvalidConfigurationException('Store your Google API key in ' . $apiKeyFile . ' - see https://code.google.com/apis/console');
        }

        // Perform API authentication
        $apiKeyFileContents  = file_get_contents($apiKeyFile);
        $serviceAccountEmail = $this->getContainer()->getParameter('genj_google_drive.service_account_email');
        $auth    = new \Google_Auth_AssertionCredentials(
            $serviceAccountEmail,
            array('https://www.googleapis.com/auth/drive'),
            $apiKeyFileContents
        );
        $client = new \Google_Client();
        $client->setAssertionCredentials($auth);
        $service = new \Google_Service_Drive($client);

        // Check if the upload path exists
        $uploadPath = $this->getContainer()->getParameter('genj_google_drive.upload_path');
        $uploadPath = $rootDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . $uploadPath . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadPath)) {
            throw new InvalidConfigurationException('Upload path does not exist or is not a directory: ' . $uploadPath);
        }

        // Get local and Google Drive file listings to prepare for the sync
        $filesLocal       = $this->retrieveLocalFilesList($uploadPath);
        $filesGoogleDrive = $service->files->listFiles();

        // Download new files
        $filesGoogleDriveFilenames = array();
        foreach ($filesGoogleDrive as $file) {
            /** @var \Google_Service_Drive_DriveFile $file */
            $filename                    = $file->getOriginalFilename();
            $filesGoogleDriveFilenames[] = $filename;
            if ($filename) {
                if (!in_array($filename, $filesLocal)) {
                    $downloadUrl = $file->getDownloadUrl();
                    if ($downloadUrl) {
                        $request     = new \Google_Http_Request($downloadUrl, 'GET', null, null);
                        $httpRequest = $client->getAuth()->authenticatedRequest($request);
                        if ($httpRequest->getResponseHttpCode() == 200) {
                            file_put_contents($uploadPath . $filename, $httpRequest->getResponseBody());
                            $output->writeln('saved new file - ' . $filename);
                        } else {
                            $output->writeln('request to Google Drive failed with status ' . $httpRequest->getResponseHttpCode());
                        }
                    }
                } else {
                    $output->writeln('already exists, ignoring - ' . $filename);
                }
            }
        }

        // Delete local files which are no longer on Google Drive
        foreach ($filesLocal as $filename) {
            if (!in_array($filename, $filesGoogleDriveFilenames)) {
                unlink($uploadPath . $filename);
                $output->writeln('deleted file which was no longer on google drive - ' . $filename);
            }
        }
    }

    /**
     * @param string $uploadPath
     *
     * @return array
     */
    protected function retrieveLocalFilesList($uploadPath)
    {
        $filesLocal = array();

        if ($handle = opendir($uploadPath)) {
            while (false !== ($entry = readdir($handle))) {
                if (is_file($uploadPath . $entry) && !($entry === '.' || $entry === '..')) {
                    $filesLocal[] = $entry;
                }
            }

            closedir($handle);
        }

        return $filesLocal;
    }
}
