<?php

/**
 * Class Ecommistry_Trailingslash_Model_Observer
 */
class Ecommistry_Trailingslash_Model_Observer
{
    /**
     * Remove trailing slash url 'controller_front_init_routers' event
     *
     * @param Varien_Event_Observer $observer
     */
    public function removeTrailingSlash(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('ecommistry_trailingslash')->isEnabled()) {
            return $this;
        }

        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();
        $request = $front->getRequest();

        if ($this->_isRunningFromCli()) {
            return $this;
        }

        if ($this->_isAdminFrontNameMatched($request)) {
            return $this;
        }

        if ($this->_isApiFrontNameMatched($request)) {
            return $this;
        }

        $baseUrl = Mage::getBaseUrl(
            Mage_Core_Model_Store::URL_TYPE_WEB,
            Mage::app()->getStore()->isCurrentlySecure()
        );
        if (!$baseUrl) {
            return $this;
        }

        $requestUri = $request->getRequestUri();
        if($requestUri === '/') {
            return $this;
        }

        if(Mage::helper('ecommistry_trailingslash')->isAddPortToUrl()) {
            $port = $request->getServer('SERVER_PORT');
            if ($port) {
                $defaultPorts = array(
                    Mage_Core_Controller_Request_Http::DEFAULT_HTTP_PORT,
                    Mage_Core_Controller_Request_Http::DEFAULT_HTTPS_PORT
                );
                $port = (in_array($port, $defaultPorts)) ? '' : ':' . $port;
            }
        } else {
            $port = '';
        }

        $requestUrl = $request->getScheme() . '://' . $request->getHttpHost() . $port . $requestUri;
        if(strpos($requestUrl, 'api')){
            return $this;
        }
        else{
            if(substr($requestUrl, -1) === '/') {
                $requestUrl = rtrim($requestUrl, '/');
                Mage::app()->getFrontController()->getResponse()
                    ->setRedirect($requestUrl, 301)
                    ->sendResponse();
                exit;
            }
        }
    }


    /**
     * Check if requested path starts with one of the admin front names
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    protected function _isAdminFrontNameMatched($request)
    {
        $useCustomAdminPath = (bool)(string)Mage::getConfig()
            ->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_USE_CUSTOM_ADMIN_PATH);
        $customAdminPath = (string)Mage::getConfig()->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_CUSTOM_ADMIN_PATH);
        $adminPath = ($useCustomAdminPath) ? $customAdminPath : null;

        if (!$adminPath) {
            $adminPath = (string)Mage::getConfig()
                ->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_ADMINHTML_ROUTER_FRONTNAME);
        }
        $adminFrontNames = array($adminPath);

        // Check for other modules that can use admin router (a lot of Magento extensions do that)
        $adminFrontNameNodes = Mage::getConfig()->getNode('admin/routers')
            ->xpath('*[not(self::adminhtml) and use = "admin"]/args/frontName');

        if (is_array($adminFrontNameNodes)) {
            foreach ($adminFrontNameNodes as $frontNameNode) {
                /** @var $frontNameNode SimpleXMLElement */
                array_push($adminFrontNames, (string)$frontNameNode);
            }
        }

        $pathPrefix = ltrim($request->getPathInfo(), '/');
        $urlDelimiterPos = strpos($pathPrefix, '/');
        if ($urlDelimiterPos) {
            $pathPrefix = substr($pathPrefix, 0, $urlDelimiterPos);
        }

        return in_array($pathPrefix, $adminFrontNames);
    }

    /**
     * Check if requested path match with one of the api / oauth front names
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    protected function _isApiFrontNameMatched($request)
    {
        $found = false;
        $path = $request->getPathInfo();
        if(strpos($path, 'api')){
            $found = true;
        }

        if(strpos($path, 'oauth')){
            $found = true;
        }
        return $found;
    }

    protected function _isRunningFromCli()
    {
        return (php_sapi_name() === 'cli');
    }
}