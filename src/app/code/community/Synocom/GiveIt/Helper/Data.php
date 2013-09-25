<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Brennon Blokland <info@synocom.nl>
 * @author     Mike Bijnsdorp <info@synocom.nl>
 */
class Synocom_GiveIt_Helper_Data
    extends Mage_Core_Helper_Data
{

    const XPATH_DATA_KEY                    = 'synocom_giveit/settings/data_key';
    const XPATH_IS_MODULE_ENABLED           = 'synocom_giveit/settings/enabled';
    const XPATH_IS_CREATE_API_ORDER_ENABLED = 'synocom_giveit/settings/enabled_api_orders';

    /**
     * Get an instance of the Give.it SDK option object
     *
     * @param string $id
     * @param string $type
     * @param string $name
     * @param array $optional optional options. Optional key is used as index, its value as value
     * @return \GiveIt\SDK\Option
     */
    public function getSdkOption($id, $type, $name, $optional = array())
    {
        $options = array(
            'id'   => $id,
            'type' => $type,
            'name' => $name
        );

        if (!empty($optional)) {
            foreach ($optional as $key => $value) {
                $options[$key] = $value;
            }
        }

        return Mage::getModel('synocom_giveit/option', $options);
    }

    /**
     * Get an instance of the Give.it SDK choice object
     *
     * @param string $id
     * @param string $name
     * @param int $price
     * @param array $optional optional options. Optional key is used as index, its value as value
     * @return \GiveIt\SDK\Choice
     */
    public function getSdkChoice($id, $name, $price, $optional = array())
    {
        $options = array(
            'id'   => $id,
            'name' => $name,
            'price' => $price
        );

        if (!empty($optional)) {
            foreach ($optional as $key => $value) {
                $options[$key] = $value;
            }
        }

        return Mage::getModel('synocom_giveit/choice', $options);
    }

    /**
     * Check if module is enabled
     *
     * @return bool|mixed
     */
    public function isModuleEnabledPerStore() {
        return Mage::getStoreConfig(self::XPATH_IS_MODULE_ENABLED);
    }

    /**
     * Check if $minorVersion match minor part of Magento installation
     *
     * @param int $minorVersion
     * @return bool
     */
    public function isMagentoMinorVersion($minorVersion)
    {
        $magentoVersion = Mage::getVersionInfo();

        if ($magentoVersion['minor'] == $minorVersion) {
            return true;
        }

        return false;
    }

    /**
     * Check if db compatible mode must be used
     *
     * @return bool
     */
    public function useDbCompatibleMode() {
        $coreHelper = Mage::helper('core');

        if (method_exists($coreHelper, 'useDbCompatibleMode') && $coreHelper->useDbCompatibleMode()) {
            return true;
        } else if($this->isMagentoMinorVersion(4)){
            return true;
        }

        return false;
    }

    /**
     * Get API Data Key
     *
     * @return mixed
     */
    public function getDataKey() {
        return Mage::getStoreConfig(self::XPATH_DATA_KEY);
    }

    /**
     * Check if creating Orders through API is enabled
     *
     * @return bool
     */
    public function isApiOrderEnabled() {
        return Mage::getStoreConfig(self::XPATH_IS_CREATE_API_ORDER_ENABLED);
    }

    /**
     * Get callback URL
     *
     * @return string
     */
    public function getCallbackUrl() {
        $selectedStoreId = Mage::app()->getRequest()->getParam('store');
        return Mage::getStoreConfig('web/unsecure/base_url', $selectedStoreId) . 'giveit/api/callbackHandler';
    }
}
