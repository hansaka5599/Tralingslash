<?php

/**
 * Class Ecommistry_Trailingslash_Model_Core_Url
 */
class Ecommistry_Trailingslash_Model_Core_Url extends Mage_Core_Model_Url
{
    /**
     * Build url by requested path and parameters
     *
     * @param string|null $routePath
     * @param array|null $routeParams
     * @return  string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        $url = parent::getUrl($routePath, $routeParams);
        $url = rtrim($url, '/');
        return $url;
    }

    /**
     * Retrieve route URL
     *
     * @param string $routePath
     * @param array $routeParams
     *
     * @return string
     */
    public function getRouteUrl($routePath = null, $routeParams = null)
    {
        $url = parent::getRouteUrl($routePath, $routeParams);
        $url = rtrim($url, '/');
        return $url;
    }
}