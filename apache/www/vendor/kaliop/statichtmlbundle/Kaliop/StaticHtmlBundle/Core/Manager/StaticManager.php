<?php

namespace Kaliop\StaticHtmlBundle\Core\Manager;

use Symfony\Component\Filesystem\Filesystem;


class StaticManager
{

    const VIEWS_ROOT = 'static-html';

    /**
     * Remove leftover trailing slash and prepends the StaticHtml views folder name
     * @return string "static-html[/folder[/subfolder]]"
     */
    static function makeDirPath($dir)
    {
        // Shouldn't have .. anyway, but let's be cautious
        $elements = explode('/', str_replace('..', '', $dir));
        array_unshift($elements, self::VIEWS_ROOT);
        return implode('/', array_filter($elements));
    }

    /**
     * Gets the Media type for an extension, using a limited list
     * with common use cases only. Defaults to text/plain.
     * @param  string  $ext - lowercase file extension
     * @return string  The corresponding media type
     */
    static function getMediaType($ext)
    {
        $type = 'text/plain';
        $knownTypes = array(
            'htm'  => 'text/html',
            'html' => 'text/html',
            'css'  => 'text/css',
            'js'   => 'text/javascript',
            'json' => 'application/json',
            'svg'  => 'image/svg+xml',
            'xml'  => 'application/xml',
        );
        if (array_key_exists($ext, $knownTypes)) {
            $type = $knownTypes[$ext];
        }
        return $type;
    }

    /**
     * Get Assetic bundles that have a static-html folder, and optionnally
     * filter down to a single bundle name.
     * @return array  A list of bundle names (may be empty)
     */
    static function getStaticBundles($container, $bundleRef=false)
    {
        $fs = new Filesystem();

        $htmlPath = 'Resources/views/' . self::VIEWS_ROOT;
        $asseticBundles = $container->getParameter('assetic.bundles');
        sort($asseticBundles);

        $matched = array();

        foreach ($asseticBundles as $bundleName) {
            $bundlePath = $container->get('kernel')->locateResource('@'.$bundleName);
            if ($fs->exists($bundlePath . $htmlPath)) {
                $matched[] = $bundleName;
            }
        }

        // Bundle identifier might have hyphens or underscores
        // Optionnally filter down to the a single bundle name
        if ($bundleRef && !empty($matched)) {
            $cleanRef = preg_replace("/[-_]/", '', $bundleRef);
            $reduced = preg_grep('/'.$cleanRef.'/i', $matched);
            $matched = array_slice($reduced, 0, 1);
        }

        return $matched;
    }

}
