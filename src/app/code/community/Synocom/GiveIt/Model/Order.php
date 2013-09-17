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
class Synocom_GiveIt_Model_Order extends Mage_Sales_Model_Order {

    public function createGiveItOrder(Synocom_GiveIt_Model_Giveit_Sale $sale) {
        $shoppingCart = array();
        foreach ($sale->getItems() as $item) {
            $product = array();
//            if ($item->getSelectedOptions()) {
//                $options = $item->getProduct()->getOptions();
//                $product['product_id'] = $options->get{ucfirst($options->getId())}();
//            } else {
                $product['product_id'] = $item->getProduct()->getDetails()->getCode();
//            }

            $product['qty'] = $item->getQuantity();
            $shoppingCart[] = $product;
        }

        $saleShippingAddress = $sale->getShippingAddress();
        $email = $saleShippingAddress->getEmail();

        $shippingAddress = array(
            'firstname'             => $saleShippingAddress->getFirstName(),
            'lastname'              => $saleShippingAddress->getLastName(),
            'country_id'            => $saleShippingAddress->getCountry(),
            'region_id'             => '',
            'region'                => $saleShippingAddress->getProvince(),
            'city'                  => $saleShippingAddress->getCity(),
            'street'                => $saleShippingAddress->getStreet(),
            'telephone'             => $sale->getPhone(),
            'postcode'              => $sale->getPostalCode(),
            'save_in_address_book'  => 0,
            'is_default_billing'    => false,
            'is_default_shipping'   => true,
            'prefix'                => '',
            'middlename'            => '',
            'suffix'                => '',
            /**
             * TODO fill this field when final JSON sale response will be provided
             */
            'company'               => '',
            'fax'                   => ''
        );
//        var_dump($shippingAddress);die;

        $billingAddress = $shippingAddress;
        $billingAddress['is_default_billing'] = true;
        $billingAddress['is_default_shipping'] = false;

        $shippingMethod = 'freeshipping_freeshipping';

//        var_dump($email, $shoppingCart, $shippingAddress, $billingAddress, $shippingMethod);die;
        $quoteId = $this->prepareGuestOrder($email, $shoppingCart, $shippingAddress, $billingAddress, $shippingMethod, false);

        $giveitPaymentMethod = Mage::getModel('synocom_giveit/method_giveit');
        $paymentMethod = $giveitPaymentMethod->getCode();
        $this->createOrder($quoteId, $paymentMethod, null);

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
        $quote = Mage::getModel('sales/quote');
        $quote->setIsMultiShipping(false);
        $quote->setCheckoutMethod('guest');
        $quote->setCustomerId(null);
        $quote->setCustomerEmail($email);
        $quote->setCustomerIsGuest(true);
        $quote->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

        $quote->setStore(Mage::app()->getStore());

        $productModel = Mage::getModel('catalog/product');
        foreach($shoppingCart as $cartItem) {
            $product = $productModel->load($cartItem['product_id']);
            $quoteItem = Mage::getModel('sales/quote_item')->setProduct($product);
            $quoteItem->setQuote($quote);
            $quoteItem->setQty($cartItem['qty']);

            $quote->addItem($quoteItem);
        }

        $quoteShippingAddress = new Mage_Sales_Model_Quote_Address();
        $quoteShippingAddress->setData($shippingAddress);
        $quoteShippingAddress->setFreeShipping(true);
        $quoteBillingAddress = new Mage_Sales_Model_Quote_Address();
        $quoteBillingAddress->setData($billingAddress);
        $quote->setShippingAddress($quoteShippingAddress);
        $quote->setBillingAddress($quoteBillingAddress);

        if (!empty($couponCode)) {
            $quote->setCouponCode($couponCode);
        }

        $quote->getShippingAddress()->setShippingMethod($shippingMethod);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
        $quote->collectTotals();	// calls $address->collectTotals();
        $quote->save();

        return $quote->getId();
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
    public function createOrder($quoteId, $paymentMethod, $paymentData) {
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $items = $quote->getAllItems();
        $quote->reserveOrderId();

        $quotePayment = $quote->getPayment();
        $quotePayment->setMethod($paymentMethod);
        $quote->setPayment($quotePayment);

        // convert quote to order
        $convertQuoteObj = Mage::getSingleton('sales/convert_quote');

        if($quote->isVirtual() == 0) {
            $order = $convertQuoteObj->addressToOrder($quote->getShippingAddress());
        } else {
            $order = $convertQuoteObj->addressToOrder($quote->getBillingAddress());
        }

        $orderPaymentObj = $convertQuoteObj->paymentToOrderPayment($quotePayment);

        // convert quote addresses
        $order->setBillingAddress($convertQuoteObj->addressToOrderAddress($quote->getBillingAddress()));
        if($quote->isVirtual() == 0) {
            $order->setShippingAddress($convertQuoteObj->addressToOrderAddress($quote->getShippingAddress()));
        }
        // set payment options
        $order->setPayment($convertQuoteObj->paymentToOrderPayment($quote->getPayment()));
        if ($paymentData) {
            $order->getPayment()->setCcNumber($paymentData->ccNumber);
            $order->getPayment()->setCcType($paymentData->ccType);
            $order->getPayment()->setCcExpMonth($paymentData->ccExpMonth);
            $order->getPayment()->setCcExpYear($paymentData->ccExpYear);
            $order->getPayment()->setCcLast4(substr($paymentData->ccNumber,-4));
        }
        // convert quote items
        foreach ($items as $item) {
            // @var $item Mage_Sales_Model_Quote_Item
            $orderItem = $convertQuoteObj->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        $order->setCanShipPartiallyItem(false);

        try {
            $order->place();
        } catch (Exception $e){
            Mage::log($e->getMessage());
            Mage::log($e->getTraceAsString());
        }

        /** TODO chang with reall data */
//        $shippingAmount = 9;
//        $order->setShippingAmount($shippingAmount);
//        $order->setBaseShippingAmount($shippingAmount);
//        $order->setShippingInclTax($shippingAmount);
//        $order->setBaseShippingInclTax($shippingAmount);
//        $order->setShippingDescription('Give it shipping description');

        $order->save();
        $order->sendNewOrderEmail();

        return $order->getId();
    }
}
