<?php
declare(strict_types=1);
namespace Xigen\CC\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\Customer;

class CustomerAttributeEmailCcCreate implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * CustomerAttributeJ6ConsultantAccountCodeCreate constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * Email CC
         */
        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'invoice_email_cc',
            [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'Invoice Email CC',
                'required' => 0,
                'user_defined' => 0,
                'note' => 'Comma separated list to cc invoice emails to',
                'visible' => 1,
                'multiline_count' => 1,
                'system' => 0,
                'position' => 400,
            ]
        );

        $customerAccountCode =$customerSetup->getEavConfig()->clear()
            ->getAttribute(Customer::ENTITY, 'invoice_email_cc');

        if ($customerAccountCode->getAttributeId()) {
            $usedInForms =  [
                'adminhtml_customer',
                'adminhtml_checkout',
                'customer_account_create',
                'customer_account_edit'
            ];
            $data = [];
            foreach ($usedInForms as $formCode) {
                $data[] = ['form_code' => $formCode, 'attribute_id' => $customerAccountCode->getAttributeId()];
            }
            $this->moduleDataSetup->getConnection()->insertMultiple(
                $this->moduleDataSetup->getTable('customer_form_attribute'),
                $data
            );
            $this->moduleDataSetup->getConnection()->endSetup();
        }


    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            \Magento\Customer\Setup\Patch\Data\UpdateIdentifierCustomerAttributesVisibility::class,
        ];
    }
}
