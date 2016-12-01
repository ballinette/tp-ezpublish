<?php

namespace  Kaliop\UtilsBundle\Core\FieldType\XmlText\Converter;

use DOMElement;
use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedLinking;

/**
 * EmbedLinking converter adds link parameters to the embed element
 * and unwraps the embed from the link if needed.
 */
class KaliopEmbedLinkingConverter extends EmbedLinking
{
    /**
     * Add the link view parameter for link-embed element
     *
     * @param DOMElement $embed
     */
    protected function copyLinkAttributes(DOMElement $embed)
    {
        parent::copyLinkAttributes($embed);

        $link = $embed->parentNode;

        if ($link->hasAttribute("view")) {
            $embed->setAttribute(static::TEMP_PREFIX . "view", $link->getAttribute("view"));
        }
    }
}
