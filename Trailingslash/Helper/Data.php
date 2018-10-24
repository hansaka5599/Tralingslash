<?php

/**
 * Class Ecommistry_Trailingslash_Helper_Data
 */
class Ecommistry_Trailingslash_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XPATH_ACTIVE = 'trailingslash/settings/active';
    const XPATH_PORT = 'trailingslash/settings/port';

    /**
     * Module enable?
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XPATH_ACTIVE);
    }

    /**
     * @return mixed
     */
    public function isAddPortToUrl()
    {
        return Mage::getStoreConfig(self::XPATH_PORT);
    }
}