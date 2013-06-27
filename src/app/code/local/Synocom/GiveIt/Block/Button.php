<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Brennon Blokland <info@synocom.nl>
 * @author     Mike Bijnsdorp <info@synocom.nl>
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

        if (!$this->isActive()) {
            return $html;
        }

        $this->_setProductDetails();

        $this->_getSdkProduct()->addDeliveryOptions();

        if ($this->_getSdkProduct()->validate()) {
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

        $buttonType = Mage::getStoreConfig('synocom_giveit/button_settings/button_color');

        $buttonHtml = $this->_getSdkProduct()->getButtonHTML($buttonType);
        $buttonJs = $this->outputButtonJS();
        $html = $buttonHtml . $buttonJs;

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
     * @return boolean|GiveIt\SDK\Product return false if no product id
     */
    protected function _setProductDetails()
    {
        $product = $this->getProduct();
        /* @var $product Mage_Catalog_Model_Product */

        if ($product->getId() && $product->isSalable()) {
            $typeId = $product->getTypeId();
            switch ($typeId) {
                case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                    $sdkProduct = Mage::getModel('synocom_giveit/product_type_simple');
                    $sdkProduct->setProductDetails($product);
                    break;
                case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                    $sdkProduct = Mage::getModel('synocom_giveit/product_type_configurable');
                    $sdkProduct->setProductDetails($product);
                    break;
                default:
                    $sdkProduct = Mage::getModel('synocom_giveit/product_type_simple');
                    $sdkProduct->setProductDetails($product);
                    break;
            }
            $this->setSdkProduct($sdkProduct);
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
            $this->setSdkProduct(Mage::getModel('synocom_giveit/product_type_abstract'));
        }
        return $this->getData('sdk_product');
    }

    /**
     * Is button active global
     */
    protected function isActive()
    {
        $product = $this->getProduct();
        $isActive = $product->getGiveitButtonActive();

        if ($isActive == Synocom_GiveIt_Model_Product_Attribute_Source_Button_Active::BUTTON_ACTIVE_USE_CONFIG || $isActive === null) {
            $isActive = Mage::getStoreConfigFlag('synocom_giveit/button_settings/button_active');
        }

        return $isActive;
    }

}
