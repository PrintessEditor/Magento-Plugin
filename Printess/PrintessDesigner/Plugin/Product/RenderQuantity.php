<?php

namespace Printess\PrintessDesigner\Plugin\Product;

use Printess\PrintessDesigner\Helper\Printess;
use Magento\Catalog\Block\Product\View;
use Magento\Framework\Exception\NoSuchEntityException;

class RenderQuantity
{

    /**
     * @var Printess
     */
    protected Printess $helperPrintess;

    /**
     * @param Printess $helperPrintess
     */
    public function __construct(
        Printess $helperPrintess
    ) {
        $this->helperPrintess = $helperPrintess;
    }

    /**
     * @param View $subject
     * @param $result
     * @return false
     * @throws NoSuchEntityException
     */
    public function afterShouldRenderQuantity(View $subject, $result): bool
    {
        if ($this->helperPrintess->hasTemplate($subject->getProduct()->getSku())) {
            return false;
        }

        return $result;
    }
}
