<?php
namespace Rissc\Printformer\Setup;

use Magento\Framework\DB\Ddl\Table;
use \Magento\Framework\Setup\UpgradeSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table as DdlTable;
use \Magento\Framework\DB\Adapter\AdapterInterface;

class UpgradeSchema
    implements UpgradeSchemaInterface
{
    const TABLE_NAME_DRAFT                = 'printformer_draft';
    const TABLE_NAME_PRODUCT              = 'printformer_product';
    const TABLE_NAME_HISTORY              = 'printformer_async_history';
    const TABLE_NAME_CUSTOMER_GROUP_RIGHT = 'printformer_customer_group_right';
    
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();
        if(version_compare($context->getVersion(), '100.0.1', '<'))
        {
            $table = $connection->newTable(
                $setup->getTable(self::TABLE_NAME_DRAFT)
            )->addColumn(
                'id',
                DdlTable::TYPE_INTEGER,
                null,
                array (
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ),
                'Draft ID'
            )->addColumn(
                'draft_id',
                DdlTable::TYPE_TEXT,
                255,
                [],
                'Draft Hash'
            )->addColumn(
                'order_item_id',
                DdlTable::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true,
                ],
                'Order Item ID'
            )->addColumn(
                'store_id',
                DdlTable::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Store ID'
            )->addIndex(
                $setup->getIdxName(self::TABLE_NAME_DRAFT, ['store_id']),
                ['store_id']
            )->addForeignKey(
                $setup->getFkName(self::TABLE_NAME_DRAFT, 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                DdlTable::ACTION_CASCADE
            )->addColumn(
                'created_at',
                DdlTable::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => DdlTable::TIMESTAMP_INIT
                ],
                'Create At'
            )->addIndex(
                $setup->getIdxName(
                    self::TABLE_NAME_DRAFT,
                    ['draft_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['draft_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );

            $setup->getConnection()->createTable($table);
        }

        if(version_compare($context->getVersion(), '100.1.5', '<'))
        {
            $connection->addColumn(
                $setup->getTable(self::TABLE_NAME_DRAFT),
                'processing_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'after' => 'created_at',
                    'comment' => 'Printformer Processing ID'
                ]
            );

            $connection->addColumn(
                $setup->getTable(self::TABLE_NAME_DRAFT),
                'processing_status',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'after' => 'processing_id',
                    'comment' => 'Printformer Processing Status'
                ]
            );
        }

        if(version_compare($context->getVersion(), '100.1.7', '<'))
        {
            $table = $connection->newTable(
                $setup->getTable(self::TABLE_NAME_HISTORY)
            )
                ->addColumn(
                    'id',
                    DdlTable::TYPE_INTEGER,
                    null,
                    array (
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true
                    ),
                    'Draft ID'
                )
                ->addColumn(
                    'draft_id',
                    DdlTable::TYPE_TEXT,
                    255,
                    [],
                    'Draft Hash'
                )
                ->addColumn(
                    'created_at',
                    DdlTable::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => DdlTable::TIMESTAMP_INIT
                    ],
                    'Create At'
                )
                ->addColumn(
                    'direction',
                    DdlTable::TYPE_TEXT,
                    35,
                    [
                        'nullable' => false,
                        'default' => 'outgoing'
                    ],
                    'Communication direction'
                )
                ->addColumn(
                    'status',
                    DdlTable::TYPE_TEXT,
                    35,
                    [
                        'nullable' => false,
                    ],
                    'Request Status'
                )
                ->addColumn(
                    'request_data',
                    DdlTable::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    'response_data',
                    DdlTable::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Response data'
                )
                ->addColumn(
                    'api_url',
                    DdlTable::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Called URL'
                );

            $setup->getConnection()->createTable($table);
        }

        if(version_compare($context->getVersion(), '100.1.21', '<'))
        {
            $connection->addColumn(
                $connection->getTableName('printformer_product'),
                'intents',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Product Intents'
                ]
            );
        }

        if(version_compare($context->getVersion(), '100.1.22', '<'))
        {
            $connection->addColumn(
                $connection->getTableName('printformer_draft'),
                'intent',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Draft Intent'
                ]
            );
        }

        if(version_compare($context->getVersion(), '100.1.23', '<'))
        {
            $connection->addColumn(
                $connection->getTableName('printformer_draft'),
                'session_unique_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 100,
                    'comment' => 'Draft Session Unique ID'
                ]
            );
        }

        if(version_compare($context->getVersion(), '100.1.24', '<'))
        {
            $connection->addColumn(
                $connection->getTableName('printformer_draft'),
                'product_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'comment' => 'Draft Product ID'
                ]
            );
        }

        if(version_compare($context->getVersion(), '100.1.25', '<'))
        {
            $connection->addColumn(
                $connection->getTableName('printformer_draft'),
                'customer_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'comment' => 'Draft Customer ID'
                ]
            );
        }

        if(version_compare($context->getVersion(), '100.1.25', '<'))
        {
            $connection->addColumn(
                $connection->getTableName('printformer_draft'),
                'user_identifier',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'user identifier'
                ]
            );
        }

        if(version_compare($context->getVersion(), '100.3.0', '<')) {
            $tableName = $connection->getTableName('printformer_draft');
            $columnName = 'user_identifier';
            if(!$connection->tableColumnExists($tableName, $columnName)) {
                $connection->addColumn(
                    $tableName,
                    $columnName,
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 55,
                        'comment' => 'user identifier'
                    ]
                );
            }
        }

        if(version_compare($context->getVersion(), '100.3.1', '<')) {
            $table = $connection->newTable($setup->getTable(self::TABLE_NAME_CUSTOMER_GROUP_RIGHT))
                ->addColumn(
                    'id',
                    DdlTable::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true
                    ]
                )
                ->addColumn('customer_group_id', DdlTable::TYPE_INTEGER, 10, ['nullable' => false, 'unsigned' => true])
                ->addColumn('draft_editor_view', DdlTable::TYPE_INTEGER, 1, ['nullable' => false, 'unsigned' => true, 'default' => 0])
                ->addColumn('draft_editor_update', DdlTable::TYPE_INTEGER, 1, ['nullable' => false, 'unsigned' => true, 'default' => 0])
                ->addColumn('review_view', DdlTable::TYPE_INTEGER, 1, ['nullable' => false, 'unsigned' => true, 'default' => 0])
                ->addColumn('review_finish', DdlTable::TYPE_INTEGER, 1, ['nullable' => false, 'unsigned' => true, 'default' => 0])
                ->addColumn('review_end', DdlTable::TYPE_INTEGER, 1, ['nullable' => false, 'unsigned' => true, 'default' => 0]);

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();

        if(version_compare($context->getVersion(), '100.3.20', '<')) {
            $customerTable = $setup->getTable('customer_entity');
            $connection->addColumn(
                $customerTable,
                'printformer_identification',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 100,
                    'comment' => 'Printformer User Identifier'
                ]
            );
        }
    }
}