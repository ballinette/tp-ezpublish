<?php

namespace Kaliop\UtilsBundle\Classes\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Intercept onKernelRequest and set the locale according to the siteaccess
 * Class LocaleListener
 * @package Kaliop\UtilsBundle\Classes\EventListener
 */
class LocaleListener
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function onKernelRequest( GetResponseEvent $event )
    {
        $translator = $this->container->get('translator');

        $siteaccess = $this->container->get('ezpublish.siteaccess')->name;
        $twigGlobals = $this->container->get('twig')->getGlobals();
        if(isset($twigGlobals['siteaccess'][$siteaccess])){
            $locale = $twigGlobals['siteaccess'][$siteaccess]['locale'];
            $event->getRequest()->setLocale($locale);
            $translator->setLocale($locale);
        }
    }

    static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 17)),
        );
    }
}
