<?php

namespace Xigen\CC\Controller\Adminhtml\Order\Invoice;

    use Xigen\CC\Registry\OverrideEmail;

/**
 * Class Email
 *
 * @package Xigen\CC\Controller\Adminhtml\Invoice
 */
class Emailoverride extends \Magento\Sales\Controller\Adminhtml\Order\Invoice\Email
{

    /**
     * @var OverrideEmail
     */
    private $overrideEmail;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        OverrideEmail $overrideEmail
    ) {
        $this->overrideEmail = $overrideEmail;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $resultForwardFactory);
    }
    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if (!$invoiceId) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $invoice = $this->_objectManager->create(\Magento\Sales\Api\InvoiceRepositoryInterface::class)->get($invoiceId);
        if (!$invoice) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $email = base64_decode($this->getRequest()->getParam('data'));
        if (!\Zend_Validate::is(trim($email), 'EmailAddress')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $this->overrideEmail->set($email);

        $this->_objectManager->create(
            \Magento\Sales\Api\InvoiceManagementInterface::class
        )->notify($invoice->getEntityId());

        $this->messageManager->addSuccessMessage(__('You sent the invoice to: ' . $email));
        return $this->resultRedirectFactory->create()->setPath(
            'sales/invoice/view',
            ['order_id' => $invoice->getOrder()->getId(), 'invoice_id' => $invoiceId]
        );
    }
}
