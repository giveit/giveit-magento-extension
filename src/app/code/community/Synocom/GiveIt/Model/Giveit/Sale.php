<?php
/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
class Synocom_GiveIt_Model_Giveit_Sale extends Mage_Core_Model_Abstract {

    protected $_items = array();
    protected $_buyer;
    protected $_recipient;
    protected $_address;

    const SALE_ITEMS_KEY            = 'items';
    const SALE_BUYER_KEY            = 'buyer';
    const SALE_RECIPIENT_KEY        = 'recipient';
    const SALE_ADDRESS_KEY          = 'shipping_address';

    /**
     * Set Sale object using API response data
     *
     * @param $responseData array
     * @return $this
     */
    public function setObject(array $responseData) {
        $this->setData($responseData);

        if (array_key_exists(self::SALE_ITEMS_KEY, $responseData)) {
            foreach ($responseData[self::SALE_ITEMS_KEY] as $apiItem) {
                $item = Mage::getModel('synocom_giveit/giveit_sale_item');
                $this->_items[] = $item->setData($apiItem);
            }
        }

        $this->_buyer = $this->_mapDataToObject(self::SALE_BUYER_KEY, $responseData, 'synocom_giveit/giveit_sale_buyer');

        $this->_recipient = $this->_mapDataToObject(self::SALE_RECIPIENT_KEY, $responseData,
            'synocom_giveit/giveit_sale_recipient');

        $this->_address = $this->_mapDataToObject(self::SALE_ADDRESS_KEY, $responseData,
            'synocom_giveit/giveit_sale_address');

        return $this;
    }

    /**
     * Get Sale Items list
     *
     * @return array
     */
    public function getItems() {
        return $this->_items;
    }

    /**
     * Return Buyer information
     *
     * @return Synocom_GiveIt_Model_Giveit_Sale_Buyer
     */
    public function getBuyer() {
        return $this->_buyer;
    }

    /**
     * Return Shipping Address
     *
     * @return Synocom_GiveIt_Model_Giveit_Sale_Shipping_Address
     */
    public function getShippingAddress() {
        return $this->_address;
    }

    public function getRecipient() {
        return $this->_recipient;
    }

    /**
     * Map API response data to object
     *
     * @param $key string
     * @param $responseData array
     * @param $modelClass string
     * @return mixed
     */
    protected function _mapDataToObject($key, $responseData, $modelClass) {
        if (array_key_exists($key, $responseData)) {
            $object = Mage::getModel($modelClass);
            return $object->setData($responseData[$key]);
        }
    }

}