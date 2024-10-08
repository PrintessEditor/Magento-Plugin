<?php

namespace Printess\PrintessDesigner\Plugin;

use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\Cart\CustomerCartResolver;
use Magento\Quote\Model\GuestCart\GuestCartResolver;
use Magento\Sales\Helper\Reorder as ReorderHelper;
use Magento\Sales\Model\Reorder\Reorder as OriginalReorder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Sales\Model\Reorder\OrderInfoBuyRequestGetter;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;

/*
$foo = new Foo;

// Single variable example
$getFooBar = function() {
    return $this->bar;
};

echo $getFooBar->call($foo); // Prints Foo::Bar

// Function call with parameters example
$getFooAddAB = function() {
    return $this->add_ab(...func_get_args());
};

echo $getFooAddAB->call($foo, 33, 6); // Prints 39
*/

class Reorder
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var CustomerCartResolver
     */
    private $customerCartProvider;

    /**
     * @var GuestCartResolver
     */
    private $guestCartResolver;

    /**
     * @var ReorderHelper
     */
    private $reorderHelper;

    /**
     * @var OriginalReorder
     */
    private $originalReorder;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var OrderInfoBuyRequestGetter
     */
    private $orderInfoBuyRequestGetter;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    public function __construct(
        OrderFactory $orderFactory,
        CustomerCartResolver $customerCartProvider,
        GuestCartResolver $guestCartResolver,
        ReorderHelper $reorderHelper,
        OriginalReorder $originalReorder,
        CartRepositoryInterface $cartRepository,
        OrderInfoBuyRequestGetter $orderInfoBuyRequestGetter,
        ProductCollectionFactory $productCollectionFactory,
        SerializerInterface $serializer
    ) {
        $this->orderFactory = $orderFactory;
        $this->customerCartProvider = $customerCartProvider;
        $this->guestCartResolver = $guestCartResolver;
        $this->reorderHelper = $reorderHelper;
        $this->originalReorder = $originalReorder;
        $this->cartRepository = $cartRepository;
        $this->orderInfoBuyRequestGetter = $orderInfoBuyRequestGetter;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->serializer = $serializer;
    }

    public function aroundExecute($subject, callable $proceed, string $orderNumber, string $storeId) {
        $order = $this->orderFactory->create()->loadByIncrementIdAndStoreId($orderNumber, $storeId);

        if (!$order->getId()) {
            throw new InputException(
                __('Cannot find order number "%1" in store "%2"', $orderNumber, $storeId)
            );
        }

        $customerId = (int)$order->getCustomerId();

        $cart = $customerId === 0
            ? $this->guestCartResolver->resolve()
            : $this->customerCartProvider->resolve($customerId);
        if (!$this->reorderHelper->isAllowed($order->getStore())) {
            //Output error
            return $proceed($orderNumber, $storeId);
        }
        
        $orderItems = $order->getItemsCollection();

        if(!$this->containsPrintessProducts($orderItems)) {
            return $proceed($orderNumber, $storeId);
        }

        $nonePrintessProducts = clone $orderItems;
        $printessProducts = clone $orderItems;

        foreach ($orderItems as $key => &$orderItem) {
            if ($this->isPrintessItem($orderItem)) {
               $nonePrintessProducts->removeItemByKey($key);
            } else {
                $printessProducts->removeItemByKey($key);
            }
         }

         if($nonePrintessProducts->count() > 0) {
            //Use original (private) method to add order items
            //Magento\Sales\Model\Reorder\Reorder
            $method = new \ReflectionMethod("Magento\Sales\Model\Reorder\Reorder", "addItemsToCart");
            $method->setAccessible(true);
            $method->invoke($this->originalReorder, $cart, $nonePrintessProducts, $storeId);
         }

         //Add printess products
         if($printessProducts->count() > 0) {
            $printessItems = array();
            $parentItemIds = array();
            $productIds = array();

            //Filter out all products that have a parent
            foreach($printessProducts as &$item) {
                $parent = $item->getParentItem();

                if(isset($parent)) {
                    $printessItems[] = array($parent, $item);
                    $parentItemIds[$parent->get_item_id()] = true;
                    $productIds[] = $parent->get_product_id();//$item->get_product_id(); //$productIds[] = $item->get_product_id();
                }
            }

            //Filter out all products that are no parent and that do not have a parent
            foreach($printessProducts as &$item) {
                $parent = $item->getParentItem();

                if(!isset($parent) && !array_key_exists($item->get_item_id(), $parentItemIds)) {
                    $printessItems[] = $item;
                    $productIds[] = $item->get_product_id();
                }
            }

            $products = $this->getOrderProducts($storeId, $productIds);

            //Add items to basket
            foreach($printessItems as &$item) {
                //Item with parent item
                if(is_array($item)) {
                    $productId = $item[0]->get_product_id();//1

                    if(array_key_exists($productId, $products)) {
                        $addResult = $this->addItemToCart($item[0], $cart, $products[$productId]);
                    }
                } else {
                    $productId = $item->get_product_id();

                    if(array_key_exists($productId, $products)) {
                        $addResult = $this->addItemToCart($item, $cart, $products[$productId]);
                    }
                }
            }
         }

         try {
            $this->cartRepository->save($cart);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $proceed($orderNumber, $storeId);
        }

        $savedCart = $this->cartRepository->get($cart->getId());

        $method = new \ReflectionMethod("Magento\Sales\Model\Reorder\Reorder", "prepareOutput");
        $method->setAccessible(true);
        return $method->invoke($this->originalReorder, $savedCart);
    }

    private function containsPrintessProducts(&$orderItems) {
        foreach ($orderItems as &$orderItem) {
            if ($this->isPrintessItem($orderItem)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get order products by store id and order item product ids.
     *
     * @param string $storeId
     * @param int[] $orderItemProductIds
     * @return Product[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOrderProducts(string $storeId, array $orderItemProductIds): array
    {
        $ret = array();
        /** @var Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setStore($storeId)
            ->addIdFilter($orderItemProductIds)
            ->addStoreFilter()
            ->addAttributeToSelect('*')
            ->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner')
            ->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner')
            ->addOptionsToResult();

        foreach($collection as &$item) {
            $ret[$item->get_entity_id()] = $item;
        }

        return $ret;
    }

    private function isPrintessItem($orderItem) {
        $parentItem = $orderItem->getParentItem();

        if($parentItem !== null) {
            $orderItem = $parentItem;
        }

        $options = $orderItem->getProductOptions();

        if (isset($options['additional_options']['printess_save_token'])) {
            return true;
        }

        return false;
    }

/**
     * Adds order item product to cart.
     *
     * @param OrderItemInterface $orderItem
     * @param Quote $cart
     * @param ProductInterface $product
     * @return void
     */
    private function addItemToCart(OrderItemInterface $orderItem, Quote $cart, ProductInterface $product): void
    {        
        $infoBuyRequest = $this->orderInfoBuyRequestGetter->getInfoBuyRequest($orderItem);

        $cartItem = $cart->addProduct($product, $infoBuyRequest);

        if (is_string($cartItem)) {
            throw new \Exception($cartItem);
        }

        $additionalOptions = [];

        if ($additionalOption = $orderItem->getOptionByCode('additional_options')) {
            $additionalOptions = $this->serializer->unserialize($additionalOption->getValue());
        }

        $options = $orderItem->getProductOptions();

        if (isset($options['additional_options']['printess_save_token'])) {
            $additionalOptions['printess_save_token'] = $options['additional_options']['printess_save_token'];
            // $additionalOptions['printess_save_token'] = [
            //     'label' => 'save_token',
            //     'value' => $options['additional_options']['printess_save_token']
            // ];
        }

        if (isset($options['additional_options']['printess_thumbnail_url'])) {
            $additionalOptions['printess_thumbnail_url'] = $options['additional_options']['printess_thumbnail_url'];
            // $additionalOptions['printess_thumbnail_url'] = [
            //     'label' => 'save_token',
            //     'value' => $options['additional_options']['printess_thumbnail_url']
            // ];
        }


        if (count($additionalOptions) > 0) {
            $cartItem->addOption([
                'product_id' => $product->get_entity_id(),
                'code' => 'additional_options',
                'value' => $this->serializer->serialize($additionalOptions)
            ]);
        }
    }
}