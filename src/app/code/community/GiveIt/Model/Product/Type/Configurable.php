<?php

/**
 * GiveIt extension
 *

 * @package    GiveIt

 */

/**
 * GiveIt Product class for Magento Configurable products
 */
class GiveIt_Model_Product_Type_Configurable
    extends GiveIt_Model_Product_Type_Abstract
{

    /**
     * @var $helper GiveIt_Helper_Data
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

        $code  = $product->getId();
        $price = $this->_roundPrice($product->getFinalPrice());
        $name  = $product->getName();
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

    protected function stripChoiceProductsField($option)
    {
        if (isset($option->choices)) {
            foreach ($option->choices as &$choice) {
                $choice = $this->stripChoiceProductsField($choice);
            }
        }

        if (isset($option->choice_products)) {
            unset($option->choice_products);
        }

        return $option;
    }

     /**
     * Adds the configurable product options to the SDK product as a buyer option
     */
    protected function _addProductOptions()
    {
        $productOptions = $this->_getProductOptions();
        $this->helper = Mage::helper('giveit');

        $firstAttribute = reset($productOptions['attributes']);

        $sdkOption = new \GiveIt\SDK\Option(array(
                                                'id'            => 'variants',
                                                'type'          => 'layered',
                                                'name'          => $this->helper->__('Variants'),
                                                'choices_title' => $firstAttribute['label'],
                     ));

        /*
         * We use the first attribute as main choice. The products of each option are saved with their choice object
         * as a reference for the nested choices.
         */
        foreach ($firstAttribute['options'] as $id => $option) {

            $choice = new \GiveIt\SDK\Choice(array(
                                               'id'                 => 'choice_' . $option['id'],
                                               'name'               => $option['label'],
                                               'price'              => $this->_roundPrice($option['price']),
                                               'choice_products'    => $option['products'],
                      ));

            $this->_mainChoices[] = $choice;
        }

        if (next($productOptions['attributes'])) {
            $this->_addNestedChoices($productOptions['attributes'], $this->_mainChoices);
        }

        $sdkOption->addChoices($this->_mainChoices);

        $sdkOption = $this->stripChoiceProductsField($sdkOption);

        if ($sdkOption->pricesVary()) {
            $this->addBuyerOption($sdkOption);
        } else {
            $this->addRecipientOption($sdkOption);
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
        $choices   = array();
        $attribute = current($productAttributes);

        foreach ($parentChoices as $parentChoice) {
            foreach ($attribute['options'] as $id => $option) {
                $choiceProducts = array_intersect($option['products'], $parentChoice->choice_products);

                if (empty($choiceProducts)) {
                    continue;
                }

                //The title of this (nested) choice has to be added to its parent
                $parentChoice->choices_title = $attribute['label'];

                $choice = new \GiveIt\SDK\Choice(array(
                                                   'id'              => 'choice_' . $option['id'],
                                                   'product_id'      => $option['products'][0],
                                                   'name'            => $option['label'],
                                                   'price'           => $this->_roundPrice($option['price']),
                                                   'choice_products' => $choiceProducts,
                          ));


                $parentChoice->addChoice($choice);
            }
        }

        // if (next($productAttributes)) {
            // $this->_addNestedChoices($productAttributes, $choices);
        // }
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
