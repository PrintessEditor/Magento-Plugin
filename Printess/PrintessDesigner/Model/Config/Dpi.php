<?php

namespace Printess\PrintessDesigner\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class Dpi implements OptionSourceInterface
{

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        $arr = $this->toArray();
        $ret = [];
        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            72 => '72 dpi',
            150 => '150 dpi',
            300 => '300 dpi'
        ];
    }
}
