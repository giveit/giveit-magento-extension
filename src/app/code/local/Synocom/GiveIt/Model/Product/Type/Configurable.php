<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Mike Bijnsdorp <info@synocom.nl>
 */

/**
 * GiveIt Product class for Magento Configurable products
 */
class Synocom_GiveIt_Model_Product_Type_Configurable
    extends Synocom_GiveIt_Model_Product_Type_Abstract
{

    /**
     * @var $helper Synocom_GiveIt_Helper_Data
     */
    public $helper;

    /**
     * Products associated with the first options of the configurable product
     *
     * @var array
     */
    protected $_products = array();

    /**
     * The 'main' or first choices of a product. Other choices are nested within these.
     *
     * @var array
     */
    protected $_mainChoices = array();

    /**
     * Gets data from the configurable product and sets it on the SDK product
     *
     * @param array $productArray containing the product
     */
    public function setProductDetails($productArray = array())
    {
        $product = reset($productArray);
        /* @var $product Mage_Catalog_Model_Product */

        $code = $product->getSku();
        $price = $this->_roundPrice($product->getFinalPrice());
        $name = $product->getName();
        $image = $product->getImageUrl();

        $productDetails = array(
            'code'  => $code,
            'price' => $price,
            'name'  => $name,
            'image' => $image
        );

        parent::setProductDetails($productDetails);
        $this->_addProductOptions();
    }

    /**
     * Adds the configurable product options to the SDK product as a buyer option
     */
    protected function _addProductOptions()
    {
        $productOptions = $this->_getProductOptions();
        $this->helper = Mage::helper('synocom_giveit');

        $firstAttribute = reset($productOptions['attributes']);

        $sdkOption = $this->helper->getSdkOption('product_options', 'layered', $this->helper->__('Product options'),
            array('choices_title' => $firstAttribute['label']));

        foreach ($firstAttribute['options'] as $id => $option) {
            $sdkChoice = $this->helper->getSdkChoice($id, $option['label'], $this->_roundPrice($option['price']));
            $this->_mainChoices[] = $sdkChoice;
            $this->_products[$id] = $option['products'];
        }

        unset($productOptions['attributes'][$firstAttribute['id']]);

        if (!empty($productOptions['attributes'])) {
            $this->_addDependantChoices($productOptions);
        }

        $sdkOption->addChoices($this->_mainChoices);

        $this->addBuyerOption($sdkOption);
    }

    /**
     * Add option depandable choices
     *
     * @param array $productOptions
     */
    protected function _addDependantChoices($productOptions)
    {
        foreach ($this->_mainChoices as $choice) {
            foreach ($productOptions['attributes'] as $attribute) {
                foreach ($attribute['options'] as $id => $option) {
                    if (!array_intersect($option['products'], $this->_products[$choice->id])) {
                        continue;
                    }
                    $sdkChoice = $this->helper->getSdkChoice($id, $option['label'], $this->_roundPrice($option['price']));
                    $choice->addChoice($sdkChoice);
                }
            }
        }
    }

    /**
     * Get all configurable product options of the configurable product
     *
     * @return array
     */
    protected function _getProductOptions()
    {
        $block = Mage::app()->getLayout()->getBlockSingleton('catalog/product_view_type_configurable');
        $jsonConfig = $block->getJsonConfig();
        return Mage::helper('core')->jsonDecode($jsonConfig);
    }

}
