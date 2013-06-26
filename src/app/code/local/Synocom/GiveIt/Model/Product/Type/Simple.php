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
 * GiveIt Product class for Magento Simple products
 */
class Synocom_GiveIt_Model_Product_Type_Simple
    extends Synocom_GiveIt_Model_Product_Type_Abstract
{

    /**
     * Gets data from the simple product and sets it on the SDK product
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProductDetails($product)
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

        parent::setProductDetails($productOptions);
    }

}

