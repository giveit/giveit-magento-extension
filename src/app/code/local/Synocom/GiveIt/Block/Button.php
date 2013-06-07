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

    const MAX_DELIVERY_OPTIONS        = 8;
    const MAX_DELIVERY_OPTION_CHOICES = 8;

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

        if(!$this->isActive()){
           return $html;
        }

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

        $buttonType  = Mage::getStoreConfig('synocom_giveit/button_settings/button_color');

        $buttonHtml = $this->_getSdkProduct()->getButtonHTML($buttonType);
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
            $price = $this->_roundPrice($product->getFinalPrice());
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
        $xmlPathTemplate = 'synocom_giveit/delivery_option_%s';

        $sdkProduct = $this->_getSdkProduct();

        $delivery = $sdkProduct->addDeliveryOption('delivery', 'Delivery Option');
        foreach (range(1, self::MAX_DELIVERY_OPTIONS) as $i) {

            $xmlPath = sprintf($xmlPathTemplate, $i);
            $config  = Mage::getStoreConfig($xmlPath);

            if ($config) {
                $id    = $config['delivery_option_choice'];
                $name  = $config['delivery_option_name'];
                $price = $this->_roundPrice($config['delivery_option_price']);

                if (empty($id)) {
                    return false;
                }

                if (empty($name)) {
                    return false;
                }

                if (empty($price)) {
                    return false;
                }

                $sdkProduct->addChoice($delivery, $id, $name, $price);
            }
        }
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

    /**
     * Round price to thousands
     *
     * @param $price float
     *
     * @return int
     */
    protected function _roundPrice($price)
    {
        $currency = Mage::getModel('core/store')->getCurrentCurrency();
        $price    = $currency->format($price, array('symbol' => ''), false);
        $price   = (int) ($price * 100);
        return $price;
    }

    /**
     * Is button active global
     */
    protected function isActive()
    {
        $isActive = Mage::getStoreConfigFlag('synocom_giveit/button_settings/button_active');
        return $isActive;
    }

}