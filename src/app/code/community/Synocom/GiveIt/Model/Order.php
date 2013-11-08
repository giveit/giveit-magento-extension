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

    protected function getSelectedVariantProductId($item)
    {
        $chosen_options = array();

        if (isset($item->selected_options->buyer->variants)) {
            $chosen_options =  $item->selected_options->buyer->variants;
        }

        if (isset($item->selected_options->recipient->variants)) {
            $chosen_options =  $item->selected_options->recipient->variants;
        }

        if (empty($chosen_options)) {
            return 0;
        }

        foreach ($chosen_options as $option) {
            if (isset($option->product_id)) {
                return $option->product_id;
            }
        }

        return 0;

    }

    protected function getNames($name)
    {
        return split($name, ' ', 1);
    }

    public function createGiveItOrder($sale) {
print_r($sale);
        $shoppingCart = array();

        foreach ($sale->items as $item) {

            $shoppingCart[] = array(
                                'qty'        => $item->quantity,
                                'product_id' => $this->getSelectedVariantProductId($item),
                              );
        }

        $delivery               = $item->delivery;
        $shippingAddress        = $sale->shipping_address;
        $shippingDescription    = $delivery->name;
        $shippingPrice          = $delivery->price / 100;
        $names                  = split(' ', $shippingAddress->name, 1);

        $orderShippingAddress   = array(
            'firstname'             => $names[0],
            'lastname'              => $names[1],
            'country_id'            => $shippingAddress->country_code,
            'region_id'             => '',
            'region'                => $shippingAddress->province,
            'city'                  => $shippingAddress->city,
            'street'                => $shippingAddress->line_1,
            'telephone'             => $shippingAddress->phone,
            'postcode'              => $shippingAddress->postal_code,
            'save_in_address_book'  => 0,
            'is_default_billing'    => false,
            'is_default_shipping'   => true,
            'prefix'                => '',
            'middlename'            => '',
            'suffix'                => '',
            'company'               => '',
            'fax'                   => ''
        );

        $billingAddress = $orderShippingAddress;
        $billingAddress['is_default_billing']  = true;
        $billingAddress['is_default_shipping'] = false;

        $shippingMethod = 'freeshipping_freeshipping';

        $quote = $this->createQuote($shippingAddress->email, $shoppingCart, $orderShippingAddress, $billingAddress, $shippingMethod, false);

        $quote->setShippingAmount($shippingPrice);
        $quote->setBaseShippingAmount($shippingPrice);
        $quote->setShippingInclTax($shippingPrice);
        $quote->setBaseShippingInclTax($shippingPrice);
        $quote->setShippingDescription($shippingDescription);

        $quote->save();

        Mage::log("created quote #" . $quote->getId());

        $giveitPaymentMethod = Mage::getModel('synocom_giveit/method_giveit');
        $paymentMethod = $giveitPaymentMethod->getCode();

        $order =  $this->createOrder($quote->getId(), $paymentMethod, null, $shippingDescription, $shippingPrice);

        Mage::log("created order #" . $order->getId());

        return $order;
    }

    /**
     * Create order base on Giveit Sale
     *
     * @param Synocom_GiveIt_Model_Giveit_Sale $sale
     * @return int
    public function OLD_createGiveItOrder(Synocom_GiveIt_Model_Giveit_Sale $sale) {
        $shoppingCart = array();

        foreach ($sale->getItems() as $item) {
            $product = array(
                            'qty'        => $item->getQuantity(),
                            'product_id' => $item->getSelectedOptions()
                                                 ->getRecipient()
                                                 ->getVariants()
                                                 ->getProductId(),
                        );

            $shoppingCart[] = $product;
        }

        $shippingDescription = $item->getDelivery()->getDescription();
        $shippingPrice = $item->getDelivery()->getFloatPrice();

        $saleShippingAddress = $sale->getShippingAddress();
        $email = $saleShippingAddress->getEmail();

        $shippingAddress = array(
            'firstname'             => $saleShippingAddress->getFirstName(),
            'lastname'              => $saleShippingAddress->getLastName(),
            'country_id'            => $saleShippingAddress->getCountryCode(),
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
            'company'               => '',
            'fax'                   => ''
        );

        $billingAddress = $shippingAddress;
        $billingAddress['is_default_billing'] = true;
        $billingAddress['is_default_shipping'] = false;

        $shippingMethod = 'freeshipping_freeshipping';

        $quoteId = $this->prepareGuestOrder($email, $shoppingCart, $shippingAddress, $billingAddress, $shippingMethod, false);

        $giveitPaymentMethod = Mage::getModel('synocom_giveit/method_giveit');
        $paymentMethod = $giveitPaymentMethod->getCode();
        return $this->createOrder($quoteId, $paymentMethod, null, $shippingDescription, $shippingPrice);
    }
*/

    /**
     * Prepare Quote order for not logged in customers
     *
     * @param array $params
     * @param string $shippingMethod eg. fedexground_fedexground
     * @param string $couponCode
     * @return int $quoteId
     */
    public function createQuote($email, array $shoppingCart, array $shippingAddress, array $billingAddress,
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

        return $quote;
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
    public function createOrder($quoteId, $paymentMethod, $paymentData, $shippingDescription, $shippingPrice) {
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

        $order->setShippingAmount($shippingPrice);
        $order->setBaseShippingAmount($shippingPrice);
        $order->setShippingInclTax($shippingPrice);
        $order->setBaseShippingInclTax($shippingPrice);
        $order->setShippingDescription($shippingDescription);

        $order->save();
        $order->sendNewOrderEmail();

        return $order;
    }
}
