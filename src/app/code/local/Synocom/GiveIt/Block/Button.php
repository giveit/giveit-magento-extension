<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Brennon Blokland <info@synocom.nl>
 */
class Synocom_GiveIt_Block_Button
    extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->_getSdk();
    }
    /**
     * Get give it SDK
     */
    protected function _getSdk()
    {
        return Mage::getSingleton('synocom_giveit/giveIt');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';

        $this->_setProductDetails();
        $this->_addDeliveryOptions();

        if ($this->_validate()) {
            $html = $this->_renderButton();
        }

        return $html;
    }

    /**
     * Render button
     *
     * @return string
     */
    protected function _renderButton()
    {
        $html = '';

        $buttonHtml = $this->_getSdkProduct()->getButtonHTML();
        $buttonJs   = $this->outputButtonJS();
        $html       = $buttonHtml . $buttonJs;

        return $html;
    }

    /**
     * This function outputs the JS inclusion for the button
     *
     * @return boolean
     */
    public function outputButtonJS()
    {
        $sdk = $this->_getSdk();
        if (!$sdk->jsOutput) {
            $sdk->jsOutput = true;
            return $sdk->getButtonJS();
        }
    }

    /**
     * Get current product used in product info
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if ($this->hasProduct()) {
            return $this->getData('product');
        }

        $productInfoBlock = $this->getLayout()->getBlock('product.info');
        if (!$productInfoBlock) {
            return false;
        }

        $product = $productInfoBlock->getProduct();
        if (!$product->getId()) {
            return false;
        }

        $this->setProduct($product);

        return $product;
    }

    /**
     * Set product details to SDK product
     *
     * @return boolean
     */
    protected function _setProductDetails()
    {
        $product = $this->getProduct();
        /* @var $product Mage_Catalog_Model_Product */

        if ($product->getId()) {
            $code  = $product->getSku();
            $price = (int) round($product->getFinalPrice() * 100, 0);
            $name  = $product->getName();
            $image = $product->getImageUrl();

            $this->_getSdkProduct()->setProductDetails($code, $price, $name, $image);
        }

    }

    /**
     * Get an instance of the Give it SDK product object
     *
     * @return \GiveIt\SDK\Product
     */
    protected function _getSdkProduct()
    {
        if (!$this->hasSdkProduct()) {
            $this->setSdkProduct(new \GiveIt\SDK\Product());
        }
        return $this->getData('sdk_product');
    }

    /**
     * Add delivery options to product
     *
     * @return void
     */
    protected function _addDeliveryOptions()
    {
        $sdkProduct = $this->_getSdkProduct();

        $delivery = $sdkProduct->addDeliveryOption('delivery', 'Delivery Option');
        $sdkProduct->addChoice($delivery, 'europe', 'Europe', 900);
        $sdkProduct->addChoice($delivery, 'usa', 'United States', 499);
    }

    /**
     * Validate the sdk product
     *
     * @return boolean
     */
    protected function _validate()
    {
        $result = $this->_getSdkProduct()->validate();
        if (!$result) {
            Mage::log($this->_getSdkProduct()->getErrorsHTML());
        }
        return $result;
    }

}