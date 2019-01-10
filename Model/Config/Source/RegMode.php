<?php
namespace SableSoft\Phone\Model\Config\Source;

/**
 * Class RegMode
 *
 * @package SableSoft\Phone\Model\Config\Source
 */
class RegMode {

    const MODE_EMAIL = 0;
    const MODE_PHONE = 1;
    const MODE_CODE = 2;

    /**
     * Retrieve possible auth modes
     *
     * @return array
     */
    public function toOptionArray() :array {
        return [
            self::MODE_EMAIL   => __('Sign up via email'),
            self::MODE_PHONE   => __('Sign up via phone'),
            self::MODE_CODE    => __('Sign up by phone code')
        ];
    }
}
