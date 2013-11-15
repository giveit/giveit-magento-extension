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
                                                  'type'        => 'layered_delivery',
                                                  'name'        => 'Delivery'
                    ));

        $countries      = array();

        foreach (range(1, self::MAX_DELIVERY_OPTIONS) as $i) {

            $xmlPath = sprintf($xmlPathTemplate, $i);
            $config = Mage::getStoreConfig($xmlPath);

            if ($config['name'] == '')                                       { continue; }
            if ($config['country'] == '')                                    { continue; }
            if ($config['price'] == '')                                      { continue; }
            if ($config['tax_percent'] < 0 || $config['tax_percent'] > 100)  { continue; }

            $config['id'] = 'option' . $i;

            $countries[$config['country']][] = $config;
        }

        $country_count = 0;

        foreach ($countries as $country => $options) {

            $countryName = Mage::app()->getLocale()->getCountryTranslation($country);

            $choice = new \GiveIt\SDK\Choice(array(
                                                'id'            => $country,
                                                'name'          => $countryName,
                                                'choices_title' => $countryName,
                      ));

            foreach ($options as $option) {

                $choice->addChoice(
                            new \GiveIt\SDK\Choice(array(
                                                'id'             => $option['id'],
                                                'name'           => $option['name'],
                                                'price'          => $this->_roundPrice($option['price']),
                                                'tax_percent'    => (int) $option['tax_percent'],
                           ))
                );
            }


            $delivery->addChoice($choice);
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
