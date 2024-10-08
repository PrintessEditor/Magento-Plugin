<?php

namespace Printess\PrintessDesigner\Plugin\Adminhtml;

use Magento\Sales\Block\Adminhtml\Order\View\Items;

class OrderColumnsHead
{

    /**
     * @param $items
     * @param $a
     * @param $b
     * @return array
     */
    private function changeColumnPosition($items, $a, $b): array
    {
        $elem1 = array_splice($items, $a, 1);
        $elem2 = array_splice($items, 0, $b);

        return array_merge($elem2, $elem1, $items);
    }

    public function afterGetColumns(Items $subject, $result): array
    {

        $result = $this->changeColumnPosition($result, 10, 0);
        return $this->changeColumnPosition($result, 11, 2);
    }
}

