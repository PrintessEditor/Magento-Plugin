<?php

namespace Printess\PrintessDesigner\Observer;

use Printess\PrintessDesigner\Helper\Data;
use JsonException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class AddAdditionalOptionsToCart implements ObserverInterface
{

    /**
     * @var \Printess\PrintessDesigner\Helper\Data
     */
    protected \Printess\PrintessDesigner\Helper\Data $helper;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;
    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    protected $productFactory;

    protected $productRepository;

    protected Configurable $configurable;

    /**
     * @param Data $helper
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     */
    public function __construct(
        \Printess\PrintessDesigner\Helper\Data $helper,
        RequestInterface $request,
        SerializerInterface $serializer,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws JsonException
     */
    public function execute(EventObserver $observer)
    {
        $contentString = $this->request->getContent();
        $params = null;

        if(!isset($contentString) || $contentString == "")
        {
            $postData = $this->request->getPostValue();

            if(isset($postData["saveToken"]))
            {
                $contentString = json_encode($postData);
            }
            
            if(!isset($contentString) || $contentString == "") {
                return;
            }
        }

        if ($this->helper->isJson($contentString)) {
            $params = json_decode($contentString, true, 512, JSON_THROW_ON_ERROR);
        }
        else if(is_string($contentString))
        {
            parse_str($contentString, $params);
            $params = json_decode(json_encode($params), true);
        }

        if(isset($params))
        {
            $item = $observer->getQuoteItem();

            $product = $this->productRepository->get($params['sku']);
            $parentId = $this->configurable->getParentIdsByChild($product->getId());

            $additionalOptions = [];

            if ($additionalOption = $item->getOptionByCode('additional_options')) {
                $additionalOptions = $this->serializer->unserialize($additionalOption->getValue());
            }

            if (isset($params['saveToken'], $params['thumbnailUrl'])) {
                $additionalOptions['printess_save_token'] = [
                    'label' => 'save_token',
                    'value' => $params['saveToken']
                ];

                $additionalOptions['printess_thumbnail_url'] = [
                    'label' => 'thumbnail_url',
                    'value' => $params['thumbnailUrl']
                ];
            } else if (isset($params['printess_save_token'], $params['printess_thumbnail_url'])) {
                $additionalOptions['printess_save_token'] = [
                    'label' => 'save_token',
                    'value' => $params['printess_save_token']
                ];
                
                $additionalOptions['printess_thumbnail_url'] = [
                    'label' => 'thumbnail_url',
                    'value' => $params['printess_thumbnail_url']
                ];
            }

            if (isset($params['printessItemOptions'])) {
                $additionalOptions['printess_item_options'] = [
                    'label' => 'printess_item_options',
                    'value' => $params['printessItemOptions']
                ];
            }

            if (count($additionalOptions) > 0) {
                $item->addOption([
                    'product_id' => $item->getProductId(),
                    'code' => 'additional_options',
                    'value' => $this->serializer->serialize($additionalOptions)
                ]);
            }
        }
    }
}
