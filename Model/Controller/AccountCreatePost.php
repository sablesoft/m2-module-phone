<?php
namespace SableSoft\Phone\Model\Controller;

// app use:
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Controller\Account\CreatePost;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Registration;
use Magento\Framework\Escaper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
// module use:
use SableSoft\Phone\Model\Config;
use SableSoft\Phone\Model\AccountManagement;
use SableSoft\Phone\Helper\Data as PhoneHelper;
use SableSoft\Phone\Model\Code as PhoneCode;

class AccountCreatePost extends CreatePost {

    /** @var AccountManagementInterface|AccountManagement */
    protected $accountManagement;
    /** @var PhoneHelper */
    protected $phoneHelper;
    /** @var PhoneCode */
    protected $phoneCode;
    /** @var PhpCookieManager */
    private $cookieMetadataManager;
    /** @var CookieMetadataFactory */
    private $cookieMetadataFactory;
    /** @var AccountRedirect  */
    private $accountRedirect;
    /** @var ScopeConfigInterface  */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param CustomerUrl $customerUrl
     * @param Registration $registration
     * @param Escaper $escaper
     * @param CustomerExtractor $customerExtractor
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountRedirect $accountRedirect
     * @param Validator $formKeyValidator
     *
     * @param PhpCookieManager $cookieMetadataManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhoneHelper $phoneHelper
     * @param PhoneCode $phoneCode
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        // parent constructor params:
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor,
        DataObjectHelper $dataObjectHelper,
        AccountRedirect $accountRedirect,
        Validator $formKeyValidator = null,
        // update constructor params:
        PhpCookieManager $cookieMetadataManager,
        CookieMetadataFactory $cookieMetadataFactory,
        PhoneHelper $phoneHelper,
        PhoneCode $phoneCode
    ) {
        // parent construct:
        parent::__construct(
            $context, $customerSession, $scopeConfig, $storeManager,
            $accountManagement, $addressHelper, $urlFactory, $formFactory,
            $subscriberFactory, $regionDataFactory, $addressDataFactory,
            $customerDataFactory, $customerUrl, $registration, $escaper,
            $customerExtractor, $dataObjectHelper, $accountRedirect, $formKeyValidator
        );
        // update construct:
        $this->cookieMetadataManager = $cookieMetadataManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->scopeConfig = $scopeConfig;
        $this->accountRedirect = $accountRedirect;
        $this->phoneHelper = $phoneHelper;
        $this->phoneCode = $phoneCode;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch( RequestInterface $request ) {
        // special dispatch only for phone code registration mode:
        if( !$this->phoneHelper->isCodeRegMode() )
            return parent::dispatch( $request );

        // try force login by phone and code:
        try {
            $this->accountManagement->isRegister = true;
            if( $customer = $this->accountManagement->authenticate(
                $request->getParam( Config::ATTRIBUTE_PHONE ),
                $request->getParam('password' ) ) )
                return $this->forceLogin( $customer );
        } catch( \Exception $e ) {}

        // default dispatch if not force login:
        return parent::dispatch( $request );
    }

    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmation
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation( $password, $confirmation ) {
        // check is phone code registration mode:
        if( $this->phoneHelper->isCodeRegMode() ) {
            if( !$this->phoneCode->validate( $password ) )
                throw new InputException(__('Invalid phone code.'));
        } else
            parent::checkPasswordConfirmation( $password, $confirmation );
    }

    /**
     * @param CustomerInterface $customer
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    protected function forceLogin( CustomerInterface $customer ) {
        $this->session->setCustomerDataAsLoggedIn( $customer );
        $this->session->regenerateId();
        if( $this->cookieMetadataManager->getCookie('mage-cache-sessid') ) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            try {
                $this->cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
            } catch (InputException $e) {
                $this->phoneHelper->log( 'error', $e->getMessage() );
            } catch( FailureToSendException $e ) {
                $this->phoneHelper->log( 'error', $e->getMessage() );
            }
        }
        $redirectUrl = $this->accountRedirect->getRedirectCookie();
        if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $redirectUrl ) {
            $this->accountRedirect->clearRedirectCookie();
            $resultRedirect = $this->resultRedirectFactory->create();
            // URL is checked to be internal in $this->_redirect->success()
            $resultRedirect->setUrl($this->_redirect->success($redirectUrl));

            return $resultRedirect;
        }

        return $this->accountRedirect->getRedirect();
    }
}
