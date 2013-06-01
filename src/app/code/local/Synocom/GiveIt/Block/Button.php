<?php

class Synocom_GiveIt_Block_Button
    extends Mage_Core_Block_Template
{

    /**
     * Factory method loads the GiveIt SDK
     */
    protected function _construct()
    {
        $this->setSdk(Mage::getSingleton('synocom_giveit/giveIt'));
    }

    protected function _toHtml()
    {
        $this->_setProductDetails();
        $this->_addDeliveryOptions();

        if ($this->_validate()) {
            echo $this->getSdkProduct()->getButtonHTML();
            $this->getSdk()->outputButtonJS();
        }
    }

    /**
     * Get current product
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
        if (!$product) {
            return false;
        }

        if ($product->getId()) {
            $code  = $product->getSku();
            $price = (int) round($product->getPrice() * 100, 0);
            $name  = $product->getName();
            $image = $product->getImageUrl();

            $this->getSdkProduct()->setProductDetails($code, $price, $name, $image);
        }
    }

    /**
     * Get an instance of the Give it SDK product object
     *
     * @return type
     */
    protected function getSdkProduct()
    {
        if (!$this->hasSdkProduct()) {
            $this->setSdkProduct(new \GiveIt\SDK\Product());
        }
        return $this->getData('sdk_product');
    }

    /**
     * Add delivery options to product
     */
    protected function _addDeliveryOptions()
    {
        $sdkProduct = $this->getSdkProduct();

        $delivery = $sdkProduct->addDeliveryOption('delivery', 'Delivery Option');
        $sdkProduct->addChoice($delivery, 'europe', 'Europe', 900);
        $sdkProduct->addChoice($delivery, 'usa', 'United States', 499);
    }

    protected function _validate()
    {
        $result = $this->getSdkProduct()->validate();
        if (!$result) {
            Mage::log($this->getSdkProduct()->getErrorsHTML());
        }
        return $result;
    }

}