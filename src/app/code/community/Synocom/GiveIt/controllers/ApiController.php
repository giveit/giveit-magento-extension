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

    public function callbackHandlerAction() {
        define('GIVEIT_DATA_KEY', $this->_helper()->getDataKey());

        $giveit = new \GiveIt\SDK;
        $type   = $giveit->getCallbackType($_POST);
        $result = $giveit->parseCallback($_POST);

        try {
            if ($type == 'sale') {
                $sale = Mage::getModel('synocom_giveit/giveit_sale');
                $sale->setObject($result);
                Mage::getModel('synocom_giveit/order')->createGiveItOrder($sale);
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }

    }

    protected function _helper() {
        return Mage::helper('synocom_giveit');
    }
}