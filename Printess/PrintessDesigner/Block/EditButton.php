<?php
namespace Printess\PrintessDesigner\Block;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\View\Element\Template;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * @api
 * @since 100.0.2
 */
class EditButton extends Generic
{
    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @param Template\Context $context
     * @param Cart $cartHelper
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Template\Context $context,
        Cart $cartHelper,
        array $data = []
    ) {
        $this->cartHelper = $cartHelper;
        parent::__construct($context, $data);
    }
    
    public function getSaveToken() {
        $item = $this->getItem();
        $saveToken = "";
        if ($additionalOptions = $item->getOptionByCode('additional_options')) {
            $additionalOptions = $item->getOptionByCode('additional_options')->getValue();
            
            if($additionalOptions) {
                $additionalOptions = json_decode($additionalOptions, true);
                
                if(key_exists("printess_save_token", $additionalOptions)) {
                    $saveToken = $additionalOptions["printess_save_token"]["value"];
                }
            }
        }
        
        return $saveToken;
    }

    public function getThumbnailUrl() {
        $item = $this->getItem();
        $saveToken = "";
        if ($additionalOptions = $item->getOptionByCode('additional_options')) {
            $additionalOptions = $item->getOptionByCode('additional_options')->getValue();
            
            if($additionalOptions) {
                $additionalOptions = json_decode($additionalOptions, true);
                
                if(key_exists("printess_thumbnail_url", $additionalOptions)) {
                    $saveToken = $additionalOptions["printess_thumbnail_url"]["value"];
                }
            }
        }
        
        return $saveToken;
    }

    public function getItemOptions() {
        $item = $this->getItem();
        $saveToken = "";
        if ($additionalOptions = $item->getOptionByCode('additional_options')) {
            $additionalOptions = $item->getOptionByCode('additional_options')->getValue();
            
            if($additionalOptions) {
                $additionalOptions = json_decode($additionalOptions, true);
                
                if(key_exists("printess_item_options", $additionalOptions)) {
                    $saveToken = $additionalOptions["printess_item_options"]["value"];
                }
            }
        }
        
        return $saveToken;
    }
    
    public function isPrintessProduct() {
        $saveToken = $this->getSaveToken();
        
        $ret = isset($saveToken) && $saveToken != "";
        
        return $ret;
    }

    public function CreateGuidV4($data = null) {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function getPrintessInfo() {
        $item = $this->getItem();
        $product = $item->getProduct();
        
        $info = array(
        	"id" => $item->getId(),
        	"sku" => $product->getSku(),
            "save_token" => $this->getSaveToken(),
            "productName" => $product->getName(),
            "thumbnailUrl" => $this->getThumbnailUrl(),
            "itemOptions" => $this->getItemOptions(),
            "productPrice" => $product->getPrice(),
            "quoteId" => $product->getQuoteItemId(),
            "addToCartLink" =>  $this->cartHelper->getAddUrl($product),
            "deleteJson" => $this->cartHelper->getDeletePostJson($item)
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $printessHelper = $objectManager->get('Printess\PrintessDesigner\Helper\Printess');

        $info["variants"] = $printessHelper->getVariations($product->getId(), $product->getSku(), true);
        $info["editorSettings"] = $printessHelper->getEditorSettings();

        $options = $item->getProductOptions();

        if(!isset($options)) {
            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        }

        if(isset($options) && isset($options["attributes_info"]))
        {
            $info["selectedOptions"] = $options["attributes_info"];
        }

        return $info;
    }
}
