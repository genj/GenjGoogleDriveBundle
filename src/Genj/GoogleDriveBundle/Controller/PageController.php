<?php

namespace Genj\GoogleDriveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class PageController
 *
 * @package Genj\GoogleDriveBundle\Controller
 */
class PageController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function slideshowAction()
    {
        // Determine upload path
        $uploadPath = $this->container->getParameter('genj_google_drive.upload_path') . DIRECTORY_SEPARATOR;

        // Read images from the upload path
        $filesString = '["placeholder"]';
        $filesArray  = glob($uploadPath . '*\.{jpg,jpeg,png,gif,bmp,JPG,JPEG,PNG,GIF,BMP}', GLOB_BRACE);
        for ($ctr = 0; $ctr <= sizeof($filesArray); $ctr++) {
            if ($filesArray[$ctr]) {
                $filesString = '["' . $filesArray[$ctr] . '"],' . $filesString;
            }
        }

        return $this->render('GenjGoogleDriveBundle:Page:slideshow.html.twig', array(
                'baseUrl' => DIRECTORY_SEPARATOR . $uploadPath,
                'images'  => $filesString
            )
        );
    }
}