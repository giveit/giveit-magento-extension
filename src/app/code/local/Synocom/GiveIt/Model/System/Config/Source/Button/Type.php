<?php
class Synocom_GiveIt_Model_System_Config_Source_Button_Type
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'white_rect_sm', 'label' => Mage::helper('synocom_giveit')->__('White')),
            array('value' => 'grey_rect_sm', 'label' => Mage::helper('synocom_giveit')->__('Grey')),
            array('value' => 'black_rect_sm', 'label' => Mage::helper('synocom_giveit')->__('Black')),
            array('value' => 'blue_rect_sm', 'label' => Mage::helper('synocom_giveit')->__('Blue')),
            array('value' => 'orange_rect_sm', 'label' => Mage::helper('synocom_giveit')->__('Orange')),
            array('value' => 'green_rect_sm', 'label' => Mage::helper('synocom_giveit')->__('Green')),
        );
    }
}
