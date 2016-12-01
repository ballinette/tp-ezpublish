<?php

namespace Kaliop\UtilsBundle\Helper;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\SignalSlot\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;


class LocationHelper {

    protected $repository;

    /**
     * Constructor
     *
     * @param eZ\Publish\Core\SignalSlot\Repository
     */
    public function __construct(Repository $repository) {
        $this->repository = $repository;
    }

    /**
     * Loads children which are readable by the current user of a location object sorted by sortClauses and filteed by contentTypeIdentifiers
     * @param Location $location
     * @param array $contentTypeIdentifiers
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     * @param array $sectionIds array of section Ids to filter in search query
     * @param int $offset the start offset for paging
     * @param int $limit the number of locations returned. /!\ see SearchService $limit in eZ5.4.6 => default $limit goes to 4096 for compatibility
     * @param array $excludedContentTypeIdentifiers
     * @param array $languages
     * @param bool $visibility
     * @return LocationList
     */
    function loadLocationChildren(Location $location, array $contentTypeIdentifiers=array(), array $sortClauses=array(), array $sectionIds=null,
                                 $offset=0, $limit=4096, $excludedContentTypeIdentifiers = array(), $languages = array(), $visibility = true){

        $criterions = array();
        $criterions[] = new Criterion\ParentLocationId( $location->id );

        if ($visibility) {
            $criterions[] = new Criterion\Visibility( Criterion\Visibility::VISIBLE );
        }

        if (!empty($contentTypeIdentifiers)) {
            $criterions[]=new Criterion\ContentTypeIdentifier( $contentTypeIdentifiers );
        }

        if (!empty($excludedContentTypeIdentifiers)) {
            $criterions[] = new Criterion\LogicalNot(new Criterion\ContentTypeIdentifier($excludedContentTypeIdentifiers));
        }

        if ($sectionIds !== null) {
            $criterions[]=new Criterion\SectionId($sectionIds);
        }

        if (!empty($languages)) {
            $criterions[]=new Criterion\LanguageCode($languages);
        }

        $filter = new Criterion\LogicalAnd($criterions);

        $query = new LocationQuery(
            array(
                "filter" => $filter,
                "offset" => $offset >= 0 ? (int) $offset : 0,
                "limit" => $limit,
                "sortClauses" => empty($sortClauses) ?  array($this->getSortClauseBySortField($location)) : $sortClauses
            )
        );

        $searchResult = $this->repository->getSearchService()->findLocations( $query );

        $childLocations = array();
        foreach ( $searchResult->searchHits as $searchHit )
        {
            $childLocations[] = $searchHit->valueObject;
        }

        return new LocationList(
            array(
                "locations" => $childLocations,
                "totalCount" => $searchResult->totalCount
            )
        );
    }

    /**
     * Load the main location from its $contentId
     * @param int $contentId
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException
     * @return Location
     */
    public function loadMainLocationByContentId($contentId) {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        if ($contentInfo->mainLocationId === null) {
            throw new BadStateException( "\$contentInfo", "ContentInfo has no main location id" );
        }
        return $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
    }

    /**
     * Load the main locations from its $contentIds
     * @param int[] $contentIds
     * @return LocationList
     */
    public function loadMainLocationsByContentIds($contentIds) {
        $locations = array();
        foreach($contentIds as $contentId) {
            try{
                $location = $this->loadMainLocationByContentId($contentId);
                $locations[] = $location;
            } catch(BadStateException $e) {}
        }

        return new LocationList(array('locations' => $locations, 'totalCount' => count($locations)));
    }

    /**
     * Load the main location from its $remoteId
     * @param string $remoteId
     * @return Location
     */
    public function loadMainLocationByContentRemoteId($remoteId) {
        $contentInfo = $this->repository->getContentService()->loadContentInfoByRemoteId($remoteId);
        return $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
    }

    /**
     * Return ez SortClause (which can then be used in LocationQuery for example) depending on $location sortField
     *
     * @param APILocation $location
     *
     * @return SortClause\ContentId|SortClause\ContentName|SortClause\Location\Id|SortClause\Location\Path|SortClause\Location\Priority
     */
    public function getSortClauseBySortField(\eZ\Publish\API\Repository\Values\Content\Location $location )
    {
        $sortField = $location->sortField;
        if( $location->sortOrder == 1)
        {
            $sortOrder=Query::SORT_ASC;
        }
        else
        {
            $sortOrder=Query::SORT_DESC;
        }

        switch ( $sortField )
        {
            case APILocation::SORT_FIELD_PATH:
                return new SortClause\Location\Path( $sortOrder );

            case APILocation::SORT_FIELD_PUBLISHED:
                return new SortClause\DatePublished( $sortOrder );

            case APILocation::SORT_FIELD_MODIFIED:
                return new SortClause\DateModified( $sortOrder );

            case APILocation::SORT_FIELD_SECTION:
                return new SortClause\SectionIdentifier( $sortOrder );

            case APILocation::SORT_FIELD_DEPTH:
                return new SortClause\Location\Depth( $sortOrder );

            //@todo: sort clause not yet implemented
            // case APILocation::SORT_FIELD_CLASS_IDENTIFIER:

            //@todo: sort clause not yet implemented
            // case APILocation::SORT_FIELD_CLASS_NAME:

            case APILocation::SORT_FIELD_PRIORITY:
                return new SortClause\Location\Priority( $sortOrder );

            case APILocation::SORT_FIELD_NAME:
                return new SortClause\ContentName( $sortOrder );

            //@todo: sort clause not yet implemented
            // case APILocation::SORT_FIELD_MODIFIED_SUBNODE:

            case APILocation::SORT_FIELD_NODE_ID:
                return new SortClause\Location\Id( $sortOrder );

            case APILocation::SORT_FIELD_CONTENTOBJECT_ID:
                return new SortClause\ContentId( $sortOrder );

            default:
                return new SortClause\Location\Path( $sortOrder );
        }
    }

    public function getLocationByRemoteId($remoteId) {
        try {
            return $this->repository->getLocationService()->loadLocationByRemoteId($remoteId);
        } catch (\Exception $ex) {
            return false;
        }
    }

}
