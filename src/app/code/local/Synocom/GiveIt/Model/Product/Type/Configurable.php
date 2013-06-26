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
     * Gets data from the configurable product and sets it on the SDK product
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProductDetails($product)
    {
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
        $helper = Mage::helper('synocom_giveit');

        foreach ($productOptions['attributes'] as $attribute) {
            $sdkOption = $helper->getSdkOption($attribute['code'], 'single_choice', $attribute['label']);
            foreach ($attribute['options'] as $option) {
                $sdkChoice = $helper->getSdkChoice($option['id'], $option['label'], $this->_roundPrice($option['price']));
                $sdkOption->addChoice($sdkChoice);
            }
            $this->addBuyerOption($sdkOption);
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
