<?php


namespace Kaliop\UtilsBundle\Helper;

use eZ\Bundle\EzPublishCoreBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class SfCacheHelper
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $kernelCacheDir;

    /**
     * @var string
     */
    private $kernelContainerClass;

    /**
     * @param Kernel $kernel
     * @param Filesystem $filesystem
     * @param $kernelCacheDir
     * @param $kernelContainerClass
     */
    public function __construct(Kernel $kernel,
        Filesystem $filesystem,
        $kernelCacheDir,
        $kernelContainerClass)
    {
        $this->kernel = $kernel;
        $this->fileSystem = $filesystem;
        $this->kernelCacheDir = $kernelCacheDir;
        $this->kernelContainerClass = $kernelContainerClass;
    }

    /**
     * Clear symfony/ez cache
     *
     * @throws \Exception
     */
    public function clearCache()
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'cache:clear'
        ));

        $application->run($input, new NullOutput());
    }

    /**
     * clear project container cache & invalidate opcache is opcache is used
     */
    public function clearCacheProjectContainer()
    {
        $containerClassPath = $this->kernelCacheDir . '/' . $this->kernelContainerClass . '.php';
        if (function_exists("opcache_invalidate")) {
            opcache_invalidate($containerClassPath, true);
        }
        $this->fileSystem->remove($containerClassPath);
    }
}