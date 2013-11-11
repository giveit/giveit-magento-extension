<?php

/**
 * GiveIt extension
 *

 * @package    GiveIt

 */

/**
 * Environment source model
 */
class GiveIt_Model_System_Config_Source_Environment
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
            self::ENVIRONMENT_LIVE   => Mage::helper('giveit')->__('Live'),
            self::ENVIRONMENT_SANDBOX  => Mage::helper('giveit')->__('Sandbox'),
        );
    }

}
