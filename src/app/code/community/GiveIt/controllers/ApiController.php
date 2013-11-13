<?php
/**
 * GiveIt extension
 *

 * @package    GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
require_once 'lib/giveit/sdk.php';

class GiveIt_ApiController extends Mage_Core_Controller_Front_Action {

    /**
     * Check if this methods in this controller are accessible
     */
    protected function _construct() {
        if (!$this->_helper()->isApiOrderEnabled()) {
            Mage::getModel('giveit/response')->setMimeType('application/json')
                ->setHttpResponseCode(403)
                ->setBody(Zend_Json::encode(array('error' => $this->__('This option is disabled.'))))
                ->sendResponse();
            exit;
        }
    }

    /**
     * Get product stock qty
     */
    public function getProductStockQtyAction()
    {
        $response = null;

        try {
            if ($sku = $this->getRequest()->getParam('product_id')) {
                $response = Mage::getModel('giveit/product')->getProductStockQty($sku);
            } else {
                throw new Mage_Exception('Wrong request, product_id is missing.');
            }
        } catch (Mage_Exception $e) {
            Mage::log('Exception while calling GiveIt_ApiController::getProductStockAction()');
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
    public function notificationsAction()
    {
        define('GIVEIT_DATA_KEY', $this->_helper()->getDataKey());

        // print_r($_POST); exit;

        $giveit = new \GiveIt\SDK;
        $type   = $giveit->getCallbackType($_POST);

        $this->getResponse()->setHeader('Content-type', 'application/json');

        try {
            if ($type == 'sale') {
                $sale = $giveit->parseCallback($_POST);
                $order = Mage::getModel('giveit/order')->createGiveItOrder($sale);

                 $response = Mage::helper('core')->jsonEncode(array('status' => 'created', 'id' => $order->getId()));

            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            Mage::log(Zend_Json::encode($result));

            $response = Mage::helper('core')->jsonEncode($e->getMessage());
            $this->getResponse()->setBody($response);
        }
    }

     public function versionAction()
    {
        $giveit = new \GiveIt\SDK;

        $data = array(
            'versions' => array(
                'sdk'       => $giveit::VERSION,
                'magento'   => Mage::getVersion(),
            )
        );

        $response = Mage::helper('core')->jsonEncode($data);

        $this->getResponse()->setBody($response)->setHeader('Content-type', 'application/json');
    }


    /**
     * Get data helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    protected function _helper() {
        return Mage::helper('giveit');
    }
}
