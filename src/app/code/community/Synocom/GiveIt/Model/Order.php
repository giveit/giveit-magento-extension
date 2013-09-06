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
class Synocom_GiveIt_Model_Order extends Mage_Core_Model_Abstract {

    /**
     * Prepare Quote order for logged in customers
     *
     * @param int $customerId
     * @param array $shoppingCart
     * @param array $shippingAddress
     * @param array $billingAddress
     * @param string $shippingMethod eg. fedexground_fedexground
     * @param string $couponCode
     * @return int $quoteId
     */
    public function prepareCustomerOrder($customerId, array $shoppingCart, array $shippingAddress, array $billingAddress,
                                         $shippingMethod, $couponCode = null)
    {
        // create quote
        $customerObj = Mage::getModel('customer/customer')->load($customerId);
        $storeId = $customerObj->getStoreId();
        $quoteObj = Mage::getModel('sales/quote')->assignCustomer($customerObj);
        $storeObj = $quoteObj->getStore()->load($storeId);
        $quoteObj->setStore($storeObj);

        // add products to quote
        $productModel = Mage::getModel('catalog/product');
        foreach($shoppingCart as $cart_item) {
            $productObj = $productModel->load($cart_item['PartId']);
            $quoteItem = Mage::getModel('sales/quote_item')->setProduct($productObj);
            $quoteItem->setQuote($quoteObj);
            $quoteItem->setQty($cart_item['Quantity']);
            if (isset($cart_item['Price']) && $cart_item['Price']) {
                $quoteItem->setOriginalPrice($cart_item['Price']);
            }
            $quoteObj->addItem($quoteItem);
        }

        // addresses
        $quoteBillingAddress = new Mage_Sales_Model_Quote_Address();
        $quoteBillingAddress->setData($billingAddress);
        $quoteObj->setBillingAddress($quoteBillingAddress);

        if($quoteObj->isVirtual() == 0) {
            $quoteShippingAddress = new Mage_Sales_Model_Quote_Address();
            $quoteShippingAddress->setData($shippingAddress);
            $quoteObj->setShippingAddress($quoteShippingAddress);
        }

        // coupon code
        if(!empty($couponCode)) $quoteObj->setCouponCode($couponCode);

        // shipping method and collect
        if($quoteObj->isVirtual() == 0) {
            $quoteObj->getShippingAddress()->setShippingMethod($shippingMethod);
            $quoteObj->getShippingAddress()->setCollectShippingRates(true);
            $quoteObj->getShippingAddress()->collectShippingRates();
        }
        $quoteObj->collectTotals();	// calls $address->collectTotals();
        $quoteObj->save();

        return $quoteObj->getId();
    }


    /**
     * Prepare Quote order for not logged in customers
     *
     * @param array $params
     * @param string $shippingMethod eg. fedexground_fedexground
     * @param string $couponCode
     * @return int $quoteId
     */
    public function prepareGuestOrder($email, array $shoppingCart, array $shippingAddress, array $billingAddress,
                                      $shippingMethod, $couponCode)
    {
        // create quote
        $quoteObj = Mage::getModel('sales/quote');
        $quoteObj->setIsMultiShipping(false);
        $quoteObj->setCheckoutMethod('guest');
        $quoteObj->setCustomerId(null);
        $quoteObj->setCustomerEmail($email);
        $quoteObj->setCustomerIsGuest(true);
        $quoteObj->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

        // set store
        $quoteObj->setStore(Mage::app()->getStore());

        // add products to quote
        $productModel = Mage::getModel('catalog/product');
        foreach($shoppingCart as $cart_item) {
            $productObj = $productModel->load($cart_item['PartId']);
            $quoteItem = Mage::getModel('sales/quote_item')->setProduct($productObj);
            $quoteItem->setQuote($quoteObj);
            $quoteItem->setQty($cart_item['Quantity']);
            $quoteObj->addItem($quoteItem);
        }

        // addresses
        $quoteShippingAddress = new Mage_Sales_Model_Quote_Address();
        $quoteShippingAddress->setData($shippingAddress);
        $quoteBillingAddress = new Mage_Sales_Model_Quote_Address();
        $quoteBillingAddress->setData($billingAddress);
        $quoteObj->setShippingAddress($quoteShippingAddress);
        $quoteObj->setBillingAddress($quoteBillingAddress);

        // coupon code
        if(!empty($couponCode)) $quoteObj->setCouponCode($couponCode);

        // shipping method an collect
        $quoteObj->getShippingAddress()->setShippingMethod($shippingMethod);
        $quoteObj->getShippingAddress()->setCollectShippingRates(true);
        $quoteObj->getShippingAddress()->collectShippingRates();
        $quoteObj->collectTotals();	// calls $address->collectTotals();
        $quoteObj->save();

        return $quoteObj->getId();
    }

    /**
     * Creates order in Magento for logged in customers
     * Converts Quote to order
     *
     * @param int $quoteId
     * @param string $paymentMethod authorizenet, paypal_express, purchaseorder...
     * @param stdClass $paymentData
     * @return int $orderId
     */
    public function createOrder($quoteId, $paymentMethod, $paymentData)
    {
        $quoteObj = Mage::getModel('sales/quote')->load($quoteId); // Mage_Sales_Model_Quote
        $items = $quoteObj->getAllItems();
        $quoteObj->reserveOrderId();

        // set payment method
        $quotePaymentObj = $quoteObj->getPayment(); // Mage_Sales_Model_Quote_Payment
        $quotePaymentObj->setMethod($paymentMethod);
        $quoteObj->setPayment($quotePaymentObj);

        // convert quote to order
        $convertQuoteObj = Mage::getSingleton('sales/convert_quote');

        if($quoteObj->isVirtual() == 0) {
            $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
        } else {
            $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getBillingAddress());
        }

        $orderPaymentObj = $convertQuoteObj->paymentToOrderPayment($quotePaymentObj);

        // convert quote addresses
        $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
        if($quoteObj->isVirtual() == 0) {
            $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
        }
        // set payment options
        $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
        if ($paymentData) {
            $orderObj->getPayment()->setCcNumber($paymentData->ccNumber);
            $orderObj->getPayment()->setCcType($paymentData->ccType);
            $orderObj->getPayment()->setCcExpMonth($paymentData->ccExpMonth);
            $orderObj->getPayment()->setCcExpYear($paymentData->ccExpYear);
            $orderObj->getPayment()->setCcLast4(substr($paymentData->ccNumber,-4));
        }
        // convert quote items
        foreach ($items as $item) {
            // @var $item Mage_Sales_Model_Quote_Item
            $orderItem = $convertQuoteObj->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $orderObj->addItem($orderItem);
        }

        $orderObj->setCanShipPartiallyItem(false);

        try {
            $orderObj->place();
        } catch (Exception $e){
            Mage::log($e->getMessage());
            Mage::log($e->getTraceAsString());
        }

        $orderObj->save();
        //$orderObj->sendNewOrderEmail();
        return $orderObj->getId();
        unset ($orderObj, $quoteObj);
    }


    /**
     * Create invoice for selected order
     *
     * @param int $orderId order id
     */
    public function createInvoiceForOrder($orderId, $commentToInvoice, $notifyCustomer = false) {
        // Load Order
        $order = Mage::getModel('sales/order')->load($orderId);

        // Conver order to invoice
        $convertor  = Mage::getModel('sales/convert_order');
        $invoice    = $convertor->toInvoice($order);

        /* @var $orderItem Mage_Sales_Model_Order_Item */
        foreach ($order->getAllItems() as $orderItem) {

            if (!$orderItem->isDummy() && !$orderItem->getQtyToInvoice() && $orderItem->getLockedDoInvoice()) {
                continue;
            }

            if ($order->getForcedDoShipmentWithInvoice() && $orderItem->getLockedDoShip()) {
                continue;
            }

            $item = $convertor->itemToInvoiceItem($orderItem);

            if ($orderItem->isDummy()) {
                $qty = 1;
            } else {
                $qty = $orderItem->getQtyToInvoice();
            }

            $item->setQty($qty);
            $invoice->addItem($item);
        }
        $invoice->collectTotals();

        /* Text, Need Notify customer */
        $invoice->addComment($commentToInvoice, $notifyCustomer);
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transactionSave->save();
    }

    public function createShippingForOrder($orderId, $commentToShipping, $notifyCustomer = false) {
        // Load Order
        $order = Mage::getModel('sales/order')->load($orderId);

        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
            return false;
        }

        // Convert order to shipping
        $convertor  = Mage::getModel('sales/convert_order');
        $shipment    = $convertor->toShipment($order);

        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->isDummy(true) && !$orderItem->getQtyToShip()) {
                continue;
            }

            if ($orderItem->getIsVirtual()) {
                continue;
            }
            $item = $convertor->itemToShipmentItem($orderItem);

            if ($orderItem->isDummy(true)) {
                $qty = 1;
            } else {
                $qty = $orderItem->getQtyToShip();
            }

            $item->setQty($qty);
            $shipment->addItem($item);
        }

        $shipment->register();

        if ($commentToShipping!='') {
            $shipment->addComment($commentToShipping, $notifyCustomer);
        }


        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();
    }

}
