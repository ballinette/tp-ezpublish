<?php

namespace Kaliop\StaticHtmlBundle\Controller;

use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Kaliop\StaticHtmlBundle\Core\Manager\StaticManager;


class StaticHtmlController extends Controller
{

    /**
     *  List Assetic bundles
     *  (We're not checking whether each bundle has a static-html dir, though)
     */
    public function listAction()
    {
        $manager = new StaticManager();
        $staticBundles = $manager->getStaticBundles($this->container);

        return $this->render('KaliopStaticHtmlBundle::list.html.twig', array(
            'bundleNames' => $staticBundles
        ));
    }

    /**
     *  List templates and folders in the bundle's static-html folder
     */
    public function indexAction($bundleRef, $templateDir)
    {
        $manager = new StaticManager();
        $bundleList = $manager->getStaticBundles($this->container, $bundleRef);

        // No valid bundle found, redirect to list of bundles
        if (!$bundleList) {
            $url = $this->generateUrl('static_html_list');
            $url .= '?was=' . $bundleRef;
            $response = $this->redirect($url, 302);
            return $response;
        } else {
            $bundleName = $bundleList[0];
        }

        // Find the requested folder
        $folder = $manager->makeDirPath($templateDir);
        $breadcrumbs = explode('/', $folder);
        $folderRef = '@'.$bundleName.'/Resources/views/'.$folder;
        $folderNotFound = false;

        $dirNames = array();
        $fileNames = array();

        // Show the root folder if requested folder doesn't exist
        try {
            $systemPath = $this->container->get('kernel')->locateResource($folderRef);
            $finder = new Finder();
            $iterator = $finder->depth(0)->in($systemPath)->sortByName();
            foreach ($iterator->directories() as $dir) {
                $dirNames[] = $dir->getFileName();
            }
            foreach ($iterator->files()->name('*.*.twig') as $file) {
                $fileNames[] = str_replace('.twig', '', $file->getFileName());
            }
        }
        catch(\InvalidArgumentException $e) {
            if (strpos($e->getMessage(), $folderRef) !== false) {
                $folderNotFound = $folder;
            } else {
                throw $e;
            }
        }

        $response = $this->render('KaliopStaticHtmlBundle::index.html.twig', array(
            'bundle' => $bundleName,
            'breadcrumbs' => $breadcrumbs,
            'dirNames' => $dirNames,
            'fileNames' => $fileNames,
            'folderNotFound' => $folderNotFound
        ));
        if ($folderNotFound) {
            $response->setStatusCode(404);
        }
        return $response;
    }

    /**
     *  Serve a static Twig template from 'views/static-html',
     *  using the bundles in config assetic.bundles
     */
    public function pageAction($bundleRef, $templateDir, $templateName, $templateExt)
    {
        $manager = new StaticManager();

        $templateExt = str_replace('.', '', $templateExt);
        if ($templateExt === '') { $templateExt = 'html'; }
        $contentType = $manager->getMediaType($templateExt) . ';charset=utf-8';
        $dirPath = $manager->makeDirPath($templateDir);
        $bundleList = $manager->getStaticBundles($this->container, $bundleRef);

        // No valid bundle found, redirect to list of bundles
        if (!$bundleList) {
            $url = $this->generateUrl('static_html_list');
            $url .= '?was=' . $bundleRef;
            $response = $this->redirect($url, 302);
            return $response;
        } else {
            $bundleName = $bundleList[0];
        }

        // Symfony logical path of the template to render
        $logicalPath = "$bundleName:$dirPath:$templateName.$templateExt.twig";

        // Render template if found,
        // redirect to the parent folder's index page if not
        try {
            $response = $this->render($logicalPath);
            $response->headers->set('Content-Type', $contentType);
            return $response;
        }
        catch(\InvalidArgumentException $e) {
            if (strpos($e->getMessage(), $logicalPath) !== false) {
                $url = $this->generateUrl('static_html_index', array(
                    'bundleRef' => $bundleRef,
                    'templateDir' => $templateDir
                ));
                $url .= "?was=$templateName." . $templateInfo['ext'];
                $response = $this->redirect($url, 302);
                return $response;
            }
            else {
                throw $e;
            }
        }

    }

}
