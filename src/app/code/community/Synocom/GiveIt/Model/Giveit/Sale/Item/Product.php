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

    /**
     * Get product details
     *
     * @return Synocom_GiveIt_Model_Giveit_Sale_Item_Product_Details
     */
    public function getDetails() {
        return $this->_details;
    }

}