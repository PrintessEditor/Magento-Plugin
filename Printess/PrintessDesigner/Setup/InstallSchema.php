<?php

namespace Printess\PrintessDesigner\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{

	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
        $orderTable = 'sales_order';
        $orderGridTable = 'sales_order_grid';
        $orderItems = "sales_order_item";

        //Sales order item table
        $setup->getConnection()->addColumn(
            $setup->getTable($orderItems),
            'printess_jobid',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 8192,
                'comment' =>'Production files'
            ]
        );
        
        $setup->getConnection()->addColumn(
            $setup->getTable($orderItems),
            'printess_production_files',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 8192,
                'comment' =>'Production files'
            ]
        );

		$setup->endSetup();
	}
}

?>