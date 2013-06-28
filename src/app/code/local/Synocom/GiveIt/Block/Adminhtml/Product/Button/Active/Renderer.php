<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Brennon Blokland <info@synocom.nl>
 */
class Synocom_Giveit_Block_Adminhtml_Product_Button_Active_Renderer
    extends Varien_Data_Form_Element_Select
{

    /**
     * Set default value for the select input field used in product configuration
     *
     * @return string
     */
    public function getElementHtml()
    {
        if (is_null($this->getValue())) {
            $this->setValue(Synocom_GiveIt_Model_Product_Attribute_Source_Button_Active::BUTTON_ACTIVE_USE_CONFIG);
        }
        return parent::getElementHtml();
    }

}

