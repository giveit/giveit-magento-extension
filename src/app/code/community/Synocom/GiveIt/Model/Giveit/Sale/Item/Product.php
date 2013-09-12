<?php
/**
 * Light4website
 *
 * @copyright  Copyright(c) 2013 light4website (http://light4website.com)
 * @license    http://light4website.com/license/license.txt
 */
class Synocom_GiveIt_Model_Giveit_Sale_Item_Product extends Mage_Core_Model_Abstract {

    const DETAILS_KEY = 'details';
    protected $_details;

    public function setData($key, $value = null) {
        parent::setData($key, $value);

        if (is_array($key) && array_key_exists(self::DETAILS_KEY, $key)) {
            $this->_details = Mage::getModel('synocom_giveit/giveit_sale_item_product_details');
            $this->_details->setData($key[self::DETAILS_KEY]);
        }

        return $this;
    }

    public function getDetails() {
        return $this->_details;
    }

}