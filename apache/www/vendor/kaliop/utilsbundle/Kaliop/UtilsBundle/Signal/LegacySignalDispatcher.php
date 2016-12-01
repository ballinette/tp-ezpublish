<?php

namespace Kaliop\UtilsBundle\Signal;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\CopyContentSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\CreateContentSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteContentSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\UpdateContentSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\CopySubtreeSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\CreateLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\HideLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\UnhideLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\SetContentStateSignal;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use Monolog\Logger;

/**
 * @todo typehing against PSR Log, not monologger...
 */
class LegacySignalDispatcher
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SignalDispatcher
     */
    private $signalDispatcher;

    /**
     * @param Logger           $logger
     * @param SignalDispatcher $signalDispatcher
     */
    public function __construct(Logger $logger, SignalDispatcher $signalDispatcher)
    {
        $this->logger = $logger;
        $this->signalDispatcher = $signalDispatcher;
    }

    public function dispatchCopyContent()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy CopyContentSignal', $args);

        $this->signalDispatcher->emit(
            new CopyContentSignal(
                array(
                    'srcContentId' => $args['srcContentId'],
                    'srcVersionNo' => $args['srcVersionNo'],
                    'dstContentId' =>$args['dstContentId'],
                    'dstVersionNo' => $args['dstVersionNo'],
                    'dstParentLocationId' => $args['dstParentLocationId'],
                )
            )
        );
    }

    public function dispatchDeleteContent()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy DeleteContentSignal', $args);

        $this->signalDispatcher->emit(
            new DeleteContentSignal(
                array(
                    'contentId' => $args['contentId'],
                )
            )
        );
    }

    public function dispatchDeleteLocation()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy DeleteLocationSignal', $args);

        $this->signalDispatcher->emit(
            new DeleteLocationSignal(
                array(
                    'contentId' => $args['contentId'],
                    'locationId' => $args['locationId']
                )
            )
        );
    }

    public function dispatchHideLocation()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy HideLocationSignal', $args);

        $this->signalDispatcher->emit(
            new HideLocationSignal(
                array(
                    'locationId' => $args['locationId'],
                    'contentId' => $args['contentId'],
                    'currentVersionNo' => $args['currentVersionNo'],
                )
            )
        );
    }

    public function dispatchUnhideLocation()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy UnhideLocationSignal', $args);

        $this->signalDispatcher->emit(
            new UnhideLocationSignal(
                array(
                    'locationId' => $args['locationId'],
                    'contentId' => $args['contentId'],
                    'currentVersionNo' => $args['currentVersionNo'],
                )
            )
        );
    }

    public function dispatchCreateLocation()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy CreateLocationSignal', $args);

        $this->signalDispatcher->emit(
            new CreateLocationSignal(
                array(
                    'contentId' => $args['contentId'],
                    'locationId' => $args['locationId']
                )
            )
        );
    }

    public function dispatchPublishContent()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy PublishVersionSignal', $args);

        $this->signalDispatcher->emit(
            new PublishVersionSignal(
                array(
                    'contentId' => $args['contentId'],
                    'versionNo' => $args['versionNo'],
                )
            )
        );
    }

    public function dispatchCreateContent()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy CreateContentSignal', $args);

        $this->signalDispatcher->emit(
            new CreateContentSignal(
                array(
                    'contentId' => $args['contentId'],
                    'versionNo' => $args['versionNo']
                )
            )
        );
    }

    public function dispatchUpdateContent()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy UpdateContentSignal', $args);

        $this->signalDispatcher->emit(
            new UpdateContentSignal(
                array(
                    'contentId' => $args['contentId'],
                    'versionNo' => $args['versionNo']
                )
            )
        );
    }

    public function dispatchCopySubtreeLocation()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy CopySubtreeLocationSignal', $args);

        $this->signalDispatcher->emit(
            new CopySubtreeSignal(
                array(
                    'subtreeId' => $args['subtreeId'],
                    'targetParentLocationId' => $args['targetParentLocationId'],
                    'targetNewSubtreeId' => $args['targetNewSubtreeId'],
                )
            )
        );
    }

    public function dispatchMoveLocation()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy MoveLocationSignal', $args);

        $this->signalDispatcher->emit(
            new MoveSubtreeSignal(
                array(
                    'locationId' => $args['locationId'],
                    'newParentLocationId' => $args['newParentLocationId'],
                )
            )
        );
    }

    public function dispatchStateAssign()
    {
        $args= func_get_args()[0];
        $this->logger->addWarning('===========> Dispatching Legacy StateAssignSignal', $args);

        $this->signalDispatcher->emit(
            new SetContentStateSignal(
                array(
                    'contentId' => $args['contentId'],
                    'objectStateGroupId' => $args['objectStateGroupId'],
                    'objectStateId' => $args['objectStateId'],
                )
            )
        );
    }
}
