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

    const PRODUCT_KEY = 'product';
    protected $_product;

    public function setData($key, $value = null) {
        parent::setData($key, $value);

        if (is_array($key) && array_key_exists(self::PRODUCT_KEY, $key)) {
            $this->_product = Mage::getModel('synocom_giveit/giveit_sale_item_product');
            $this->_product->setData($key[self::PRODUCT_KEY]);
        }

        return $this;
    }

    public function getProduct() {
        return $this->_product;
    }

}