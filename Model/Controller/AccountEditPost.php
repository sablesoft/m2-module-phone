<?php
namespace SableSoft\Phone\Model\Controller;

// app use:
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Account\EditPost;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
// module use:
use SableSoft\Phone\Model\Config;
use SableSoft\Phone\Model\Phone;

/**
 * Class AccountEditPost
 *
 * @package SableSoft\Phone\Model\Controller
 */
class AccountEditPost extends EditPost {

    /** @var Phone */
    protected $phone;

    /**
     * AccountEditPost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     * @param Escaper|null $escaper
     * @param Phone $phone
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        Validator $formKeyValidator,
        CustomerExtractor $customerExtractor,
        ?Escaper $escaper = null,
        // update constructor params
        Phone $phone
    ) {
        // parent construct:
        parent::__construct(
            $context, $customerSession, $customerAccountManagement,
            $customerRepository, $formKeyValidator, $customerExtractor, $escaper
        );
        // update construct:
        $this->phone = $phone;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch( RequestInterface $request ) {
        // check phone for unique if changed:
        $oldPhone = $this->session->getCustomer()->getData( Config::ATTRIBUTE_PHONE );
        $phone = $request->getParam( Config::ATTRIBUTE_PHONE );
        if( $phone = $this->phone->setShort( $phone ) )
            if( $phone != $oldPhone )
                if( $customer = $this->phone->getCustomer() ) {
                    $this->messageManager->addErrorMessage( __( 'This phone number is already taken!' ) );
                    $resultRedirect = $this->resultRedirectFactory->create();

                    return $resultRedirect->setPath('*/*/edit');
                }

        return parent::dispatch( $request );
    }
}
