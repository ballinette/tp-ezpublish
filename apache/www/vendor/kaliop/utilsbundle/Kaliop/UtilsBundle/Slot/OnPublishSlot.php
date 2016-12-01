<?php

namespace Kaliop\UtilsBundle\Slot;

use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\MVC\Legacy\Kernel as Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OnPublishSlot extends BaseSlot
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    private $container;

    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\ContentService\PublishVersionSignal )
        {
            return;
        }

        $contentId = $signal->contentId;

        // We are going to call eZ publish legacy code by calling the legacy runCallBack function
        // For Commands
        // $legacyKernelClosure = $this->getContainer()->get( 'ezpublish_legacy.kernel' );
        // $legacyKernel = $legacyKernelClosure();
        // return $legacyKernel->runCallback( ....

        // The legacy runCallBack receives two params
        // $legacyKernel->runCallback->runCallback( [all the code], false );
        // the second parameter makes it not re-initialize the kernel every time

        $legacyKernelClosure = $this->container->get( 'ezpublish_legacy.kernel' );
        $legacyKernel = $legacyKernelClosure();

        $legacyKernel->runCallback(
            function () use ($contentId)
            {
                // Fetch the object by its ID
                $object = \eZContentObject::fetch( $contentId );

                // avoid fatal errors below
                if ( $object == null )
                {
                    return;
                }

                // Get the total number of versions
                $versionCount = $object->getVersionCount();
                // Get the version limit, so this object should have up to this number of versions
                $versionLimit = \eZContentClass::versionHistoryLimit( $object->attribute( 'content_class' ) );

                // @todo should we check that versionLimit is at least 2 ?

                // Check if the number of versions is lower than the max number of versions
                // If it is lower, we don't need to remove any existing version
                if ( $versionCount > $versionLimit )
                {

                    // Get the number of versions to remove
                    $versionToRemove = $versionCount - $versionLimit;
                    // Get the versions that should be removed
                    // offset is 0 and limit is the number of versions to remove
                    // so we are going to remove all the returned versions
                    $versions = $object->versions( true, array(
                        'conditions' => array( 'status' => \eZContentObjectVersion::STATUS_ARCHIVED ),
                        'sort' => array( 'modified' => 'asc' ),
                        'limit' => array( 'limit' => $versionToRemove, 'offset' => 0 ),
                    ) );

                    // Loop the versions array, removing all versions
                    $db = \eZDB::instance();
                    $db->begin();
                    foreach( $versions as $version )
                    {
                        $version->removeThis();
                    }
                    $db->commit();
                }
            }, false
        );
    }

}
