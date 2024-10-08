<?php

namespace Printess\PrintessDesigner\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class OutputFormat implements OptionSourceInterface
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
            "pdf" => '.pdf (Portable Document Format)',
            "png" => '.png (Portable Network Graphics)',
            "jpg" => '.jpg (Joint Photographic Experts Group)',
            "tif" => '.tif (Tag Image File Format)'
        ];
    }
}
