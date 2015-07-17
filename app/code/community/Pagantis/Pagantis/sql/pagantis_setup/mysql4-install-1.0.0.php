<?php

$installer = $this;
$installer->startSetup();
$installer->addAttribute("order", "pagantis_transaction", array("type" => "varchar"));
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_grid'), 'pagantis_transaction', 'varchar(255)');
$installer->endSetup();
