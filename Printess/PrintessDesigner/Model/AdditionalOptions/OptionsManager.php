<?php

namespace Printess\PrintessDesigner\Model\AdditionalOptions;

use Exception;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class OptionsManager
{

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    public function __construct(
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param $source
     * @param $destination
     * @return void
     */
    public function transferAdditionalOptions($source, $destination): void
    {
        try {
            $sourceItems = [];

            foreach ($source->getItems() as $sourceItem) {
                if (!$sourceItem->getParentItemId()) {
                    $sourceItems[$sourceItem->getId()] = $sourceItem;
                }
            }

            foreach ($destination->getItems() as $destinationItem) {
                if (!$destinationItem->getParentItemId()) {
                    $quoteItemId = $destinationItem->getQuoteItemId();

                    if (isset($sourceItems[$quoteItemId])) {
                        $sourceItem = $sourceItems[$quoteItemId];

                        if ($additionalOptions = $sourceItem->getOptionByCode('additional_options')) {
                            $options = $destinationItem->getProductOptions();
                            $options['additional_options'] = $this->serializer->unserialize($additionalOptions->getValue());
                            $destinationItem->setProductOptions($options);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }
}
