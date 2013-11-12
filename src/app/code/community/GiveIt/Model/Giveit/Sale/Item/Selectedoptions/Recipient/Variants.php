<?php
/**
 * GiveIt extension
 *
 * @method getId()
 * @method getName()
 * @method getPrice()
 *

 * @package    GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
class GiveIt_Model_Giveit_Sale_Item_Selectedoptions_Recipient_Variants extends Mage_Core_Model_Abstract {

    public function getProductId() {
        $data = $this->getData();
echo "variants\n";
        print_r($data);

    }

    /**
     * Get selected opiton SKU
     *
     * @return mixed|null
     */
    public function getSku() {
        $data = $this->getData();

        if (array_key_exists(0, $data)) {
            $nestedSelectedItem = array_pop($this->getData());

            if (is_array($nestedSelectedItem) && array_key_exists('id', $nestedSelectedItem)) {
                return $nestedSelectedItem['id'];
            }
        } else {
            return $this->getData('id');
        }

        return null;
    }

}