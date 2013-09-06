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
        $encryptedResponse = $crypt->encode($response, Mage::helper('synocom_giveit')->getDataKey());

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $response = Mage::helper('core')->jsonEncode($encryptedResponse);
        $this->getResponse()->setBody($response);
    }

    public function callbackHandlerAction() {
        define('GIVEIT_DATA_KEY', Mage::helper('synocom_giveit')->getDataKey());

        $giveit = new \GiveIt\SDK;
        $type   = $giveit->getCallbackType($_POST);
//        $result = $giveit->parseCallback($_POST);

        $json = '{
    "id": 1,
    "created_at": "2013-02-01 00:00:00 +0100",
    "updated_at": null,
    "fetched": 0,
    "status": "open",
    "total": 4932,
    "selected_options": null,
    "social_network": "facebook",
    "public_id": "2f4091a8b1a2ace822838cc2952742e9",
    "buyer_id": 1,
    "recipient_id": 2,
    "shipping_address_id": 1,
    "items": [
        {
            "id": 1,
            "created_at": "2013-04-11 14:46:35 +0200",
            "updated_at": null,
            "fetched_at": null,
            "quantity": 1,
            "total": 1333,
            "product": {
                "details": {
                    "code": "ES001",
                    "price": 1233,
                    "name": "ElectroSwing Vol 1",
                    "description": "A blend of swing and electro"
                },
                "options": {
                    "buyer": [
                        {
                            "type": "delivery",
                            "choices": [
                                {
                                    "id": "next_day",
                                    "price": 499,
                                    "name": "Next Day"
                                },
                                {
                                    "id": "super_saver",
                                    "price": 100,
                                    "name": "Super Saver"
                                }
                            ]
                        }
                    ]
                }
            },
            "delivery": null,
            "selected_options": {
                "buyer": {
                    "delivery": "super_saver"
                }
            },
            "commission_percent": null,
            "commission_fee": null,
            "retailer_id": 1,
            "sale_id": 1
        }
    ],
    "buyer": {
        "id": 1,
        "created_at": "2013-04-11 14:46:35 +0200",
        "updated_at": null,
        "first_name": "George",
        "last_name": "Smith",
        "gender": "male",
        "phone": "0610 666 666",
        "email": "george@smith.net",
        "birthday": "1975-01-01",
        "singly_id": null,
        "social_networks": [
            {
                "id": 1,
                "created_at": "2013-04-11 14:46:35 +0200",
                "updated_at": null,
                "type": "twitter",
                "network_id": "george_smith",
                "thumbnail_url": "https://si0.twimg.com/profile_images/407617295/n676896911_9134_bigger.jpg",
                "person_id": 1
            }
        ]
    },
    "recipient": {
        "id": 2,
        "created_at": "2013-04-11 14:46:35 +0200",
        "updated_at": null,
        "first_name": "Bob",
        "last_name": "Jones",
        "gender": "unknown",
        "phone": "+34 123 234",
        "email": "bob@jones.com",
        "birthday": "1980-01-01",
        "singly_id": null,
        "social_networks": [
            {
                "id": 2,
                "created_at": "2013-04-11 14:46:35 +0200",
                "updated_at": null,
                "type": "facebook",
                "network_id": "1234567890",
                "thumbnail_url": "graph.facebook.com/4/picture?width=50&height=50",
                "person_id": 2
            }
        ]
    },
    "shipping_address": {
        "id": 1,
        "created_at": "2013-04-11 14:46:35 +0200",
        "updated_at": null,
        "name": "George Smith",
        "line_1": "123 Somewhere St",
        "line_2": null,
        "line_3": null,
        "line_4": null,
        "city": "Dallas",
        "province": "TX",
        "postal_code": "132678DF",
        "country": "USA",
        "phone": null,
        "email": null,
        "person_id": 1
    },
    "payment":{
        "id": 2,
        "created_at": "2013-04-11 18:15:48 +0200",
        "updated_at": "2013-04-11 18:16:07 +0200",
        "amount": 2850,
        "currency": "USD",
        "commission_amount": 0,
        "provider": "PayPal",
        "user_id": "test@give.it",
        "pre_approval_key": "PA-56453443",
        "reference": "test@give.it",
        "status": "approved",
        "sale_id": 1
     }
}';
        $result = Mage::helper('core')->jsonDecode($json);
        var_dump($result);

        if ($type == 'sale') {

        }
    }

}