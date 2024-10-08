<?php

namespace Printess\PrintessDesigner\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Dpi extends AbstractSource
{

    /**
     * @var string
     */
    protected string $optionFactory;

    /**
     * @return array
     */
    public function getAllOptions(): array
    {

        $this->_options = [
            ['label' => 'No individual setting', 'value' => ''],
            ['label' => '72 dpi', 'value' => 72],
            ['label' => '150 dpi', 'value' => 150],
            ['label' => '300 dpi', 'value' => 300],
        ];

        return $this->_options;

    }

}
