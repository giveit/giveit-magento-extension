<?php

/**
 * GiveIt extension
 *

 * @package    GiveIt

 */

/**
 * Button type source model
 */
class GiveIt_Model_System_Config_Source_Button_Type
{

    const TYPE_WHITE_SM  = 'white_sm';
    const TYPE_WHITE_LG  = 'white_lg';
    const TYPE_BLACK_SM  = 'black_sm';
    const TYPE_BLACK_LG  = 'black_lg';

    /**
     * Returns option list of button types for configuration
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::TYPE_WHITE_SM => Mage::helper('giveit')->__('White small'),
            self::TYPE_WHITE_LG => Mage::helper('giveit')->__('White large'),
            self::TYPE_BLACK_SM => Mage::helper('giveit')->__('Black small'),
            self::TYPE_BLACK_LG => Mage::helper('giveit')->__('Black large'),
        );
    }

}
