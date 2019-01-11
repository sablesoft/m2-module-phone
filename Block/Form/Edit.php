<?php
namespace SableSoft\Phone\Block\Form;

// app use:
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Form\Edit as MagentoEdit;
// module use:
use SableSoft\Phone\Model\Config;

/**
 * Class Edit
 *
 * @package SableSoft\Phone\Block\Form
 */
class Edit extends MagentoEdit {

    /** @var string */
    protected $phone;

    /**
     * @return CustomerInterface
     */
    public function getCustomer() : CustomerInterface {
        $customer = parent::getCustomer();
        $customer->setEmail( $this->getEmail( $customer ) );
        $customer->setFirstname( $this->getFirstname( $customer ) );
        $customer->setLastname( $this->getLastname( $customer ) );

        return $customer;
    }

    /**
     * @param null|CustomerInterface $customer
     * @return string
     */
    public function getEmail( $customer = null ) : string {
        if( !$customer )
            $customer = $this->getCustomer();
        $email = $customer->getEmail();
        if( strpos( $email, $this->getPhone( $customer ) ) !== false )
            $email = '';

        return (string) $email;
    }

    /**
     * @param null|CustomerInterface $customer
     * @return string
     */
    public function getFirstname( $customer = null ) : string {
        if( !$customer )
            $customer = $this->getCustomer();
        $name = $customer->getFirstname();
        if( strpos( $name, $this->getPhone( $customer ) ) !== false )
            $name = '';

        return (string) $name;
    }

    /**
     * @param null|CustomerInterface $customer
     * @return string
     */
    public function getLastname( $customer = null ) : string {
        if( !$customer )
            $customer = $this->getCustomer();
        $name = $customer->getLastname();
        if( strpos( $name, $this->getPhone( $customer ) ) !== false )
            $name = '';

        return (string) $name;
    }

    /**
     * @param null|CustomerInterface $customer
     * @return string
     */
    public function getPhone( $customer = null ) : string {
        if( !is_null( $this->phone ) )
            return $this->phone;

        if( !$customer )
            $customer = $this->getCustomer();
        $customer->getCustomAttributes();
        $attribute = $customer->getCustomAttribute( Config::ATTRIBUTE_PHONE );

        return $this->phone = $attribute ? (string) $attribute->getValue() : '';
    }
}
