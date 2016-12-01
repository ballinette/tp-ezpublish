<?php

namespace Kaliop\UtilsBundle\Helper;

use Symfony\Component\HttpFoundation\Response;

class ResponseHelper
{
    CONST LOCATION_HEADER = 'X-Location-Id';
    protected $defaultMaxAge;

    public function __construct($defaultMaxAge)
    {
        $this->defaultMaxAge = $defaultMaxAge;
    }

    /**
     * Create a standard HTTP Response
     * This method can be used to build a response for any custom route controller action
     *
     * @param int        $maxAge  : shared Max Age ttl in seconds (defaults to 1 year if omitted)
     * @param bool|false $noCache : id true, creates a response with no-cache headers (will not be cached by Varnish)
     * @param null       $varyHeader
     *
     * @return Response
     */
    public function createResponse($noCache = false, $maxAge = -1, $varyHeader = null)
    {
        $response = new Response();
        $response->setPublic();

        if (!$noCache) {
            if ($maxAge == -1) {
                $maxAge = $this->defaultMaxAge;
            }

            $response->setSharedMaxAge($maxAge);
        } else {
            $response->headers->set("Cache-control", 'no-cache');
            $response->headers->set("Pragma", 'no-cache');
        }

        if ($varyHeader != null) {
            $response->setVary($varyHeader);
        }

        return $response;
    }

    /**
     * Pré construit une réponse ESI
     * @param array|string $headerValues
     * @param int  $maxAge
     * @param null $headerName
     *
     * @return Response
     */
    public function createEsiResponse($headerValues, $maxAge = -1, $headerName = null)
    {

        if ($headerName != null) {
            $headerType = $headerName;
        } else {
            $headerType = self::LOCATION_HEADER;
        }

        $response = new Response();
        $response->setPublic();

        if (!empty( $headerValues )) {
            if (is_array($headerValues)) {
                $headerValues = implode(' ', $headerValues);
            }
        } else {
            $headerValues = '';
        }

        if ($maxAge == -1) {
            $maxAge = $this->defaultMaxAge;
        }

        $response->setSharedMaxAge($maxAge);
        $response->headers->set($headerType, $headerValues);

        return $response;
    }
}