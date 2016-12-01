<?php


namespace Kaliop\UtilsBundle\Twig;

use Twig_SimpleFilter;
use Twig_Extension;

class KaliopUtilsExtension extends Twig_Extension
{
    private $legacyKernel;

    public function __construct(\Closure $legacyKernel)
    {
        $this->legacyKernel = $legacyKernel;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'kalioputils_extension';
    }

    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('truncate_custom_html', array($this, 'truncateHTML')),

        );
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(

            new \Twig_SimpleFunction('get_image_size', array($this, 'getImageSize')),
            new \Twig_SimpleFunction('get_image_mime_type', array($this, 'getImageMimeType')),
        );
    }

    /**
     * Truncates an HTML string to a $limit chars without removing HTML tags
     * @param string $html
     * @param int $limit
     * @param string $endchar
     * @return string
     */
    public function truncateHTML($html, $limit, $endchar = '...')
    {
        $output = new TruncateHtmlString($html, $limit);
        return $output->cut($endchar);
    }

    public function getAbsoluteImagePath($relativePath){

        $closure    = $this->legacyKernel;

        $mntPath =  $closure()->runCallback(function(){

            $fileINI = \eZINI::instance('file.ini');

            return $fileINI->variable('eZDFSClusteringSettings','MountPointPath');
        });


        $varDir =  $closure()->runCallback(function(){

            $fileINI = \eZINI::instance('');

            return $fileINI->variable('FileSettings','VarDir');
        });


        $storageDir =  $closure()->runCallback(function(){

            $fileINI = \eZINI::instance('');

            return $fileINI->variable('FileSettings','StorageDir');
        });


        $parsedUri = parse_url($relativePath);
        $imageFinalPath = ltrim($parsedUri['path'],'/');

        if(!empty($mntPath)){
            $imageFinalPath = $mntPath . $parsedUri['path'];
        }

        return $imageFinalPath;


    }

    public function getImageSize($imagePath){

        $imageFinalPath = $this->getAbsoluteImagePath($imagePath);
        $imageSizeInfos = getimagesize($imageFinalPath);
        return array('width' => $imageSizeInfos[0], 'height' => $imageSizeInfos[1]);
    }

    public function getImageMimeType($imagePath)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo,$this->getAbsoluteImagePath($imagePath));
        finfo_close($finfo);
        return $mimeType;
    }

}