<?php

namespace Printess\PrintessDesigner\Plugin\Orders;

use Printess\PrintessDesigner\Model\Api\DocumentsFactory;
use Printess\PrintessDesigner\Model\Api\PriceInfoFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class CustomProductAttributes
{

    /**
     * @var OrderItemExtensionFactory
     */
    protected OrderItemExtensionFactory $orderItemExtensionFactory;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $productFactory;

    /**
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     * @param ProductFactory $productFactory
     * @param PriceInfoFactory $priceInfoFactory
     */
    public function __construct(
        OrderItemExtensionFactory $orderItemExtensionFactory,
        ProductFactory $productFactory
    ) {
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $orderItem): OrderItemInterface
    {
        $extensionAttributes = $orderItem->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ?: $this->orderItemExtensionFactory->create();

        $options = $orderItem->getProductOptions();

        if (isset($options['additional_options']['printess_save_token'])) {
            $extensionAttributes->setPrintessSaveToken($options['additional_options']['printess_save_token']['value']);
        }

        if (isset($options['additional_options']['printess_thumbnail_url'])) {
            $extensionAttributes->setPrintessThumbnailUrl($options['additional_options']['printess_thumbnail_url']['value']);
        }

        $orderItem->setExtensionAttributes($extensionAttributes);

        return $orderItem;
    }

    /**
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemSearchResultInterface $result
     * @return OrderItemSearchResultInterface
     */
    public function afterGetList(OrderItemRepositoryInterface $subject, OrderItemSearchResultInterface $result): OrderItemSearchResultInterface
    {
        $orderItems = $result->getItems();

        foreach ($orderItems as $orderItem) {
            $extensionAttributes = $orderItem->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ?: $this->orderItemExtensionFactory->create();

            $options = $orderItem->getProductOptions();

            if (isset($options['additional_options']['printess_save_token'])) {
                $extensionAttributes->setPrintessSaveToken($options['additional_options']['printess_save_token']['value']);
            }

            if (isset($options['additional_options']['printess_thumbnail_url'])) {
                $extensionAttributes->setPrintessThumbnailUrl($options['additional_options']['printess_thumbnail_url']['value']);
            }

            $orderItem->setExtensionAttributes($extensionAttributes);
        }

        return $result;
    }
}
