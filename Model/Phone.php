<?php
namespace SableSoft\Phone\Model;

// module use:
use Magento\Customer\Model\Config\Share;
use Magento\Framework\Api\FilterBuilder;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
// module use:
use SableSoft\Phone\Model\Config as Config;
use SableSoft\Smsp\Model\Config as SmspConfig;

/**
 * Class Phone
 *
 * @package SableSoft\Phone\Model
 */
class Phone {
    /** @var SmspConfig  */
    protected $smspConfig;
    /** @var Config */
    protected $config;
    /** @var string */
    protected $number;
    /** @var string */
    protected $countryCode;
    /** @var PsrLogger */
    protected $logger;
    /** @var FilterBuilder */
    private $filterBuilder;
    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;
    /** @var StoreManagerInterface */
    private $storeManager;
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * Phone constructor.
     *
     * @param Config $config
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        FilterBuilder $filterBuilder,
        SmspConfig $smspConfig,
        Config $config,
        PsrLogger $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->filterBuilder = $filterBuilder;
        $this->smspConfig = $smspConfig;
        $this->config = $config;
        $this->logger = $logger;
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

    /**
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer() {
        if( !$phone = $this->getShort() )
            return false;

        try {
            $phoneAttribute = Config::ATTRIBUTE_PHONE;
            // add website filter:
            $websiteIdFilter = false;
            if( $this->config->getCustomerAccountShareScope() == Share::SHARE_WEBSITE )
                $websiteIdFilter[] = $this->filterBuilder
                    ->setField('website_id')
                    ->setConditionType('eq')
                    ->setValue( $this->storeManager->getStore()->getWebsiteId() )
                    ->create();
            // Add phone filter:
            $phoneFilter[] = $this->filterBuilder
                ->setField( $phoneAttribute )
                ->setConditionType('eq')
                ->setValue( $phone )
                ->create();
            // Build search criteria
            $searchCriteriaBuilder = $this->searchCriteriaBuilder->addFilters( $phoneFilter );
            if( is_array( $websiteIdFilter ) )
                $searchCriteriaBuilder->addFilters($websiteIdFilter);
            $searchCriteria = $searchCriteriaBuilder->create();
            // Retrieve the customer collection
            // and return customer if there was exactly one customer found
            $collection = $this->customerRepository->getList($searchCriteria);
            if( $collection->getTotalCount() == 1 )
                return $collection->getItems()[0];

        } catch( \Exception $e ) {
            $this->logger->error( $e->getMessage() );
        }

        return false;
    }
}
