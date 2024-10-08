<?php

namespace Printess\PrintessDesigner\Plugin\Email\Order;

class DefaultOrder
{

    /**
     * @param \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder $subject
     * @param $result
     * @return array
     */
    public function afterGetItemOptions(\Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder $subject, $result)
    {
        return array_filter($result, static function ($v, $k) {

            if (array_key_exists('option_type', $v)) {
                return !in_array($v['option_type'], ["save_token"], true);
            }

            return !(str_starts_with($k, 'printess'));

        }, ARRAY_FILTER_USE_BOTH);
    }

    public function getThumbnailUrl($item)
    {
        return "https://community.magento.com/html/assets/Adobe_Corporate_Horizontal_Lockup_Red_RGB.png";
    }
}
