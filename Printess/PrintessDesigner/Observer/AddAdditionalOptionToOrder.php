<?php

namespace Printess\PrintessDesigner\Observer;

use Printess\PrintessDesigner\Model\AdditionalOptions\OptionsManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class AddAdditionalOptionToOrder implements ObserverInterface
{

    /**
     * @var OptionsManager
     */
    private OptionsManager $optionsManager;

    /**
     * @param OptionsManager $optionsManager
     */
    public function __construct(OptionsManager $optionsManager)
    {
        $this->optionsManager = $optionsManager;
    }

    /**
     * @event sales_model_service_quote_submit_before
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {

        /** @var Quote $quote */
        $quote = $observer->getData('quote');
        /** @var Order $order */
        $order = $observer->getData('order');

        $this->optionsManager->transferAdditionalOptions($quote, $order);
    }
}
