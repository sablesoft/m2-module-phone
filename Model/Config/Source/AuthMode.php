<?php
namespace SableSoft\Phone\Model\Config\Source;

/**
 * Class AuthMode
 *
 * @package SableSoft\Phone\Model\Config\Source
 */
class AuthMode {

    const MODE_EMAIL = 0;
    const MODE_PHONE = 1;
    const MODE_BOTH = 2;
    const MODE_CODE = 3;

    /**
     * Retrieve possible auth modes
     *
     * @return array
     */
    public function toOptionArray() : array {
        return [
            self::MODE_EMAIL   => __('Login via email only'),
            self::MODE_PHONE   => __('Login via phone only'),
            self::MODE_BOTH         => __('Login via phone or email'),
            self::MODE_CODE         => __('Login via phone code')
        ];
    }
}
