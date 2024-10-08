<?php

namespace Printess\PrintessDesigner\Plugin\Adminhtml;

use Printess\PrintessDesigner\Model\Adjustment;
// use Printess\PrintessDesigner\Model\SupplierParameter;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;

class OrderOptions {

    /**
     * @param DefaultColumn $subject
     * @param $result
     * @return array
     */
    public function afterGetOrderOptions(DefaultColumn $subject, $result): array
    {

        return array_filter($result, static function ($v, $k) {

            // if (array_key_exists('option_type', $v)) {
            //     return !in_array($v['option_type'], [SupplierParameter::TYPE_NAME, Adjustment::TYPE_NAME], true);
            // }

            return !(str_starts_with($k, 'printess'));

        }, ARRAY_FILTER_USE_BOTH);

    }
}
