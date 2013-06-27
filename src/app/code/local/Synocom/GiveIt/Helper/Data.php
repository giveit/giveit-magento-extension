<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Brennon Blokland <info@synocom.nl>
 * @author     Mike Bijnsdorp <info@synocom.nl>
 */
class Synocom_GiveIt_Helper_Data
    extends Mage_Core_Helper_Data
{

    /**
     * Get an instance of the Give it SDK option object
     *
     * @param string $id
     * @param string $type
     * @param string $name
     * @param array $optional optional options. Optional key is used as index, its value as value
     * @return \GiveIt\SDK\Option
     */
    public function getSdkOption($id, $type, $name, $optional = array())
    {
        $options = array(
            'id'   => $id,
            'type' => $type,
            'name' => $name
        );

        if (!empty($optional)) {
            foreach ($optional as $key => $value) {
                $options[$key] = $value;
            }
        }

        return Mage::getModel('synocom_giveit/option', $options);
    }

    /**
     * Get an instance of the Give it SDK choice object
     *
     * @param string $id
     * @param string $name
     * @param int $price
     * @return \GiveIt\SDK\Choice
     */
    public function getSdkChoice($id, $name, $price)
    {
        return Mage::getModel('synocom_giveit/choice',
                array(
                'id'    => $id,
                'name'  => $name,
                'price' => $price
                )
        );
    }

}
