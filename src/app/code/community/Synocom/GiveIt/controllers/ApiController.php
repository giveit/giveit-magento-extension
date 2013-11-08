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
require_once 'lib/giveit/sdk.php';

class Synocom_GiveIt_ApiController extends Mage_Core_Controller_Front_Action {

    /**
     * Check if this methods in this controller are accessible
     */
    protected function _construct() {
        if (!$this->_helper()->isApiOrderEnabled()) {
            Mage::getModel('synocom_giveit/response')->setMimeType('application/json')
                ->setHttpResponseCode(403)
                ->setBody(Zend_Json::encode(array('error' => $this->__('This option is disabled.'))))
                ->sendResponse();
            exit;
        }
    }

    /**
     * Get product stock qty
     */
    public function getProductStockQtyAction() {
        $response = null;

        try {
            if ($sku = $this->getRequest()->getParam('product_id')) {
                $response = Mage::getModel('synocom_giveit/product')->getProductStockQty($sku);
            } else {
                throw new Mage_Exception('Wrong request, product_id is missing.');
            }
        } catch (Mage_Exception $e) {
            Mage::log('Exception while calling Synocom_GiveIt_ApiController::getProductStockAction()');
            Mage::log('product_id: ' . $sku . ' Error: ' . $e->getMessage());
            $response = array('error' => $e->getMessage());
        }

        $crypt = \GiveIt\SDK\Crypt::getInstance();
        $encryptedResponse = $crypt->encode($response, $this->_helper()->getDataKey());

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $response = Mage::helper('core')->jsonEncode($encryptedResponse);
        $this->getResponse()->setBody($response);
    }

    /**
     * Callback handler
     */
    public function callbackHandlerAction() {
        define('GIVEIT_DATA_KEY', $this->_helper()->getDataKey());

        // print_r($_POST); exit;

        $giveit = new \GiveIt\SDK;
        $type   = $giveit->getCallbackType($_POST);

        try {
            if ($type == 'sale') {
                $result = $giveit->parseCallback($_POST);
              //  $result = Zend_Json::decode(Zend_Json::encode($result));

                // $sale = Mage::getModel('synocom_giveit/giveit_sale');
                // $sale->setObject($result);
                $order = Mage::getModel('synocom_giveit/order')->createGiveItOrder($result);
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            Mage::log(Zend_Json::encode($result));

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $response = Mage::helper('core')->jsonEncode($e->getMessage());
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Get data helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    protected function _helper() {
        return Mage::helper('synocom_giveit');
    }
}
