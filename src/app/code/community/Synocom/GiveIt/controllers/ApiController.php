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

class Synocom_GiveIt_ApiController extends Mage_Core_Controller_Front_Action {

    /**
     * Get product stock qty
     */
    public function getProductStockQtyAction() {
        $response = null;

        try {
            if ($sku = $this->getRequest()->getParam('product_id')) {
                $response = Mage::getModel('synocom_giveit/product')->getProductStockQty($sku);
            }

            throw new Mage_Exception('Wrong request, product_id is missing.');
        } catch (Mage_Exception $e) {
            Mage::log('Exception while calling Synocom_GiveIt_ApiController::getProductStockAction()');
            Mage::log('product_id: ' . $sku . ' Error: ' . $e->getMessage());
            $response = array('error' => $e->getMessage());
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $response = Mage::helper('core')->jsonEncode($response);
        $this->getResponse()->setBody($response);
    }

}