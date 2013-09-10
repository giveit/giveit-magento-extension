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

        /*
         * We use the first attribute as main choice. The products of each option are saved with their choice object
         * as a reference for the nested choices.
         */
        foreach ($firstAttribute['options'] as $id => $option) {
            $sdkChoice = $this->helper->getSdkChoice($option['id'], $option['label'], $this->_roundPrice($option['price']),
                array('choice_products' => $this->_getAssignedProductId($option['products'])));
            $this->_mainChoices[] = $sdkChoice;
        }

        if (next($productOptions['attributes'])) {
            $this->_addNestedChoices($productOptions['attributes'], $this->_mainChoices);
        }

        $sdkOption->addChoices($this->_mainChoices);

        $this->addBuyerOption($sdkOption);
    }

    protected function _getAssignedProductId(array $options) {
        if (count($options) == 1) {
            $options['product_id'] = array_pop($options);
            array_push($options, $options['product_id']);
        }

        return $options;
    }

    /**
     * Add nested choices to the main choices
     *
     * @param array $productAttributes
     * @param array $parentChoices
     */
    protected function _addNestedChoices($productAttributes, $parentChoices)
    {
        $choices = array();
        $attribute = current($productAttributes);
        foreach ($parentChoices as $parentChoice) {
            foreach ($attribute['options'] as $id => $option) {
                $choiceProducts = array_intersect($option['products'], $parentChoice->choice_products);
                if (empty($choiceProducts)) {
                    continue;
                }
                //The title of this (nested) choice has to be added to its parent
                $parentChoice->choices_title = $attribute['label'];
                $nestedChoice = $this->helper->getSdkChoice($id, $option['label'], $this->_roundPrice($option['price']),
                    array('choice_products' => $this->_getAssignedProductId($choiceProducts)));
                $parentChoice->addChoice($nestedChoice);
                $choices[] = $nestedChoice;
            }
        }

        if (next($productAttributes)) {
            $this->_addNestedChoices($productAttributes, $choices);
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
