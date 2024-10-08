<?php

namespace Printess\PrintessDesigner\Helper;

use JsonException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @param $value
     * @return bool
     */
    public function isJson($value): bool
    {
        if (is_array($value)) {
            return false;
        }

        if (is_null($value) || empty($value)) {
            return false;
        }

        try {
            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            return false;
        }

        return true;
    }
}
