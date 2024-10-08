<?php

namespace Printess\PrintessDesigner\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class OutputFormat extends AbstractSource
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
            ['label' => 'No individual setting (PDF)', 'value' => ''],
            ['label' => '.pdf (Portable Document Format)', 'value' => "pdf"],
            ['label' => '.png (Portable Network Graphics)', 'value' => "png"],
            ['label' => '.jpeg (Joint Photographic Experts Group)', 'value' => "jpg"],
            ['label' => '.tif (Tag Image File Format)', 'value' => "tif"],
        ];

        return $this->_options;

    }

}
