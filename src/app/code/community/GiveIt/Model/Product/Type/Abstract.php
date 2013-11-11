<?php

/**
 * GiveIt extension
 *

 * @package    GiveIt

 */

require_once 'lib/giveit/classes/Product.php';

/**
 * Wrapper class for Giveit SDK Product
 */
class GiveIt_Model_Product_Type_Abstract
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
        $xmlPathTemplate = 'giveit/delivery_option_%s';

        $delivery = new \GiveIt\SDK\Option(array(
                                                  'id'          => 'delivery',
                                                  'type'        => 'delivery',
                                                  'name'        => 'Delivery'
                    ));

        $choices  = array();

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

                $choice = new \GiveIt\SDK\Choice(array(
                                                    'id'            => $id,
                                                    'name'          => $name,
                                                    'price'         => $price,
                                                    'tax_percent'   => (int)$taxPercentage,
                          ));

                $delivery->addChoice($choice);
            }
        }

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
        return (int) round(str_replace(',', '.', $price) * 100);
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
