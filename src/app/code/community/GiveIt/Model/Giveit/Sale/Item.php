<?php
/**
 * GiveIt extension
 *

 * @package    GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
class GiveIt_Model_Giveit_Sale_Item extends Mage_Core_Model_Abstract {

    const PRODUCT_KEY           = 'product';
    const SELECTED_OPTIONS_KEY  = 'selected_options';
    const DELIVERY_KEY          = 'delivery';

    protected $_product;
    protected $_selectedOptions;
    protected $_delivery;

    public function setData($key, $value = null) {
        parent::setData($key, $value);

        if (is_array($key)) {
            if(array_key_exists(self::PRODUCT_KEY, $key)) {
                $this->_product = Mage::getModel('giveit/giveit_sale_item_product');
                $this->_product->setData($key[self::PRODUCT_KEY]);
            }

            if (array_key_exists(self::SELECTED_OPTIONS_KEY, $key)) {
                $this->_selectedOptions = Mage::getModel('giveit/giveit_sale_item_selectedoptions');
                $this->_selectedOptions->setData($key[self::SELECTED_OPTIONS_KEY]);
            }

            if (array_key_exists(self::DELIVERY_KEY, $key)) {
                $this->_delivery = Mage::getModel('giveit/giveit_sale_item_delivery');
                $this->_delivery->setData($key[self::DELIVERY_KEY]);
            }
        }

        return $this;
    }

    /**
     * Get API product
     *
     * @return GiveIt_Model_Giveit_Sale_Item_Product
     */
    public function getProduct() {
        return $this->_product;
    }

    /**
     * Get selected options
     *
     * @return GiveIt_Model_Giveit_Sale_Item_Selectedoptions
     */
    public function getSelectedOptions() {
        return $this->_selectedOptions;
    }

    /**
     * Get Delivery
     *
     * @return GiveIt_Model_Giveit_Sale_Item_Delivery
     */
    public function getDelivery() {
        return $this->_delivery;
    }

}