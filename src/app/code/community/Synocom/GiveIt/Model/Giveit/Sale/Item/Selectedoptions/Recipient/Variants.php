<?php
/**
 * GiveIt extension
 *
 * @method getId()
 * @method getName()
 * @method getPrice()
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
class Synocom_GiveIt_Model_Giveit_Sale_Item_Selectedoptions_Recipient_Variants extends Mage_Core_Model_Abstract {

    public function getSku() {
        return $this->getData('id');
    }

}