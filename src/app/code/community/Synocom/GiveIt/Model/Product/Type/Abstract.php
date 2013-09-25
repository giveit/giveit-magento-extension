<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Mike Bijnsdorp <info@synocom.nl>
 */

require_once 'lib/giveit/classes/Product.php';

/**
 * Wrapper class for Giveit SDK Product
 */
class Synocom_GiveIt_Model_Product_Type_Abstract
    extends \GiveIt\SDK\Product
{

    const MAX_DELIVERY_OPTIONS = 8;
    const MAX_DELIVERY_OPTION_CHOICES = 8;

    /**
     * Add delivery options to product
     *
     * @return void
     */
    public function addDeliveryOptions()
    {
        $xmlPathTemplate = 'synocom_giveit/delivery_option_%s';

        $delivery = Mage::helper('synocom_giveit')->getSdkOption('giveit', 'delivery', 'Delivery Option');
        $choices = array();
        foreach (range(1, self::MAX_DELIVERY_OPTIONS) as $i) {

            $xmlPath = sprintf($xmlPathTemplate, $i);
            $config = Mage::getStoreConfig($xmlPath);

            if ($config) {
                $id = $config['delivery_option_choice'];
                $name = $config['delivery_option_name'];
                $price = $this->_roundPrice($config['delivery_option_price']);
                $taxPercentage = $config['delivery_option_tax_percentage'];

                if (empty($id)) {
                    continue;
                }

                if (empty($name)) {
                    continue;
                }

                if (empty($price) && $price !== 0) {
                    continue;
                }

                if ($taxPercentage < 0 || $taxPercentage > 100) {
                    continue;
                }

                $choices[] = Mage::helper('synocom_giveit')->getSdkChoice($id, $name, $price, array(
                        'tax_percent' => (int)$taxPercentage,
                    )
                );
            }
        }

        $delivery->addChoices($choices);
        $this->addBuyerOption($delivery);
    }

    /**
     * Round price to thousands
     *
     * @param $price float
     *
     * @return int
     */
    protected function _roundPrice($price)
    {
        $currency = Mage::getModel('core/store')->getCurrentCurrency();
        $price = $currency->format($price, array('symbol' => ''), false);
        $price = (int) ($price * 100);
        return $price;
    }

    /**
     * Validate the sdk product
     *
     * @return boolean
     */
    public function validate()
    {
        $result = parent::validate();
        if (!$result) {
            Mage::log($this->getErrorsHTML());
        }
        return $result;
    }

}
