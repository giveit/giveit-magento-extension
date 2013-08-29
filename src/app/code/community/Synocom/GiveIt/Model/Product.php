<?php
/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */

class Synocom_GiveIt_Model_Product extends Mage_Core_Model_Abstract {

    /**
     * Get product stock qty by SKU
     *
     * @param $sku
     * @return array
     * @throws Mage_Exception
     */
    public function getProductStockQty($sku) {
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

        if ($product) {
            switch ($product->getTypeId()) {
                case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                    $productQty = $this->getSimpleProductStockQty($product);
                    break;
                case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                    $productQty = $this->getConfigurableProductStockQty($product);
                    break;
                default:
                    throw new Mage_Exception('Wrong product type: ' . $product->getTypeId());
            }

            return array(
                'product_id'    => $sku,
                'variants'      => $productQty,
            );
        }

        throw new Mage_Exception('Product with SKU: "' . $sku . '" does not exist.');
    }

    /**
     * Get Simple product qty
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int
     * @throws Mage_Exception
     */
    public function getSimpleProductStockQty(Mage_Catalog_Model_Product $product) {
        $productStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

        if ($productStock) {
            return (int)$productStock->getQty();
        }

        throw new Mage_Exception('Product stock does not exist for: ' . $product->getQty());
    }

    /**
     * Get Configurable product qty
     *
     * @param Mage_Catalog_Model_Product_Type_Configurable $product
     * @return array
     */
    public function getConfigurableProductStockQty(Mage_Catalog_Model_Product_Type_Configurable $product) {
        $configurableProduct = Mage::getModel('catalog/product_type_configurable')->setProduct($product);

        $simpleProducts = $configurableProduct->getUsedProductCollection()
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions();

        $productsQty = array();
        foreach ($simpleProducts as $simpleProduct) {
            $productsQty[$simpleProduct->getSku()] = $this->getSimpleProductStockQty($simpleProduct);
        }

        return $productsQty;
    }

}