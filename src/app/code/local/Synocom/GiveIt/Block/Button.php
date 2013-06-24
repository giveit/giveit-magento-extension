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
            $typeId = $product->getTypeId();
            switch ($typeId) {
                case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                    $this->_setSimpleProduct($product);
                    break;
                default:
                    $this->_setSimpleProduct($product);
                    break;
            }
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
     * Get an instance of the Give it SDK option object
     *
     * @param string $id
     * @param string $type
     * @param string $name
     * @param array $optional optional options. Optional key is used as index, its value as value
     * @return \GiveIt\SDK\Option
     */
    protected function _getSdkOption($id, $type, $name, $optional = array())
    {
        $options = array(
            'id'   => $id,
            'type' => $type,
            'name' => $name
        );

        if(!empty($optional)){
            foreach($optional as $key => $value){
                $options[$key] = $value;
            }
        }

        return new \GiveIt\SDK\Option($options);
    }

    /**
     * Get an instance of the Give it SDK choice object
     *
     * @param string $id
     * @param string $name
     * @param int $price
     * @return \GiveIt\SDK\Choice
     */
    protected function _getSdkChoice($id, $name, $price)
    {
        return new \GiveIt\SDK\Choice(array(
                'id'    => $id,
                'name'  => $name,
                'price' => $price
                )
        );
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

        $delivery = $this->_getSdkOption('giveit', 'delivery', 'Delivery Option', array('tax_delivery' => true));
        $choices = array();
        foreach (range(1, self::MAX_DELIVERY_OPTIONS) as $i) {

            $xmlPath = sprintf($xmlPathTemplate, $i);
            $config  = Mage::getStoreConfig($xmlPath);

            if ($config) {
                $id    = $config['delivery_option_choice'];
                $name  = $config['delivery_option_name'];
                $price = $this->_roundPrice($config['delivery_option_price']);

                if (empty($id)) {
                    continue;
                }

                if (empty($name)) {
                    continue;
                }

                if (empty($price)) {
                    continue;
                }

                $choices[] = $this->_getSdkChoice($id, $name, $price);
            }
        }
        $delivery->addChoices($choices);
        $sdkProduct->addBuyerOption($delivery);
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
        $product = $this->getProduct();
        $isActive = $product->getGiveitButtonActive();

        if($isActive == Synocom_GiveIt_Model_Product_Attribute_Source_Button_Active::BUTTON_ACTIVE_USE_CONFIG || $isActive === null){
            $isActive = Mage::getStoreConfigFlag('synocom_giveit/button_settings/button_active');
        }

        return $isActive;
    }

    /**
     * Set SDK product details for a simple product
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setSimpleProduct($product)
    {
        $code = $product->getSku();
        $price = $this->_roundPrice($product->getFinalPrice());
        $name = $product->getName();
        $image = $product->getImageUrl();

        $productOptions = array(
            'code'  => $code,
            'price' => $price,
            'name'  => $name,
            'image' => $image
        );

        $this->_getSdkProduct()->setProductDetails($productOptions);
    }

}
