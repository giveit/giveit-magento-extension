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
class Synocom_GiveIt_Model_Giveit_Sale_Item_Delivery extends Mage_Core_Model_Abstract {

    public function getDescription() {
        return 'ID: ' . $this->getData('id')
            . ', Name: ' . $this->getName()
            . ', TAX: ' . $this->getTaxPercent() . '%';
    }

    public function getFloatPrice() {
        return ($this->getPrice() / 100);
    }

}