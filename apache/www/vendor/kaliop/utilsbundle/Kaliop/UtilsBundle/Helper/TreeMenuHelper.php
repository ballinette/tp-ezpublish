<?php

namespace Kaliop\UtilsBundle\Helper;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\SignalSlot\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use Kaliop\SolrBundle\Solr\Select\Filter\KeyValueFilter;
use Kaliop\SolrBundle\Solr\Select\Filter\OrFilter;
use Kaliop\SolrBundle\Solr\Select\Request;
use Kaliop\SolrBundle\Solr\Select\ResponseItem;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TreeMenuHelper {

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Repository
     */
    protected $repository;

    protected static $uasortKeysPosition = array();

    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->repository = $this->container->get('ezpublish.api.repository');
    }

    /**
     * Reconstitue une portion de l'arbre de noeud eZ Publish en 2 requêtes
     * en renseignant pour chaque noeud : le nombre de ses descendants, sa profondeur,
     * et si celui-ci fait partie du chemin donné
     * @param $pathLocationsStringId Path string de la location en cours
     * @param $rootLocations  Location[] servant de niveau 1 au menu
     * @param array $typeIdentifiers Type identifiers des contentType a afficher
     * @param int $depth RELATIVE depth of $rootLocations children to be displayed
     * @param Request $solrRequest solr Request déjà initialisée si besoin de filtres/paramètres supplémentaires
     * @return array
     */

    public function tree($pathLocationsStringId, $rootLocations, $typeIdentifiers=array(), $depth=3, $solrRequest=null) {
        if (!is_numeric($depth)) $depth = 3;

        $rootLocationsId = array();
        $locationService = $this->repository->getLocationService();

        // Create Solr request
        $solrHelper = $this->container->get('kaliopsolr.helper.solrrequest');

        if($solrRequest === null)
        {
            $solrRequest = $solrHelper->createRequest();
        }
        $solrRequest = $this->createSolrRequest($solrRequest);

        if (is_array($typeIdentifiers) && count($typeIdentifiers) > 0) {
            $solrRequest->fq->addFilter( new OrFilter('meta_class_identifier_ms', $typeIdentifiers) );
        }

        $orFilter = new OrFilter();
        while(list(, $rootLocation) = each($rootLocations)) {
            if (is_numeric($rootLocation))
                $rootLocation = $locationService->loadLocation($rootLocation);

            $rootLocationsId[] = (string) $rootLocation->id;
            $orFilter->addFilter(new KeyValueFilter('meta_path_si', $rootLocation->id));
        }

        self::$uasortKeysPosition = $rootLocationsId;


        $solrRequest->fq->addFilter( $orFilter );
        $solrRequest->fl[] = 'meta_path_string_ms';
        $solrRequest->rows = 99999;

        // Search correct location id following root's path string
        $locationsDepth = array();
        $response = $solrHelper->send($solrRequest);
        $responseItemsByLocationId = array();
        foreach($response as $responseItem) {
            foreach($responseItem->meta_path_string_ms as $pathString) {

                $pathLocationIds = explode('/', trim($pathString, '/'));
                $matchPath=array_intersect($rootLocationsId, $pathLocationIds);

                if (count($matchPath) > 0 ) {
                    $rootNodeId=reset($matchPath);
                    if( preg_match('#/'.$rootNodeId.'/(\d+/){0,'.$depth.'}$#',$pathString ) )
                    {
                        $locationsDepth[(int) end($pathLocationIds)] = count($pathLocationIds);
                        $responseItemsByLocationId[(int) end($pathLocationIds)] = $responseItem;
                    }
                }
            }
        }

        $parentLocationsId = $this->createItems(array_keys($locationsDepth), $responseItemsByLocationId, $pathLocationsStringId);
        return $this->createTree($parentLocationsId, $rootLocationsId);
    }

    protected function createSolrRequest($solrRequest) {
        return $solrRequest;
    }

    protected function createItem(Location $location, ResponseItem $responseItem, $pathLocationsId) {
        $lastPathLocationId = end($pathLocationsId);
        return array(
            'content_info' => $location->getContentInfo(),
            'in_path' => in_array($location->id, $pathLocationsId),
            'selected' => $location->id == $lastPathLocationId,
        );
    }

    private function createItems($locationId, $responseItemsByLocationId, $pathLocationsStringId) {
        $pathLocationsId = explode('/', trim($pathLocationsStringId, '/'));

        $query = new LocationQuery(array( 'criterion' => new Criterion\LocationId( $locationId ) ));
        $query->limit = count($locationId);

        $searchService = $this->repository->getSearchService();
        $result = $searchService->findLocations($query);

        $parentLocationsId = array();
        reset($result->searchHits);
        while(list(, $searchHit) = each($result->searchHits)) {
            $location = $searchHit->valueObject;
            $item = $this->createItem($location, $responseItemsByLocationId[$location->id], $pathLocationsId);
            $item['location'] = $location;
            $item['children'] = array();
            $item['depth'] = $location->depth;
            $item['weight'] = 1;
            $parentLocationsId[$location->id] = $item;
        }

        uasort($parentLocationsId, function ($a, $b) {
            if ($a['depth'] == $b['depth']) return 0;
            return ($a['depth'] > $b['depth']) ? -1 : 1;
        });

        return $parentLocationsId;
    }

    protected function createTree($parentLocationsId, $rootLocationsId) {
        $tree = $parentLocationsId;
        $parentLocationsIds = array_keys($parentLocationsId);
        foreach($parentLocationsIds as $locationId) {
            $row = $tree[$locationId];
            $location = $row['location'];

            if (isset($tree[$location->parentLocationId])) {
                $parentItem = $tree[$location->parentLocationId];
                $parentItem['weight'] += $row['weight'];
                $parentItem['children'][$locationId] = $row;

                uasort($parentItem['children'], array(__CLASS__, 'uasortChildren'));

                $tree[$location->parentLocationId] = $parentItem;
                unset($tree[$locationId]);
            } elseif (!in_array($locationId, $rootLocationsId)) {
                unset($tree[$locationId]);
            }
        }

        uasort($tree, array(__CLASS__, 'uasortTreeNode'));

        return $tree;
    }

    private static function uasortTreeNode($a, $b) {
        if ($a && $a['location'] && $b && $b['location']) {
            $aPosition = array_search($a['location']->id, self::$uasortKeysPosition);
            $bPosition = array_search($b['location']->id, self::$uasortKeysPosition);
            if ($aPosition == $bPosition) return 0;
            return ($aPosition < $bPosition) ? -1 : 1;
        }
        return 0;
    }

    protected static function uasortChildren($a,$b)
    {
        if ($a['location']->priority == $b['location']->priority) return 0;
        return ($a['location']->priority < $b['location']->priority) ? -1 : 1;
    }
}
