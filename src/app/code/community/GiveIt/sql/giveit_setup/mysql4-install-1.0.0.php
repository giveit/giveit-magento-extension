<?php

/* @var $installer GiveIt_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, 'giveit_button_active');
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'giveit_button_active',
    array(
    'group'            => 'General',
    'frontend'         => '',
    'label'            => 'Give.it Button Active',
    'input'            => 'select',
    'class'            => '',
    'source'           => 'giveit/product_attribute_source_button_active',
    'default'          => GiveIt_Model_Product_Attribute_Source_Button_Active::BUTTON_ACTIVE_USE_CONFIG,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'          => true,
    'required'         => false,
    'user_defined'     => true,
    'searchable'       => false,
    'filterable'       => false,
    'comparable'       => false,
    'visible_on_front' => false,
    'unique'           => false,
    'used_in_product_listing' => true,
    'apply_to'         => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE.','.Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
    'is_configurable'  => false,
    'input_renderer'   => 'giveit/adminhtml_product_button_active_renderer',
));

$installer->endSetup();