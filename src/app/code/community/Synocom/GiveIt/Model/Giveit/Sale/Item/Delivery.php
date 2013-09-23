<?php
/**
 * Light4website
 *
 * @copyright  Copyright(c) 2013 light4website (http://light4website.com)
 * @license    http://light4website.com/license/license.txt
 */
class Synocom_GiveIt_Model_Giveit_Sale_Item_Delivery extends Mage_Core_Model_Abstract {

    public function getDescription() {
        return 'ID: ' . $this->getData('id')
            . ', Name: ' . $this->getName()
            . ', TAX: ' . $this->getTaxPercent() . '%'
            . ', Title: ' . $this->getTitle();
    }

    public function getFloatPrice() {
        return ($this->getPrice() / 100);
    }

}