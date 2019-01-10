<?php
namespace SableSoft\Phone\Block\Form;

use SableSoft\Phone\Helper\Data as PhoneHelper;
use SableSoft\Phone\Model\Config\Source\RegMode;
use Magento\Customer\Block\Form\Register as MagentoRegister;

class Register extends MagentoRegister {

    /** @var PhoneHelper  */
    protected $phoneHelper;

    /**
     * Constructor Register
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        // parent class construct params:
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = [],
        // update class construct params:
        PhoneHelper $helper
    ) {
        // parent construct:
        parent::__construct(
            $context, $directoryHelper, $jsonEncoder, $configCacheType, $regionCollectionFactory,
            $countryCollectionFactory, $moduleManager, $customerSession, $customerUrl, $data
        );
        // update construct:
        $this->phoneHelper = $helper;
    }

    /**
     * @return MagentoRegister|$this
     */
    protected function _prepareLayout() {
        // update block template by registration mode:
        switch( $this->phoneHelper->getConfigValue('auth' ) ) {
            case RegMode::MODE_CODE:
            case RegMode::MODE_PHONE:
                $this->setTemplate('SableSoft_Phone::form/register.phtml');
                break;
            default: break;
        };
        return parent::_prepareLayout();
    }

}