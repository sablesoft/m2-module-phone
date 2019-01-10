<?php
namespace SableSoft\Phone\Model;

// module use:
use SableSoft\Smsp\Model\Config as SmspConfig;

/**
 * Class Phone
 *
 * @package SableSoft\Phone\Model
 */
class Phone {
    /** @var SmspConfig  */
    protected $smspConfig;
    /** @var string */
    protected $number;
    /** @var string */
    protected $countryCode;

    /**
     * Phone constructor.
     *
     * @param Config $config
     */
    public function __construct( SmspConfig $config ) {
        $this->smspConfig = $config;
    }

    /**
     * @param string $number
     * @return string|null
     */
    public function setShort( string $number ) : string {
        $countryCode = $this->getCountryCode();
        $pattern = "/^\+$countryCode \((\d+)\) (\d+)\-(\d+)\-(\d+)/i";
        $replacement = '${1}${2}${3}${4}';
        $number = preg_replace( $pattern, $replacement, $number );
        if( !is_numeric( $number ) )
            return '';

        return $this->number = $number;
    }

    /**
     * @param string $number
     * @return null|string
     */
    public function setFull( string $number ) {
        $countryCode = $this->getCountryCode();
        if( strpos( $number, $countryCode ) !== 0 )
            return null;

        $number = substr( $number, strlen( $countryCode ) );
        if( !is_numeric( $number ) )
            return null;

        return $this->number = $number;
    }

    /**
     * @return null|string
     */
    public function getFull() {
        if( !$short = $this->getShort() )
            return null;

        return $this->getCountryCode() . $short;
    }

    /**
     * @return string|null
     */
    public function getShort() {
        return $this->number;
    }

    /**
     * @param string $code
     * @return null|string
     */
    public function setCountryCode( string $code ) {
        if( !is_numeric( $code ) )
            return null;

        return $this->countryCode = $code;
    }

    /**
     * @return string
     */
    public function getCountryCode() : string {
        if( $this->countryCode )
            return $this->countryCode;

        return $this->countryCode =
            $this->smspConfig->getValue( SmspConfig::FIELD_COUNTRY );
    }
}
