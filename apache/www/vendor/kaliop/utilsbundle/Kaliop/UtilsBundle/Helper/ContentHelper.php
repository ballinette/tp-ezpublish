<?php

namespace Kaliop\UtilsBundle\Helper;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\SignalSlot\Repository as Repository;


class ContentHelper {

    protected $repository;

    /**
     * Constructor
     *
     * @param \eZ\Publish\Core\SignalSlot\Repository
     */
    public function __construct(Repository $repository) {
        $this->repository = $repository;
    }

    public function getContent($contentIds){
        $contentService = $this->repository->getContentService();

        $content = array();
        foreach ($contentIds as $contentId) {
            array_push($content,$contentService->loadContent($contentId));
        }

        return array(
            'content' => $content
        );

    }

    /**
     * Return related content with a valid mainLocationId (ie: not in trash) from $fieldName related objects
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldName
     * @return array
     *
     */
    public function getRelationContents($content,$fieldName )
    {
        $relationContents=array();
        $contentService = $this->repository->getContentService();

        $destinationContentIds = $content->getFieldValue($fieldName)->destinationContentIds;
        foreach ($destinationContentIds as $contentId) {

            try {
                $destinationContent = $contentService->loadContent($contentId);
                if ($destinationContent->contentInfo->mainLocationId != null) {
                    array_push($relationContents, $destinationContent);
                }
            } catch (\Exception $e) {
            }
        }
        return $relationContents;
    }

    /**
     * Return related content with a valid mainLocationId (ie: not in trash or deleted) from $fieldName related object (ezcontentobjectrelation firle type)
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldName
     * @return Content
     *
     */
    public function getRelationContent($content,$fieldName )
    {

        $contentService = $this->repository->getContentService();
        try {
            $destinationContent = $contentService->loadContent($content->getFieldValue( $fieldName )->destinationContentId);
            if ($destinationContent->contentInfo->mainLocationId != null) {
                $relationContent =$destinationContent;
            }
        } catch (\Exception $e) {
            $relationContent=null;
        }

        return $relationContent;
    }

    public function getContentType($classId,$language){
        if ( !is_numeric($classId) ){
            return null;
        }

        $contentTypeService = $this->repository->getContentTypeService();

        $classIdentifier = $contentTypeService->loadContentType($classId);

        return $classIdentifier->getName($language);
    }

    public function getContentTypeByContentId($contentId)
    {
        $contentService = $this->repository->getContentService();
        $contentTypeService = $this->repository->getContentTypeService();
        $contentType = $contentTypeService->loadContentType($contentService->loadContentInfo($contentId)->contentTypeId);

        return $contentType->identifier;
    }

    public function getContentByRemoteId($remoteId) {
        return $this->repository->getContentService()->loadContentByRemoteId($remoteId);
    }

    /**
     * @param int $locationId
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContentByLocationId($locationId) {
        $location = $this->repository->getLocationService()->loadLocation( $locationId );
        return $this->loadContentByLocation($location);
    }

    /**
     * @param Location $location
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContentByLocation(Location $location) {
        return $this->repository->getContentService()->loadContentByContentInfo( $location->getContentInfo() );
    }

    /**
     * Return children contents from specified parent location
     * @param Location $location
     * @param array $contentTypeIdentifiers
     * @return array
     */
    public function loadContentsByParentLocationId(Location $location, $contentTypeIdentifiers=array())
    {
        $filter = new Criterion\LogicalAnd(array(
            new Criterion\ContentTypeIdentifier( $contentTypeIdentifiers ),
            new Criterion\ParentLocationId( $location->id )
        ));

        $query = new Query(
            array(
                "filter" => $filter,
            )
        );

        $searchResult = $this->repository->getSearchService()->findContent($query);

        $childContents = array();
        foreach ( $searchResult->searchHits as $searchHit )
        {
            $childContents[]=$searchHit->valueObject;
        }

        return $childContents;
    }

    /**
     * Returns reverse related content ids for specified field identifier
     * @param Content $content : source Content
     * @param $fieldIdentifier : relationobjectslist field
     * @param array $classIdentifiers : related objects' class identifiers to filter
     * @param null $maxResults : maximum number of related objects to return
     * @return array
     */
    public function loadReverseRelatedContent(Content $content,$fieldIdentifier,$classIdentifiers = array(),$maxResults=null)
    {
        $reverseRelatedContentIds= array();

        $attributeValue = $content->getFieldValue($fieldIdentifier);

        if($attributeValue != null){

            $contentService = $this->repository->getContentService();

            // load reverse related content
            foreach ($attributeValue->destinationContentIds as $contentId){

                $contentInfo = $contentService->loadContentInfo( $contentId );
                $reverseRelatedInfo = $contentService->loadReverseRelations($contentInfo);

                //get ContentId for each Relation
                foreach($reverseRelatedInfo as $relationInfo)
                {
                    $relatedContentId = $relationInfo->getSourceContentInfo()->id;
                    //echo "related COntent Id -->".$relatedContentId;
                    if($relatedContentId != $content->id){ // related content must be different from current content
                        if(!in_array($relatedContentId,$reverseRelatedContentIds)){

                            if(count($classIdentifiers) > 0)
                            {
                                $contentType = $this->getContentTypeByContentId($relatedContentId);
                                //echo 'ContentType -->'.$contentType;
                                if(in_array($contentType,$classIdentifiers))
                                {
                                    $reverseRelatedContentIds[]=$relatedContentId;
                                }
                            }
                            else
                            {
                                $reverseRelatedContentIds[]=$relatedContentId;
                            }
                        }
                    }
                }
            }

            if($maxResults !== null)
            {
                $reverseRelatedContentIds = array_slice($reverseRelatedContentIds, 0, $maxResults);
            }
        }

        return $reverseRelatedContentIds;
    }

}