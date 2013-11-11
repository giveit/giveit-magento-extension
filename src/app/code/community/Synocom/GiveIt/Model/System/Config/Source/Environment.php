<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Brennon Blokland <info@synocom.nl>
 */

/**
 * Environment source model
 */
class Synocom_GiveIt_Model_System_Config_Source_Environment
{

    const ENVIRONMENT_SANDBOX = 'sandbox';
    const ENVIRONMENT_LIVE    = 'live';

    /**
     * Returns option list of environment for configuration
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::ENVIRONMENT_LIVE   => Mage::helper('synocom_giveit')->__('Live'),
            self::ENVIRONMENT_SANDBOX  => Mage::helper('synocom_giveit')->__('Sandbox'),
        );
    }

}
