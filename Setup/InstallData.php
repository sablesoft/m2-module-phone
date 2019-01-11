<?php
namespace SableSoft\Phone\Setup;

// app use:
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;

/**
 * Class InstallData
 *
 * @package SableSoft\Phone\Setup
 */
class InstallData implements InstallDataInterface {

    const ATTRIBUTE_CODE = 'phone';

    /** @var EavSetupFactory  */
    private $setupFactory;
    /** @var Config  */
    private $eavConfig;
    /** @var \Psr\Log\LoggerInterface  */
    private $logger;

    /**
     * InstallData constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param EavSetupFactory $setupFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        EavSetupFactory $setupFactory,
        Config $config
    ) {
        $this->logger = $logger;
        $this->eavConfig = $config;
        $this->setupFactory = $setupFactory;
    }

    /**
     * Installs module DB schema
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @return void
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->setupFactory->create(['setup' => $setup]);
        // Add new customer attribute
        $eavSetup->addAttribute(
            Customer::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type'                  => 'varchar',
                'label'                 => 'Phone',
                'input'                 => 'text',
                'required'              => false,
                'frontend_class'        => 'validate digits validate-greater-than-zero',
                'unique'                => true,
                'sort_order'            => 10,
                'visible'               => true,
                'system'                => true,
                'is_used_in_grid'       => true,
                'is_visible_in_grid'    => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ]
        );

        // add attribute to form
        try {
            $attribute = $this->eavConfig->getAttribute(
                Customer::ENTITY, self::ATTRIBUTE_CODE
            );
            $attribute->setData(
                'used_in_forms', ['adminhtml_customer', 'customer_account_create', 'customer_account_edit' ]
            )->setData('is_used_for_customer_segment', true )
                ->setData('is_system', 1)
                ->setData('is_user_defined', 0)
                ->setData('is_visible', 1);
            $attribute->save();
        } catch ( \Exception $e ) {
            $this->logger->error( $e->getMessage() );
        }
    }
}
