<?php

$installer = $this;

$installer->startSetup();

/** @var $adapter Varien_Db_Adapter_Pdo_Mysql */
$adapter = $installer->getConnection();

/*
 * Mailjet_Iframes_Model_Resource_Setup -> synchronize()
 */
//$installer->synchronize();


//if ($adapter->isTableExists($installer->getTable('iframes/iframes_test'))) {
//    $installer->run("DROP TABLE IF EXISTS {$this->getTable('iframes/iframes_test')};");
//}
//
//$table = $installer->getConnection()
//    ->newTable($installer->getTable('iframes/iframes_test'))
//    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//        'identity'  => true,
//        'unsigned'  => true,
//        'nullable'  => false,
//        'primary'   => true,
//        ), 'Website Id')
//    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
//        ), 'Code')
//    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
//        ), 'Website Name')
//    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//        'unsigned'  => true,
//        'nullable'  => false,
//        'default'   => '0',
//        ), 'Sort Order')
//    ->addColumn('default_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//        'unsigned'  => true,
//        'nullable'  => false,
//        'default'   => '0',
//        ), 'Default Group Id')
//    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//        'unsigned'  => true,
//        'default'   => '0',
//        ), 'Defines Is Website Default')
//    ->addIndex($installer->getIdxName('iframes/iframes_test', array('code'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
//        array('code'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
//    ->addIndex($installer->getIdxName('iframes/iframes_test', array('sort_order')),
//        array('sort_order'))
//    ->addIndex($installer->getIdxName('iframes/iframes_test', array('default_group_id')),
//        array('default_group_id'))
//    ->setComment('Websites');
//$installer->getConnection()->createTable($table);
//
//$installer->getConnection()->insertForce($installer->getTable('iframes/iframes_test'), array(
//    'website_id'        => 1,
//    'code'              => 'admin1',
//    'name'              => 'Admin1',
//    'sort_order'        => 0,
//    'default_group_id'  => 0,
//    'is_default'        => 0,
//));
//$installer->getConnection()->insertForce($installer->getTable('iframes/iframes_test'), array(
//    'website_id'        => 2,
//    'code'              => 'admin2',
//    'name'              => 'Admin2',
//    'sort_order'        => 0,
//    'default_group_id'  => 0,
//    'is_default'        => 0,
//));
//
//Mage::getModel('core/log_adapter', 'iframes_setup.log')->log($installer->getSubscribers());

$installer->endSetup();