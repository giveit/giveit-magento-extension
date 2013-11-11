<?php
/**
 * GiveIt extension
 *

 * @package    GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
class GiveIt_Model_Observer
{
    const GIVEIT_SECTION    = 'giveit';
    const MAGENTO_MINOR_VERSION     = 4;

    /**
     *  Update Admin configuration section for Magento 1.4.x.x version
     *
     * @param $observer
     */
    public function controllerActionLayoutGenerateXmlBefore($observer)
    {
        $sectionCode = Mage::app()->getRequest()->getParam('section');
        $helper = $this->_helper();

        if ($helper->isMagentoMinorVersion(self::MAGENTO_MINOR_VERSION) && ($sectionCode == self::GIVEIT_SECTION)) {
            $sections = Mage::getSingleton('adminhtml/config')->getSections();
            $this->_updateButtonPositionConfiguration($sections->{self::GIVEIT_SECTION});
        }
    }

    /**
     * Update list of options available for position of button in Admin panel
     *
     * @param Mage_Core_Model_Config_Element $section
     */
    protected function _updateButtonPositionConfiguration(Mage_Core_Model_Config_Element $section)
    {
        $buttonPositionSimpleFields = $section->groups->button_position_simple->fields;
        $this->_unsetFieldsFromGroup($buttonPositionSimpleFields, array('position_1', 'position_2', 'position_3'));

        $buttonPositionConfigurableFields = $section->groups->button_position_configurable->fields;
        $this->_unsetFieldsFromGroup($buttonPositionConfigurableFields, array('position_1', 'position_3', 'position_4'));

        $buttonPositionConfigurableFields->position_2->label = $this->_helper()->__('Position 1');
    }

    /**
     * Unset list of fields in Admin configuration
     *
     * @param Mage_Core_Model_Config_Element $fieldsList
     * @param $fieldsToUnset
     */
    protected function _unsetFieldsFromGroup(Mage_Core_Model_Config_Element $fieldsList, $fieldsToUnset)
    {
        foreach ($fieldsToUnset as $fieldToUnset) {
            unset($fieldsList->$fieldToUnset);
        }
    }

    /**
     * Add column to Catalog Product grid view with Give.it config
     *
     * @param $observer
     */
    public function coreBlockAbstractToHtmlBefore($observer)
    {
        $event = $observer->getEvent();
        $block = $event->getBlock();

        if ($block->getId() == 'productGrid') {
            $giveItOptions = Mage::getModel('giveit/product_attribute_source_button_active')->getOptionArray();

            $block->addColumnAfter('giveit_button_active',
                array(
                    'header'        => Mage::helper('catalog')->__('Give.it'),
                    'width'         => '80px',
                    'index'         => 'giveit_button_active',
                    'type'          => 'options',
                    'options'       => $giveItOptions,
                    'filterable'    => true,
                    'sortable'      => false
                ), 'type');

            $block->sortColumnsByOrder();
        }
    }

    /**
     * Add giveit_button_active to retrieve in collection for Catalog Product grid
     *
     * @param $observer
     */
    public function catalogProductCollectionLoadBefore($observer)
    {
        $event = $observer->getEvent();
        $productCollection = $event->getCollection();

        if ($this->_isCatalogGridRequest()) {
            $productCollection->addAttributeToSelect('giveit_button_active');

            $store = $this->_getStore();
            if ($store->getId()) {
                $productCollection->joinAttribute(
                    'giveit_button_active_alias',
                    'catalog_product/giveit_button_active',
                    'entity_id',
                    null,
                    'left',
                    $store->getId()
                );
            }
        }
    }

    /**
     * Check if is request for Catalog Product grid
     *
     * @return bool
     */
    protected function _isCatalogGridRequest() {
        $request = Mage::app()->getRequest();
        $action = in_array($request->getActionName(), array('index', 'grid'));
        $moduleName = $request->getModuleName();
        $controllerName = $request->getControllerName();

        if ($this->_isAdminModule($moduleName) && $this->_isCatalogProductController($controllerName) && $action) {
            return true;
        }

        return false;
    }

    /**
     * Check if is Admin module
     *
     * @param $moduleName
     * @return bool
     */
    protected function _isAdminModule($moduleName)
    {
        if ($moduleName == 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Check if is catalog_product controller
     *
     * @param $controllerName
     * @return bool
     */
    protected function _isCatalogProductController($controllerName)
    {
        if ($controllerName == 'catalog_product') {
            return true;
        }

        return false;
    }

    /**
     * Get current store
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        $storeId = Mage::app()->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Get data helper
     *
     * @return GiveIt_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('giveit');
    }

}