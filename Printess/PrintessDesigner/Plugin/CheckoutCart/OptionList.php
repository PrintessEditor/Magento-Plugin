<?php

namespace Printess\PrintessDesigner\Plugin\CheckoutCart;

use Printess\PrintessDesigner\Model\Adjustment;
// use Printess\PrintessDesigner\Model\SupplierParameter;
use Magento\Checkout\Block\Cart\Item\Renderer;

class OptionList
{

    /**
     * @param Renderer $subject
     * @param $result
     * @return array
     */
    public function afterGetOptionList(Renderer $subject, $result): array
    {

        return array_filter($result, static function ($v, $k) {

            // if (array_key_exists('option_type', $v)) {
            //     return !in_array($v['option_type'], [SupplierParameter::TYPE_NAME, Adjustment::TYPE_NAME], true);
            // }

            return !(str_starts_with($k, 'printess'));

        }, ARRAY_FILTER_USE_BOTH);

    }
}
