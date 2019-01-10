<?php
namespace SableSoft\Phone\Model\Config\Source;

/**
 * Class RegMode
 *
 * @package SableSoft\Phone\Model\Config\Source
 */
class RegMode {

    const MODE_ONLY_EMAIL = 0;
    const MODE_ONLY_PHONE = 1;
    const MODE_BOTH = 2;

    /**
     * Retrieve possible auth modes
     *
     * @return array
     */
    public function toOptionArray() :array {
        return [
            self::MODE_ONLY_EMAIL   => __('Sign up via email only'),
            self::MODE_ONLY_PHONE   => __('Sign up via phone only'),
            self::MODE_BOTH         => __('Sign up via phone and email')
        ];
    }
}
