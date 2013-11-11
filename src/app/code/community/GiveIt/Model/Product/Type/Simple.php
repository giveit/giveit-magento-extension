<?php

/**
 * GiveIt extension
 *

 * @package    GiveIt

 */

/**
 * GiveIt Product class for Magento Simple products
 */
class GiveIt_Model_Product_Type_Simple
    extends GiveIt_Model_Product_Type_Abstract
{

    /**
     * Gets data from the simple product and sets it on the SDK product
     *
     * @param array $productArray
     */
    public function setProductDetails($productArray = array())
    {
        $product = reset($productArray);
        /* @var $product Mage_Catalog_Model_Product */

        // $code = $product->getSku();
        $code  = $product->getId();
        $price = $this->_roundPrice($product->getFinalPrice());
        $name  = $product->getName();
        $image = $product->getImageUrl();

        $productOptions = array(
            'code'  => $code,
            'price' => $price,
            'name'  => $name,
            'image' => $image
        );

        parent::setProductDetails($productOptions);
    }

}

