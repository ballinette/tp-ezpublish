<?php
/**
Truncate Html string without stripping tags

 * Usage:
{{ htmlstring|truncatehtml(500)|raw }}
 */
namespace Kaliop\UtilsBundle\Twig;

class TruncateHtmlString {
    function __construct($string, $limit) {
        // create dom element using the html string
        $this->tempDiv = new \DomDocument();

//        $string = mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
        $this->tempDiv->loadXML($string);
        // keep the characters count till now
        $this->charCount = 0;
        $this->encoding = 'UTF-8';
        // character limit need to check
        $this->limit = $limit;
    }
    function cut($endchar) {
        // create empty document to store new html
        $this->newDiv = new \DomDocument();
        // cut the string by parsing through each element
        $this->searchEnd($this->tempDiv->documentElement, $this->newDiv,$endchar);
        $newhtml = $this->newDiv->saveHTML();
        return $newhtml;
    }

    function deleteChildren($node) {
        while (isset($node->firstChild)) {
            $this->deleteChildren($node->firstChild);
            $node->removeChild($node->firstChild);
        }
    }
    function searchEnd($parseDiv, $newParent,$endchar) {
        foreach($parseDiv->childNodes as $ele) {
            // not text node
            if($ele->nodeType != 3) {
                $newEle = $this->newDiv->importNode($ele, true);
                if(count($ele->childNodes) === 0) {
                    $newParent->appendChild($newEle);
                    continue;
                }
                $this->deleteChildren($newEle);
                $newParent->appendChild($newEle);
                $res = $this->searchEnd($ele, $newEle,$endchar);
                if($res)
                    return $res;
                else
                    continue;
            }

            // the limit of the char count reached
            if(mb_strlen($ele->nodeValue, $this->encoding) + $this->charCount >= $this->limit) {
                $newEle = $this->newDiv->importNode($ele);
                // TODO : utiliser une regex pr match . ou " "
                $position = strpos($newEle->nodeValue, " ", $this->limit - $this->charCount);
                if ($position == false)
                    $position = strpos($newEle->nodeValue, ".", $this->limit - $this->charCount);
                $newEle->nodeValue = substr($newEle->nodeValue, 0, $position).$endchar;
                $newParent->appendChild($newEle);
                return true;
            }
            $newEle = $this->newDiv->importNode($ele);
            $newParent->appendChild($newEle);
            $this->charCount += mb_strlen($newEle->nodeValue, $this->encoding);
        }
        return false;
    }
}