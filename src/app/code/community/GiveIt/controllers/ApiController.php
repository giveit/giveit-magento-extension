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
        if (! $this->_helper()->isApiOrderEnabled()) {
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
    public function stockAction()
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
        $json = Mage::helper('core')->jsonEncode($encryptedResponse);

        $this->getResponse()
             ->setHeader('Content-type', 'application/json')
             ->setBody($json)
             ;
    }

    /**
     * Callback handler
     */
    public function notificationsAction()
    {
        define('GIVEIT_DATA_KEY', $this->_helper()->getDataKey());

        // print_r($_POST); exit;

        $giveit     = new \GiveIt\SDK;
        $type       = $giveit->getCallbackType($_POST);
        $response   = $this->getResponse();

        try {
            if ($type == 'sale') {
                $sale = $giveit->parseCallback($_POST);

                if (is_object($sale)) {
                    $order    = Mage::getModel('giveit/order')->createGiveItOrder($sale);
                    $data     = array('status' => 'success', 'id' => $order->getId());

                    Mage::log('successful sale callback');

                } else {

                    $data = array('error' => 'callback decode error - ' . $sale);

                    Mage::log('callback JSON error - ' . $sale);

                    $response->setHttpResponseCode(500);

                }

            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());

            $data = array('error' => $e->getMessage);

            $response->setHttpResponseCode(500);

        }

        $response->setHeader('Content-type', 'application/json')
                 ->setBody(Mage::helper('core')->jsonEncode($data))
                 ;

    }

    public function versionAction()
    {
        $giveit = new \GiveIt\SDK;
        $data   = array(
            'versions' => array(
                'sdk'       => $giveit::VERSION,
                'module'    => (string) Mage::getConfig()->getModuleConfig("GiveIt")->version,
                'magento'   => Mage::getVersion(),
                'php'       => phpversion(),
                'php-sapi'  => php_sapi_name(),
            )
        );

        $response = Mage::helper('core')->jsonEncode($data);

        $this->getResponse()
             ->setHeader('Content-type', 'application/json')
             ->setBody($response)
             ;
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
