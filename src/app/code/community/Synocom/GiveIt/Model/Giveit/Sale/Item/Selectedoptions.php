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
class Synocom_GiveIt_Model_Giveit_Sale_Item_Selectedoptions extends Mage_Core_Model_Abstract {

    const BUYER_KEY     = 'buyer';
    const RECIPIENT_KEY = 'recipient';

    protected $_buyer;
    protected $_recipient;

    public function setData($key, $value = null) {
        parent::setData($key, $value);

        if (is_array($key) && array_key_exists(self::BUYER_KEY, $key)) {
            $this->_buyer = Mage::getModel('synocom_giveit/giveit_sale_item_selectedoptions_buyer');
            $this->_buyer->setData($key[self::BUYER_KEY]);
        }

        if (is_array($key) && array_key_exists(self::RECIPIENT_KEY, $key)) {
            $this->_recipient = Mage::getModel('synocom_giveit/giveit_sale_item_selectedoptions_recipient');
            $this->_recipient->setData($key[self::RECIPIENT_KEY]);
        }

        return $this;
    }

    public function getBuyer() {
        return $this->_buyer;
    }

    public function getRecipient() {
        return $this->_recipient;
    }
}