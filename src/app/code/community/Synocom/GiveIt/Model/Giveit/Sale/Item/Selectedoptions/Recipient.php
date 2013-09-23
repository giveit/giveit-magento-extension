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
class Synocom_GiveIt_Model_Giveit_Sale_Item_Selectedoptions_Recipient extends Mage_Core_Model_Abstract {

    const VARIANTS_KEY = 'variants';

    protected $_variants;

    public function setData($key, $value = null) {
        parent::setData($key, $value);

        if (is_array($key)) {
            $this->_variants = Mage::getModel('synocom_giveit/giveit_sale_item_selectedoptions_recipient_variants');
            $this->_variants->setData(array_pop($key));
        }

        return $this;
    }

    public function getVariants() {
        return $this->_variants;
    }

}