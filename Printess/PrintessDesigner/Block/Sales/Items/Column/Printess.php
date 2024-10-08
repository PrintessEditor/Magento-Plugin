<?php

namespace Printess\PrintessDesigner\Block\Sales\Items\Column;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;

class Printess extends DefaultColumn
{

    /**
     * @var AuthorizationInterface
     */
    private AuthorizationInterface $authorization;

    public function __construct(
        Context $context,
        AuthorizationInterface $authorization,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        OptionFactory $optionFactory,
        array $data = []
    ) {
        $this->authorization = $authorization;

        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }

    public function getPdfUrls()
    {
        $data = $this->getItem()->getData();
        $urls = array();

        if(isset($data["printess_production_files"]))
        {
            $urls = json_decode($data["printess_production_files"], true);
        }

        return $urls;
    }
}
