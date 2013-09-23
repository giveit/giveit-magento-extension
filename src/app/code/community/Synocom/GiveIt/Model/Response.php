<?php

class Synocom_GiveIt_Model_Response extends Zend_Controller_Response_Http {

    /**
     * Character set which must be used in response
     */
    const RESPONSE_CHARSET = 'utf-8';

    /**
     * Set header appropriate to specified MIME type
     *
     * @param string $mimeType MIME type
     * @return Synocom_GiveIt_Model_Response
     */
    public function setMimeType($mimeType) {
        return $this->setHeader('Content-Type', "{$mimeType}; charset=" . self::RESPONSE_CHARSET, true);
    }

}