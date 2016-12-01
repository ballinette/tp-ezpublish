<?php


namespace Kaliop\UtilsBundle\Helper;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use eZ\Publish\API\Repository\Values\Content\Content;

class TranslationHelper
{

    /**
     * @var $configResolver ConfigResolver
     */
    private $configResolver;

    private $languages;

    /**
     * @var $content Content
     */
    private $content;


    public function __construct(ConfigResolver $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function getLanguages()
    {
        if ($this->languages == null) {
            $this->languages = $this->configResolver->getParameter( 'languages' );
        }

        return $this->languages;
    }


    /**
     * @param Content $content
     * @param string $fieldIdentifier
     * @return mixed|null
     */
    public function getFieldValue($content, $fieldIdentifier)
    {
        $fieldValue = null;
        $languages = $this->getLanguages();
        foreach ($languages as $language) {
            $fieldValue = $content->getFieldValue( $fieldIdentifier, $language );

            if ($fieldValue != null) {
                break;
            }
        }
        $contentInfo = $content->contentInfo;
        if ($fieldValue == null && $contentInfo->alwaysAvailable) {
            $mainLanguage = $contentInfo->mainLanguageCode;
            $fieldValue = $content->getFieldValue($fieldIdentifier, $mainLanguage);
        }

        return $fieldValue;
    }

    /**
     * @param $content \eZ\Publish\API\Repository\Values\Content\Content
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    public function getFieldsForContent($content)
    {
        $fields = array();
        $languages = $this->getLanguages();
        foreach ($languages as $language) {
            $fields = $content->getFieldsByLanguage($language);
            if (count($fields) > 0) {
                break;
            }
        }

        return $fields;
    }

}