<?php

namespace Printess\PrintessDesigner\Block\Sales\Items\Column;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;

class Thumbnail extends DefaultColumn
{

    /**
     * @var Image
     */
    protected Image $imageHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    public function __construct(
        Context $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        OptionFactory $optionFactory,
        Image $imageHelper,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getThumbnailUrl()
    {

        if (($options = $this->getItem()->getProductOptions()) && isset($options['additional_options']['printess_thumbnail_url'])) {
            return $options['additional_options']['printess_thumbnail_url']['value'];
        }

        if ($this->getItem()->getProductType() === Configurable::TYPE_CODE) {
            $product = $this->productRepository->get($this->getItem()->getSku());
        } else {
            $product = $this->getItem()->getProduct();
        }

        return $this->imageHelper->init($product, 'product_listing_thumbnail')->resize(100, 100)->getUrl();

    }

}
