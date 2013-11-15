<?php

/**
 * GiveIt extension
 *
 * @package    GiveIt
 * @copyright  Give.it
 */
class Giveit_Block_Adminhtml_Product_Button_Active_Renderer
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
            $this->setValue(GiveIt_Model_Product_Attribute_Source_Button_Active::BUTTON_ACTIVE_USE_CONFIG);
        }

        return parent::getElementHtml();
    }

}

