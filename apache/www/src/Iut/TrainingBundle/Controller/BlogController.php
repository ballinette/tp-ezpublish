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
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd( [
            new Criterion\Subtree( [ $location->pathString ] ),
            new Criterion\ContentTypeIdentifier( 'blog_post' )
        ] );
        $query->sortClauses[] = new SortClause\DatePublished( Query::SORT_DESC );

        $searchResult = $this->getRepository()
            ->getSearchService()
            ->findContent( $query );

        $listLocation = [];
        if ( $searchResult->totalCount )
        {
            foreach ( $searchResult->searchHits as $hit )
            {
                $listLocation[] = $this->getRepository()
                    ->getLocationService()
                    ->loadLocation(
                        $hit->valueObject->versionInfo->contentInfo->mainLocationId
                    );
            }
        }
        return $this->render(
            'IutTrainingBundle:full:blog.html.twig',
            [ 'locations' => $listLocation, 'content' => $location->getContentInfo() ]
        );


    }
}
