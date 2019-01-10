<?php
namespace SableSoft\Phone\Model;

// app use:
use Magento\Customer\Model\Session;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class Code
 *
 * @package SableSoft\Phone\Model
 */
class Code {

    private $time;
    private $value;
    private $ready;

    protected $sessionKey = 'PhoneCode';
    protected $valueSource = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /** @var \Magento\Customer\Model\Session */
    protected $session;
    /** @var Config  */
    protected $config;

    /**
     * Code constructor.
     *
     * @param Session $session
     * @param Config $config
     */
    public function __construct( Session $session, Config $config ) {
        $this->session = $session;
        $this->config = $config;
        $this->load();
    }

    /**
     * @return bool
     * @throws InvalidConfigurationException
     */
    public function load() : bool {
        $this->clean( false );
        $data = $this->_session('get');
        if( !is_array( $data ) ) return false;
        if( !isset( $data['time'] ) || !isset( $data['value'] ) )
            throw new InvalidConfigurationException('Invalid customer session phone code!');

        $this->value = $data['value'];
        $this->time = (int) $data['time'];
        $this->ready = true;

        if( $this->isExpired() )
            $this->clean();

        return (bool) $this->ready;
    }

    /**
     * @return bool|string
     */
    public function generate() {
        // check code is freeze:
        if( $this->isFreeze() )
            return false;

        $this->value = $this->_value();
        $this->time = time();
        $this->ready = true;
        $data = [
            'time'  => $this->time,
            'value' => $this->value
        ];
        $this->_session( 'set', $data );

        return $this->value;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function validate( string $value ) : bool {
        if( !$this->isReady() && !$this->load() )
            return false;

        return $this->value === $value;
    }

    /**
     * @param bool $unset
     */
    public function clean( bool $unset = true ) {
        $this->time = null;
        $this->value = null;
        $this->ready = null;
        if( $unset )
            $this->_session( 'uns');
    }

    /**
     * @return bool
     */
    public function isExpired() : bool {
        if( !$this->isReady() && !$this->load() )
            return false;

        $period = time() - (int) $this->time;

        return ( $period > $this->config->getValue(Config::FIELD_CODE_VALID ) );
    }

    /**
     * @return bool
     */
    public function isFreeze() : bool {
        if( !$this->isReady() && !$this->load() )
            return false;

        $period = time() - $this->time;

        return ( $period <= $this->config->getValue( Config::FIELD_CODE_FREEZE ) );
    }

    /**
     * @return bool
     */
    public function isReady() : bool {
        return (bool) $this->ready;
    }

    /**
     * @return string
     */
    protected function _value() : string {
        $input = $this->valueSource;
        $length = strlen( $input );
        $codeLength = $this->config->getValue(Config::FIELD_CODE_LENGTH );
        $string = '';
        for( $i = 0; $i < $codeLength; $i++ ) {
            $char = $input[ mt_rand( 0, $length - 1 ) ];
            $string .= $char;
        }

        return $string;
    }

    /**
     * @param string $method
     * @param null $data
     * @return mixed
     */
    protected function _session( string $method, $data = null ) {
        $method .= $this->sessionKey;
        return is_null( $data ) ?
            $this->session->$method() :
            $this->session->$method( $data );
    }
}
