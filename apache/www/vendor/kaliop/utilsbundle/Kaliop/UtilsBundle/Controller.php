<?php

namespace Kaliop\UtilsBundle;

class Controller extends \eZ\Bundle\EzPublishCoreBundle\Controller {
    /**
     * @return \Kaliop\UtilsBundle\Helper\ContentHelper
     */
    public function getContentHelper() {
        return $this->get( 'kalioputils.helper.content' );
    }

    /**
     * @return \Kaliop\UtilsBundle\Helper\LocationHelper
     */
    public function getLocationHelper() {
        return $this->get( 'kalioputils.helper.location' );
    }

    public function getTreeMenuHelper() {
        return $this->get( 'kalioputils.helper.treemenu' );
    }

    /**
     * Returns value for $parameterName and fallbacks to $defaultValue if not defined
     *
     * @param string $parameterName
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function getParameter( $parameterName, $defaultValue = null )
    {
        if ( $this->getConfigResolver()->hasParameter( $parameterName ) )
            return $this->getConfigResolver()->getParameter( $parameterName );

        return $defaultValue;
    }
}