<?php

namespace Kaliop\UtilsBundle\EventListener;

use eZ\Publish\Core\MVC\Legacy\Event\PostBuildKernelEvent;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use Kaliop\UtilsBundle\Signal\LegacySignalDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Closure;
use ezpKernelHandler;

class LegacyKernelListener implements EventSubscriberInterface
{
    /**
     * @var \Closure|\ezpKernelHandler
     */
    private $legacyKernel;

    /**
     * @var LegacySignalDispatcher
     */
    private $legacySignalDispatcher;

    /**
     * @var array
     */
    private $legacyEventsActivation;

    public function __construct(LegacySignalDispatcher $legacySignalDispatcher, $legacyEventsActivation, $legacyKernel)
    {
        $this->legacySignalDispatcher = $legacySignalDispatcher;
        $this->legacyEventsActivation = $legacyEventsActivation;

        if ($legacyKernel instanceof Closure || $legacyKernel instanceof ezpKernelHandler) {
            $legacyKernelClosure = $legacyKernel;
            $this->legacyKernel = $legacyKernelClosure();
        } else {
            throw new \RuntimeException('LegacyKernelListener only accepts $legacyKernel instance of Closure or ezpKernelHandler');
        }
    }


    public static function getSubscribedEvents()
    {
        return array(
            LegacyEvents::POST_BUILD_LEGACY_KERNEL => 'onKernelPostBuild'
        );
    }

    public function onKernelPostBuild( PostBuildKernelEvent $event )
    {
        $dispatcher = $this->legacySignalDispatcher;
        $listener = $this;

        try {
            $event->getLegacyKernel()->runCallback(
                function () use ($dispatcher, $listener)
                {
                    if($this->legacyEventsActivation['content_copy'])
                        \ezpEvent::getInstance()->attach('content/copy', array($dispatcher, 'dispatchCopyContent'));
                    if($this->legacyEventsActivation['content_delete'])
                        \ezpEvent::getInstance()->attach('content/delete', array($dispatcher, 'dispatchDeleteContent'));
                    if($this->legacyEventsActivation['content_publish'])
                        \ezpEvent::getInstance()->attach('content/publish', array($dispatcher, 'dispatchPublishContent'));
                    if($this->legacyEventsActivation['content_create'])
                        \ezpEvent::getInstance()->attach('content/create', array($dispatcher, 'dispatchCreateContent'));
                    if($this->legacyEventsActivation['content_update'])
                        \ezpEvent::getInstance()->attach('content/update', array($dispatcher, 'dispatchUpdateContent'));

                    if($this->legacyEventsActivation['location_add'])
                        \ezpEvent::getInstance()->attach('location/add', array($dispatcher, 'dispatchCreateLocation'));
                    if($this->legacyEventsActivation['location_delete'])
                        \ezpEvent::getInstance()->attach('location/delete', array($dispatcher, 'dispatchDeleteLocation'));
                    if($this->legacyEventsActivation['location_hide'])
                        \ezpEvent::getInstance()->attach('location/hide', array($dispatcher, 'dispatchHideLocation'));
                    if($this->legacyEventsActivation['location_unhide'])
                        \ezpEvent::getInstance()->attach('location/unhide', array($dispatcher, 'dispatchUnhideLocation'));
                    if($this->legacyEventsActivation['location_subtreecopy'])
                        \ezpEvent::getInstance()->attach('location/subtreecopy', array($dispatcher, 'dispatchCopySubtreeLocation'));
                    if($this->legacyEventsActivation['location_move'])
                        \ezpEvent::getInstance()->attach('location/move', array($dispatcher, 'dispatchMoveLocation'));

                    //Fix for updating priorities in Solr when updating priorities from back-office
                    \ezpEvent::getInstance()->attach('content/afterupdatepriority', array($listener, 'updateSolrPriority'));

                    //Trigger SetContentStateSignal
                    if($this->legacyEventsActivation['state_assign'])
                        \ezpEvent::getInstance()->attach('state/assign', array($dispatcher, 'dispatchStateAssign'));

                }, false
            );
        }
        catch(\Exception $e) //We need to catch RunTimeException because of ezpKernelTreeMenu not supporting runCallback()
        {

        }
    }

    /**
     * Run legacy registerSearchObject function on content/afterupdatepriority ezpEvent's $contentObjectID
     */
    public function updateSolrPriority()
    {
        $args= func_get_args();

        if(count($args) > 1)
        {
            $contentObjectID = $args[1];

            $this->legacyKernel->runCallback(
                function () use ($contentObjectID) {
                    \eZContentOperationCollection::registerSearchObject($contentObjectID);
                },
                false,
                false
            );
        }
    }

}
