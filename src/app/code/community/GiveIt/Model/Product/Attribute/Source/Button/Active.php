<?php

/**
 * GiveIt extension
 *

 * @package    GiveIt

 */
class GiveIt_Model_Product_Attribute_Source_Button_Active
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
                    'label' => Mage::helper('giveit')->__('Yes'),
                    'value' => self::BUTTON_ACTIVE_YES
                ),
                array(
                    'label' => Mage::helper('giveit')->__('No'),
                    'value' => self::BUTTON_ACTIVE_NO
                ),
                array(
                    'label' => Mage::helper('giveit')->__('Use config'),
                    'value' => self::BUTTON_ACTIVE_USE_CONFIG
                )
            );
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array(
            'unsigned'  => true,
            'default'   => null,
            'extra'     => null
        );

        $helper = Mage::helper('giveit');

        if ($helper->useDbCompatibleMode()) {
            $column['type'] = 'int';
            $column['is_null'] = true;
        } else {
            $column['type'] = Varien_Db_Ddl_Table::TYPE_INTEGER;
            $column['nullable'] = true;
        }

        return array($attributeCode => $column);
    }

    /**
     * Retrieve Select for update Attribute value in flat table
     *
     * @param   int $store
     * @return  Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute_option')
            ->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }

}

