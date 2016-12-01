<?php

namespace Kaliop\UtilsBundle\Core\FieldType\XmlText\Converter;

use DOMElement;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedLinking;
use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedToHtml5;
use DOMDocument;

class KaliopEmbedToHtml5Converter extends EmbedToHtml5
{

    /**
     * Process embed tags for a single tag type (embed or embed-inline)
     * Kaliop hack : catch UnauthorizedException to avoid 403 response in main page
     *
     * @param \DOMDocument $xmlDoc
     * @param $tagName string name of the tag to extract
     */
    protected function processTag( DOMDocument $xmlDoc, $tagName )
    {
        try {
            parent::processTag($xmlDoc, $tagName);
        }
        catch(UnauthorizedException $e)
        {

        }
    }

    /**
     * Add view parameter if needed for link-embed element
     *
     * @param DOMElement $embed
     * @return array|null
     */
    protected function getLinkParameters(DOMElement $embed)
    {
        $parameters = parent::getLinkParameters($embed);

        if (!is_null($parameters)) {
            if ($embed->hasAttribute(EmbedLinking::TEMP_PREFIX . "view")) {
                $parameters['view'] = $embed->getAttribute( EmbedLinking::TEMP_PREFIX . "view" );
            }
        }

        return $parameters;
    }
}
