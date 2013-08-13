<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Brennon Blokland <info@synocom.nl>
 */
class Synocom_GiveIt_Model_Product_Attribute_Source_Button_Active
    extends Mage_Eav_Model_Entity_Attribute_Source_Boolean
{
    /**
     * Constants - attribute value
     */

    const BUTTON_ACTIVE_YES        = 1;
    const BUTTON_ACTIVE_NO         = 0;
    const BUTTON_ACTIVE_USE_CONFIG = 2;

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('synocom_giveit')->__('Yes'),
                    'value' => self::BUTTON_ACTIVE_YES
                ),
                array(
                    'label' => Mage::helper('synocom_giveit')->__('No'),
                    'value' => self::BUTTON_ACTIVE_NO
                ),
                array(
                    'label' => Mage::helper('synocom_giveit')->__('Use config'),
                    'value' => self::BUTTON_ACTIVE_USE_CONFIG
                )
            );
        }
        return $this->_options;
    }

}

