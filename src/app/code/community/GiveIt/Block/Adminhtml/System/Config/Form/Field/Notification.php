<?php
/**
 * GiveIt extension
 *

 * @package    GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
class GiveIt_Block_Adminhtml_System_Config_Form_Field_Notification
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string|GiveIt_Helper_Data
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        return Mage::helper('giveit')->getCallbackUrl();
    }

}