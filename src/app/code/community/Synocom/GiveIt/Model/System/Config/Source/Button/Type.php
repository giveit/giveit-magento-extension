<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Brennon Blokland <info@synocom.nl>
 */

/**
 * Button type source model
 */
class Synocom_GiveIt_Model_System_Config_Source_Button_Type
{

    const TYPE_WHITE  = 'white_rect_sm';
    const TYPE_GREY   = 'grey_rect_sm';
    const TYPE_BLACK  = 'black_rect_sm';
    const TYPE_BLUE   = 'blue_rect_sm';
    const TYPE_ORANGE = 'orange_rect_sm';
    const TYPE_GREEN  = 'green_rect_sm';

    /**
     * Returns option list of button types for configuration
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::TYPE_WHITE => Mage::helper('synocom_giveit')->__('White'),
            self::TYPE_GREY => Mage::helper('synocom_giveit')->__('Grey'),
            self::TYPE_BLACK => Mage::helper('synocom_giveit')->__('Black'),
            self::TYPE_BLUE => Mage::helper('synocom_giveit')->__('Blue'),
            self::TYPE_ORANGE => Mage::helper('synocom_giveit')->__('Orange'),
            self::TYPE_GREEN => Mage::helper('synocom_giveit')->__('Green'),
        );
    }

}
