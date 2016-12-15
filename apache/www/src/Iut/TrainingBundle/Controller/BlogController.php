<?php

namespace Iut\TrainingBundle\Controller;


use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Repository\Values\Content\Location;


class BlogController extends Controller
{

    public function indexAction( $locationId, $viewType, $layout = false, array $params = [] )
    {
        $repository = $this->getRepository();
        $location = $repository->getLocationService()->loadLocation( $locationId );

        $listLocation = $this->container->get('kalioputils.helper.location')->loadLocationChildren(
            $location,
            ['blog_post'],
            [new SortClause\DatePublished( Query::SORT_DESC )]
        );
        return $this->render(
            'IutTrainingBundle:full:blog.html.twig',
            [ 'locations' => $listLocation->locations, 'content' => $location->getContentInfo() ]
        );

    }

    public function blogpostAction( $locationId, $viewType, $layout = false, array $params = [] )
    {
        $repository     = $this->getRepository();

        $location       = $repository->getLocationService()->loadLocation( $locationId );
        $ownerId        = $location->contentInfo->ownerId;
        $owner          = $repository->getUserService()->loadUser( $ownerId );

        $params += [ 'owner' => $owner ];

        $response = $this->container->get( "ez_content" )
            ->viewLocation( $locationId, $viewType, $layout, $params );

        return $response;
    }

}
