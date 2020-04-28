<?php
/**
 * Add email cc field to customer account area. Transactional emails are also sent to this address.
 * Copyright (C) 2018 Dominic Xigen
 *
 * This file included in Xigen/CC is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Xigen\CC\Plugin\Magento\Framework\Mail\Template;

use Xigen\CC\Registry\OverrideEmail;

/**
 * Plugin to add customer email cc
 */
class TransportBuilder
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $isInvoice = false;

    protected $_state;

    protected $scopeConfig;

    /**
     * @var OverrideEmail
     */
    private $overrideEmail;

    protected $customerrepository;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        OverrideEmail $overrideEmail

    )
    {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->logger = $logger;
        $this->_state = $state;
        $this->scopeConfig = $scopeConfig;
        $this->overrideEmail = $overrideEmail;
    }

    public function beforeSetTemplateVars($subject, $vars)
    {
        if (isset($vars['invoice'])) {
            $this->isInvoice = $vars['invoice'];
        }
        return ['vars' => $vars];
    }

    public function beforeGetTransport(
        \Magento\Framework\Mail\Template\TransportBuilder $subject
    )
    {
        try {
            $enabled = $this->scopeConfig->getValue('sales_email/invoice/invoice_cc_enabled');
            if ($enabled && $this->isInvoice) {
                $overrideEmail = $this->overrideEmail->get();
                $ccEmailAddresses = (count($overrideEmail) > 0) ? $overrideEmail : $this->getEmailCopyTo();
                if (!empty($ccEmailAddresses)) {
                    foreach ($ccEmailAddresses as $ccEmailAddress) {
                        $subject->addCc(trim($ccEmailAddress));
                        $this->logger->debug((string)__('Added customer CC: %1', trim($ccEmailAddress)));
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error((string)__('Failure to add customer CC: %1', $e->getMessage()));
        }
        return [];
    }

    /**
     * Get customer from invoice
     */
    public function getCustomerFromInvoice()
    {
        $customer = $this->customerRepositoryInterface->getById($this->isInvoice->getOrder()->getCustomerId());
        if ($customer->getId()) {
            return $customer;
        }
        $this->logger->error((string)__('Failure to load customer from given invoice: %1 using customer id %1',
            $this->isInvoice->getId(), $this->isInvoice->getOrder()->getCustomerId()));
        return null;
    }

    /**
     * Return email copy_to list
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        $customer = $this->getCustomerFromInvoice();
        if (is_null($customer)) {
            return false;
        }
        $customerEmailCC = $customer->getCustomAttribute('invoice_email_cc');
        if (is_object($customerEmailCC)) {
            return explode(',', trim($customerEmailCC->getValue()));
        }

        return false;
    }

}
