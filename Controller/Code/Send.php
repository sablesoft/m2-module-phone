<?php
namespace SableSoft\Phone\Controller\Code;

use SableSoft\Smsp\Model\Api;
use SableSoft\Smsp\Model\Config;
use SableSoft\Phone\Model\Code;
use SableSoft\Phone\Model\Phone;
use Magento\Framework\App\Action\Context;
use SableSoft\Smsp\Controller\Index\Index as SmspController;

/**
 * Class Send
 *
 * @package SableSoft\Phone\Controller\Code
 */
class Send extends SmspController {

    const PARAM_NUMBER = 'number';
    const SMSP_COMMAND = 'msg_send';

    /** @var Code  */
    protected $code;
    /** @var int */
    protected $number;
    /** @var Phone  */
    protected $phone;

    /**
     * Index constructor.
     * @param Api $api
     * @param Context $context
     */
    public function __construct(
        Api $api,
        Config $config,
        Context $context,
        // session phone code model:
        Code $code,
        Phone $phone
    ) {
        $this->code = $code;
        $this->phone = $phone;
        parent::__construct( $api, $config, $context );
    }

    /**
     * Send code to phone number
     */
    public function execute() {
        // check customer number:
        if( !$this->number )
            return parent::response([
               'success' => false,
               'error'   => __('Number is required for message sending!')
            ]);
        // check session code not freeze:
        if( $this->code->isFreeze() )
            return parent::response([
                'success' => false,
                'error'   => __('Code sending is freeze yet. Just wait.')
            ]);
        // generate new code:
        if( !$code = $this->code->generate() )
            return parent::response([
                'success' => false,
                'error'   => __('Generation code error.')
            ]);
        // set request params:
        $this->params = [
            'message'       => $this->message( $code ),
            'recipients'     => $this->number
        ];
        // make api request:
        return parent::execute();
    }

    protected function prepare() {
        $this->command = self::SMSP_COMMAND;
        $number = $this->_request->getParam( self::PARAM_NUMBER );
        $this->number = $this->phone->setShort( $number );
    }

    protected function message( $code ) {
        return $code; // @todo
    }
}
