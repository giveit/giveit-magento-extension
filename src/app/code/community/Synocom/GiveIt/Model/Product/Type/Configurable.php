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

        $recipientSdkOption = $this->helper->getSdkOption('product_options', 'layered', $this->helper->__('Product options'),
            array('choices_title' => $firstAttribute['label']));
        $buyerSdkOption = $this->helper->getSdkOption('product_options', 'layered', $this->helper->__('Product options'),
            array('choices_title' => $firstAttribute['label']));

        /*
         * We use the first attribute as main choice. The products of each option are saved with their choice object
         * as a reference for the nested choices.
         */
        foreach ($firstAttribute['options'] as $id => $option) {
            $id = $this->_getSdkChoiceId($option['products'], $option['id']);
            $sdkChoice = $this->helper->getSdkChoice($id, $option['label'], $this->_roundPrice($option['price']),
                array('choice_products' => $option['products']));
            $this->_mainChoices[] = $sdkChoice;
        }

        if (next($productOptions['attributes'])) {
            $this->_addNestedChoices($productOptions['attributes'], $this->_mainChoices);
        }

        // prepare buyer options
        $buyerOptions = $this->_cloneArrayOfObjects($this->_mainChoices);

        $this->prepareBuyerOptions($buyerOptions);
        $buyerSdkOption->addChoices($buyerOptions);
        $this->addBuyerOption($buyerSdkOption);

        // prepare recipient options
        $recipientOptions = $this->_cloneArrayOfObjects($this->_mainChoices);

        $this->prepareRecipientOptions($recipientOptions);
        $recipientSdkOption->addChoices($recipientOptions);
        $this->addRecipientOption($recipientOptions);
    }

    /**
     * Prepare Recipient options
     *
     * @param $options array
     */
    public function prepareRecipientOptions(& $options) {
        foreach ($options as $key => $option) {

            if (is_array($option->choices)) {
                $this->prepareRecipientOptions($option->choices);
            }

            if ($option->price != 0) {
                unset($options[$key]);
            }
        }
    }

    /**
     * Prepare Buyer options
     *
     * @param $options array
     */
    public function prepareBuyerOptions(& $options) {
        foreach ($options as $key => $option) {

            if (is_array($option->choices)) {
                $this->prepareBuyerOptions($option->choices);
            }

            if (($option->price == 0) && (empty($option->choices))) {
                unset($options[$key]);
            }
        }
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
                $id = $this->_getSdkChoiceId($choiceProducts, $id);

                $nestedChoice = $this->helper->getSdkChoice($id, $option['label'], $this->_roundPrice($option['price']),
                    array('choice_products' => $choiceProducts));
                $parentChoice->addChoice($nestedChoice);
                $choices[] = $nestedChoice;
            }
        }

        if (next($productAttributes)) {
            $this->_addNestedChoices($productAttributes, $choices);
        }
    }

    /**
     * Get choice ID for SDK
     *
     * @param $options
     * @param $id
     * @return string
     */
    protected function _getSdkChoiceId($options, $id) {
        if (count($options) == 1) {
            $productId = array_pop($options);
            $product = Mage::getModel('catalog/product')->load($productId);
            return $product->getSku();
        } else {
            return 'choice_'.$id;
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

    /**
     * Clone array of objects
     *
     * @param array $arrayToClone
     * @return array
     */
    protected function _cloneArrayOfObjects(array $arrayToClone) {
        $clonedArray = array();

        foreach ($arrayToClone as $object) {
            $clonedArray[] = clone $object;
        }

        return $clonedArray;
    }

}
