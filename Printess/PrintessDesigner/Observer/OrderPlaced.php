<?php
    namespace Printess\PrintessDesigner\Observer;

    use Magento\Framework\Event\ObserverInterface;
    use Magento\Framework\Event\Observer;
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Magento\Store\Model\ScopeInterface;
    use Magento\Framework\App\Config\ScopeConfigInterface;
    use Printess\PrintessDesigner\Api\PrintessRepository;
    use \Magento\Directory\Api\CountryInformationAcquirerInterface;
    use Magento\Store\Model\StoreManagerInterface;

    //https://magento.stackexchange.com/questions/130150/magento-2-add-extra-data-to-an-order
    //https://www.thecoachsmb.com/how-to-add-order-attribute-in-magento-2/
    //https://www.mujahidh.com/how-to-create-custom-order-attribute-save-and-show-in-admin-grid-in-magento-2/
    //https://www.thecoachsmb.com/how-to-add-order-attribute-in-magento-2/
    //https://magento.stackexchange.com/questions/349733/how-to-add-a-custom-attribute-in-the-items-ordered-table

    class OrderPlaced implements ObserverInterface
    {
        protected ProductRepositoryInterface $productRepository;
        protected ScopeConfigInterface $scopeConfig;
        protected CountryInformationAcquirerInterface $countryInformation;
        protected StoreManagerInterface $storeManager;

        public function __construct(ProductRepositoryInterface $productRepository,
                                    ScopeConfigInterface $scopeConfig,
                                    CountryInformationAcquirerInterface $countryInformation,
                                    StoreManagerInterface $storeManager) {
            $this->productRepository = $productRepository;
            $this->scopeConfig = $scopeConfig;
            $this->countryInformation = $countryInformation;
            $this->storeManager = $storeManager;
        }

        private function parseStreet($street, $index)
        {
            $splits = is_array($street) ? $street : explode("\n", str_replace("\r\n", "\n", $street));

            if(count($splits) > $index)
            {
                return $splits[$index];
            }
            else
            {
                return "";
            }
        }

        private function createVdpFormData($order): array {
            $ret = array();

            $shippingAddress = $order->getShippingAddress();

            if(!isset($shippingAddress)) {
                $shippingAddress = $order->getBillingAddress();
            }

            if(isset($shippingAddress))
            {
                $firstName = $shippingAddress->getFirstname();
                $middleName = $shippingAddress->getMiddlename();
                $lastName = $shippingAddress->getLastname();

                if($firstName !== null && !empty($firstName)) {
                    $ret["Firstname"] = $firstName;
                }

                if($middleName !== null && !empty($middleName)) {
                    $ret["Middlename"] = $middleName;
                }

                if($lastName !== null && !empty($lastName)) {
                    $ret["Lastname"] = $lastName;
                }

                $fullName = "";

                if($firstName !== null && !empty($firstName)) {
                    $fullName = $firstName;
                }

                if($middleName !== null && !empty($middleName)) {
                    if(!empty($fullName)) {
                        $fullName = $fullName . " ";
                    }

                    $fullName = $fullName . $middleName;
                }

                if($lastName !== null && !empty($lastName)) {
                    if(!empty($fullName)) {
                        $fullName = $fullName . " ";
                    }

                    $fullName = $fullName . $lastName;
                }
            } else {
                $customerFirstName = $order->getCustomerFirstname();
                $customerMiddleName = $order->getCustomerMiddlename();
                $customerLastName = $order->getCustomerLastname();
    
                if($customerFirstName !== null && !empty($customerFirstName)) {
                    $ret["Firstname"] = $customerFirstName;
                }
    
                if($customerMiddleName !== null && !empty($customerMiddleName)) {
                    $ret["Middlename"] = $customerMiddleName;
                }
    
                if($customerLastName !== null && !empty($customerLastName)) {
                    $ret["Lastname"] = $customerLastName;
                }

                $fullName = "";

                if($customerFirstName !== null && !empty($customerFirstName)) {
                    $fullName = $customerFirstName;
                }

                if($customerMiddleName !== null && !empty($customerMiddleName)) {
                    if(!empty($fullName)) {
                        $fullName = $fullName . " ";
                    }

                    $fullName = $fullName . $customerMiddleName;
                }

                if($customerLastName !== null && !empty($customerLastName)) {
                    if(!empty($fullName)) {
                        $fullName = $fullName . " ";
                    }

                    $fullName = $fullName . $customerLastName;
                }
            }

            $customerFirstName = $order->getCustomerFirstname();
            $customerMiddleName = $order->getCustomerMiddlename();
            $customerLastName = $order->getCustomerLastname();

            if($customerFirstName !== null && !empty($customerFirstName)) {
                $ret["CustomerFirstname"] = $customerFirstName;
            }

            if($customerMiddleName !== null && !empty($customerMiddleName)) {
                $ret["CustomerMiddlename"] = $customerMiddleName;
            }

            if($customerLastName !== null && !empty($customerLastName)) {
                $ret["CustomerLastname"] = $customerLastName;
            }

            $customerFullName = "";

            if($customerFirstName !== null && !empty($customerFirstName)) {
                $customerFullName = $customerFirstName;
            }

            if($customerMiddleName !== null && !empty($customerMiddleName)) {
                if(!empty($customerFullName)) {
                    $customerFullName = $customerFullName . " ";
                }

                $customerFullName = $customerFullName . $customerMiddleName;
            }

            if($customerLastName !== null && !empty($customerLastName)) {
                if(!empty($customerFullName)) {
                    $customerFullName = $customerFullName . " ";
                }

                $customerFullName = $customerFullName . $customerLastName;
            }

            return $ret;
        }

        private function createDropshipAddress(PrintessRepository $printess, $order, $customerId): int
        {
            $shippingAddress = $order->getShippingAddress();

            if(!isset($shippingAddress)) {
                $shippingAddress = $order->getBillingAddress();
            }

            if(isset($shippingAddress))
            {
                $dropshipData = array(
                    "companyName" => $shippingAddress->getCompany(),
                    "address1" => $this->parseStreet($shippingAddress->getStreet(), 0),
                    "address2" => $this->parseStreet($shippingAddress->getStreet(), 1),
                    "city" => $shippingAddress->getCity(),
                    "country" => $shippingAddress->getCountryId(),
                    "countryState" => $shippingAddress->getRegion(),
                    "email" => $shippingAddress->getEmail(),
                    "firstName" => $shippingAddress->getFirstname(),
                    "lastName" => $shippingAddress->getLastname(),
                    "middleName" => $shippingAddress->getMiddlename(),
                    "phone" => $shippingAddress->getTelephone(),
                    "fax" => $shippingAddress->getFax(),
                    "zip" => $shippingAddress->getPostcode()
                );

                foreach($dropshipData as $key => $value)
                {
                    if(!isset($value))
                    {
                        $value = "";
                    }

                    $dropshipData[$key] = mb_convert_encoding($value, "utf-8", "iso-8859-1");
                }

                if(method_exists($shippingAddress, "getShippingMethod")) {
                    $shippingMethod = $shippingAddress->getShippingMethod();
                } else {
                    $shippingMethod = "";
                }

                $dropshipDataPaket = array(
                    "userId" => $customerId,
                    "type" => "printess-shipping",
                    "json" => json_encode($dropshipData),
                    "shippingMethod" => $shippingMethod
                );

                return $printess->createDropshippingAddress($dropshipDataPaket);
            }
            
            return -1;
        }

        public function produce(PrintessRepository $printess, $storeUrl, $storeScope, $templateName, $order, $itemCount, $outputDpi, $outputFormat)
        {
            $outputFileFormat = $outputFormat;

            if(!isset($outputFileFormat) || $outputFileFormat == "") {
                $outputFileFormat = $this->scopeConfig->getValue('designer/output/format', $storeScope);

                if(!isset($outputFileFormat) || $outputFileFormat = "") {
                    $outputFileFormat = "pdf";
                }
            }

            $origin = $storeUrl;//$this->scopeConfig->getValue('designer/output/origin', $storeScope);

            if(!isset($origin) || $origin = "") {
                $origin = $storeUrl;
            }

            $produceData = [//OutputType = lineItem.OutputType,
                'templateName' => $templateName,
                'externalOrderId' => $order->getId(),
                'usePublishedVersion' => true,
                'copies' => $itemCount,
                'outputSettings' => [
                    'dpi' => (int)($outputDpi ?? $this->scopeConfig->getValue('designer/output/dpi', $storeScope)),
                    'optimizeImages' => (bool)$this->scopeConfig->getValue('designer/output/optimize_images', $storeScope),
                ],
                'origin' => $origin,
                'outputType' => $outputFileFormat
            ];

            $vdpFormData = $this->createVdpFormData($order);

            if(count($vdpFormData) > 0) {
                $produceData["vdp"] = array(
                    "form" => $vdpFormData
                );
            }

            return $printess->produce($produceData);
        }

        public function dropshipProduce(PrintessRepository $printess,
            $storeUrl,
            $storeScope,
            $templateName,
            $order,
            $lineItem,
            $dropshipId,
            $productDefinitionId,
            $dropShipItemCount,
            $otherItemCount,
            $outputDpi, $outputFormat)
        {
            $outputFileFormat = $outputFormat;

            if(!isset($outputFileFormat) || $outputFileFormat == "") {
                $outputFileFormat = $this->scopeConfig->getValue('designer/output/format', $storeScope);

                if(!isset($outputFileFormat) || $outputFileFormat = "") {
                    $outputFileFormat = "pdf";
                }
            }

            $origin = $storeUrl;//$this->scopeConfig->getValue('designer/output/origin', $storeScope);

            if(!isset($origin) || $origin = "") {
                $origin = $storeUrl;
            }

            $produceData = [//OutputType = lineItem.OutputType,
                'templateName' => $templateName,
                'externalOrderId' => $order->getId(),
                'usePublishedVersion' => true,
                'copies' => $dropShipItemCount + $otherItemCount,
                'outputSettings' => [
                    'dpi' => (int)($outputDpi ?? $this->scopeConfig->getValue('designer/output/dpi', $storeScope)),
                    'optimizeImages' => (bool)$this->scopeConfig->getValue('designer/output/optimize_images', $storeScope),
                ],
                'origin' => $origin,
                'outputType' => $outputFileFormat
            ];

            $vdpFormData = $this->createVdpFormData($order);

            if(count($vdpFormData) > 0) {
                $produceData["vdp"] = array(
                    "form" => $vdpFormData
                );
            }

            $callbackPayload = array(
                "lineItemId" => $lineItem->getId(),
                "orderId" => $order->getId(),
                "domain"=> $storeUrl
            );

            $dropshipData = array(
                "dropshipDataId" => $dropshipId,
                "callbackType" => 2,
                "productDefinitionId" => $productDefinitionId ? parseInt($productDefinitionId, 10) : 0,
                "callbackPayload" => json_encode($callbackPayload),
                "linkedOrderLineItems" => ($dropShipItemCount + $otherItemCount) > 0 ? $dropShipItemCount + $otherItemCount : 0,
                "linkedOrderLineItemsId" => ($dropShipItemCount + $otherItemCount) > 0 ? "" . $order->getId() : ""
            );

            return $printess->produce($produceData, $dropshipData);
        }

        public function execute(Observer $observer)
        {
            $order = $observer->getEvent()->getOrder();

            $storeScope = ScopeInterface::SCOPE_STORE;
            $serviceToken = $this->scopeConfig->getValue('designer/api_token/service_token', $storeScope);

            $printess = new PrintessRepository($serviceToken);

            $dropShippingItems = array();
            $nonDropShippingItems = array();
            $productDefinitionIds = array();
            $dropShipAddressId = -1;

            $orderData = $order->getData();

            foreach ($order->getAllVisibleItems() as $_item) {
                $options = $_item->getProductOptions();

                if(isset($options['additional_options']) && isset($options['additional_options']['printess_save_token']) && isset($options['additional_options']['printess_save_token']["value"]) && $options['additional_options']['printess_save_token']["value"] != "") {
                    $product = $this->productRepository->getById($_item->getData("product_id"));

                    if(!isset($product)) {
                        $product = $this->productRepository->get($_item->getSku());
                    }

                    $dropShippingId = $product->getData('printess_dropship_product_definition_id');

                    if(!is_numeric($dropShippingId))
                    {
                        $dropShippingId = -1;
                    }

                    $productDefinitionIds[$_item->getId()] = $dropShippingId;

                    if($dropShippingId >= 0)
                    {
                        $dropShippingItems[] = $_item;
                    }
                    else
                    {
                        $nonDropShippingItems[] = $_item;
                    }
                }
            }

            if(!$order->getIsVirtual() && count($dropShippingItems) > 0)
            {  
                $dropShipAddressId = $this->createDropshipAddress($printess, $order, $order->getCustomerId());
            }

            if(count($dropShippingItems) > 0)
            {
                foreach ($dropShippingItems as &$_item) {
                   $options = $_item->getProductOptions();
                   $itemData = $_item->getData();
                   
                   try
                   {
                       if(!isset($itemData["printess_jobid"]))
                       {
                           $productionResult = $this->dropshipProduce($printess,
                               $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
                               $storeScope,
                               $options['additional_options']['printess_save_token']['value'],
                               $order,
                               $_item,
                               $dropShipAddressId,
                               array_key_exists($_item->getId(), $productDefinitionIds) ? $productDefinitionIds[$_item->getId()] : -1,
                               count($dropShippingItems),
                               count($nonDropShippingItems),
                               $product->getData('printess_output_dpi'),
                               $product->getData('printess_output_format')
                           );
                           
                           $itemData["printess_jobid"] = json_encode($productionResult);
                           
                           while(true) {
                               $productionStatus = $printess->getProductionStatus($productionResult->jobId);
                               
                               if (property_exists($productionStatus, "isFinalStatus") && $productionStatus->isFinalStatus === true) {
                                   if ($productionStatus->isSuccess === false) {
                                       $results[] = array(
                                           "jobId" => $item->jobId,
                                           "error" => json_encode($productionStatus->errorDetails)
                                       );
                                       break;
                                   } else {
                                       $obj = new \ReflectionObject($productionStatus->result->r);
                                       $propeties = $obj->getProperties();
                                       $urls = array();
                                       foreach($propeties as $property) {
                                           $name = $property->getName();
                                           $value = $productionStatus->result->r->$name;
                                           
                                           $urls[] =  array("document" => $name, "url" => $value);
                                       }
                                       
                                       $itemData["printess_production_files"] = json_encode($urls);
                                       break;
                                   }
                               }
                           }
                       }
                   }
                   catch(\Exception $e)
                   {
                       $itemData["printess_jobid"] = array(
                            "saveToken" => $options['additional_options']['printess_save_token']['value'],
                            "error" => json_encode($e)
                        );
                       
                       $itemData["printess_production_files"] = "";
                   }
                   
                   $_item->setData($itemData);
                   //$_item->save();
                }
            }

            foreach ($nonDropShippingItems as &$_item) {
                $options = $_item->getProductOptions();
                $itemData = $_item->getData();
                
                try
                {
                    if(!isset($itemData["printess_jobid"]))
                    {
                        $productionResult = $this->produce($printess, $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB), $storeScope,
                            $options['additional_options']['printess_save_token']['value'],
                            $order,
                            count($dropShippingItems) + count($nonDropShippingItems),
                            $product->getData('printess_output_dpi'),
                            $product->getData('printess_output_format'));
                        
                        $itemData["printess_jobid"] = json_encode($productionResult);
                        
                        while(true) {
                            $productionStatus = $printess->getProductionStatus($productionResult->jobId);
                            
                            if (property_exists($productionStatus, "isFinalStatus") && $productionStatus->isFinalStatus === true) {
                                if ($productionStatus->isSuccess === false) {
                                    $results[] = array(
                                        "jobId" => $item->jobId,
                                        "error" => json_encode($productionStatus->errorDetails)
                                    );
                                    break;
                                } else {
                                    $obj = new \ReflectionObject($productionStatus->result->r);
                                    $propeties = $obj->getProperties();
                                    $urls = array();
                                    foreach($propeties as $property) {
                                        $name = $property->getName();
                                        $value = $productionStatus->result->r->$name;
                                        
                                        $urls[] =  array("document" => $name, "url" => $value);
                                    }
                                    
                                    $itemData["printess_production_files"] = json_encode($urls);

                                    break;
                                }
                            }
                        }
                    }
                }
                catch(\Exception $e)
                {
                    $itemData["printess_jobid"] = array(
                        "saveToken" => $options['additional_options']['printess_save_token']['value'],
                        "error" => json_encode($e)
                    );
                    
                    $itemData["printess_production_files"] = "";
                }
                
                $_item->setData($itemData);
                //$_item->save();
            }

            return $this;
        }
    }
?>