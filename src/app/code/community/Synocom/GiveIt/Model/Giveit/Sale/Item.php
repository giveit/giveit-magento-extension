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
class Synocom_GiveIt_Model_Giveit_Sale_Item extends Mage_Core_Model_Abstract {

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
                $this->_product = Mage::getModel('synocom_giveit/giveit_sale_item_product');
                $this->_product->setData($key[self::PRODUCT_KEY]);
            }

            if (array_key_exists(self::SELECTED_OPTIONS_KEY, $key)) {
                $this->_selectedOptions = Mage::getModel('synocom_giveit/giveit_sale_item_selectedoptions');
                $this->_selectedOptions->setData($key[self::SELECTED_OPTIONS_KEY]);
            }

            if (array_key_exists(self::DELIVERY_KEY, $key)) {
                $this->_delivery = Mage::getModel('synocom_giveit/giveit_sale_item_delivery');
                $this->_delivery->setData($key[self::DELIVERY_KEY]);
            }
        }

        return $this;
    }

    public function getProduct() {
        return $this->_product;
    }

    public function getSelectedOptions() {
        return $this->_selectedOptions;
    }

    public function getDelivery() {
        return $this->_delivery;
    }

}