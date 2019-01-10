<?php
namespace SableSoft\Phone\Model;

// module use:
use SableSoft\Core\Model\Config as CoreConfig;

/**
 * Class Config
 *
 * @package SableSoft\Phone\Model
 */
class Config extends CoreConfig {

    const SECTION               = 'phone';
    const FIELD_REG_MODE        = 'reg';
    const FIELD_AUTH_MODE       = 'auth';
    const FIELD_PHONE_LABEL     = 'phone_label';
    const FIELD_CODE_FREEZE     = 'code_freeze';
    const FIELD_CODE_VALID      = 'code_valid';
    const FIELD_CODE_LENGTH     = 'code_length';

    const ATTRIBUTE_PHONE   = self::SECTION;

    const ROUTE_PHONE_CODE_SEND = 'phone/code/send';
    const FIELD_CUSTOMER_ACCOUNT_SHARE_SCOPE = 'customer/account_share/scope';

    protected $section = self::SECTION;

    /** @var array - settings keys */
    protected $keys = [
        // general settings:
        self::FIELD_PHONE_LABEL     => self::GROUP_GENERAL,
        self::FIELD_REG_MODE        => self::GROUP_GENERAL,
        self::FIELD_AUTH_MODE       => self::GROUP_GENERAL,
        self::FIELD_CODE_FREEZE     => self::GROUP_GENERAL,
        self::FIELD_CODE_VALID      => self::GROUP_GENERAL,
        self::FIELD_CODE_LENGTH     => self::GROUP_GENERAL,
        self::FIELD_DEVELOP         => self::GROUP_GENERAL
    ];

    /**
     * Retrieve the customer account share scope
     *
     * @return int
     */
    public function getCustomerAccountShareScope() : int {
        return (int) $this->scopeConfig->getValue(self::FIELD_CUSTOMER_ACCOUNT_SHARE_SCOPE );
    }
}
