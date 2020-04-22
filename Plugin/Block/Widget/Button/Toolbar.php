<?php

namespace Xigen\CC\Plugin\Block\Widget\Button;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\UrlInterface;


class Toolbar
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    public function __construct(\Magento\Framework\Registry $registry, UrlInterface $urlBuilder)
    {
        $this->_coreRegistry = $registry;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     */
    public function beforePushButtons(
        ToolbarContext $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    )
    {
        if (!$context instanceof \Magento\Sales\Block\Adminhtml\Order\Invoice\View) {
            return [$context, $buttonList];
        }
        $buttons = $buttonList->getItems();
        if(isset($buttons[0]['send_notification'])) {
            $button = $buttons[0]['send_notification'];
            $buttonOnClick = $button->getOnclick();
        }
        $buttonList->remove('send_notification');

        $buttonList->add('send_notification',
            [
                'label' => __('Send Email'),
                'class' => 'send-email-custom-cc send-email',
                'onclick' => "require([
    'Magento_Ui/js/modal/prompt'
], function(prompt) { // Variable that represents the `prompt` function
    prompt({
        title: 'Override Recipient',
        validation: false,
        content: 'Enter override email address, or leave blank / click cancel, for normal operations',
        actions: {
            confirm: function(data){ if(data == '') { " . $buttonOnClick . " } else { document.location = '" . $this->getEmailUrl() . "data/'+window.btoa(data) } },
            cancel: function(){ " . $buttonOnClick . " },
            always: function(){}
        }
    });
});",
            ]
        );

        return [$context, $buttonList];
    }

    /**
     * Get email Override url
     *
     * @return string
     */
    public function getEmailUrl()
    {
        return $this->getUrl(
            'sales/*/emailoverride',
            ['order_id' => $this->getInvoice()->getOrder()->getId(), 'invoice_id' => $this->getInvoice()->getId()]
        );
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}