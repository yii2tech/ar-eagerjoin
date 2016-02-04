<?php

namespace yii2tech\tests\unit\ar\eagerjoin;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();

        $this->setupTestDbData();
    }

    protected function tearDown()
    {
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ], $config));
    }

    /**
     * @return string vendor path
     */
    protected function getVendorPath()
    {
        return dirname(__DIR__) . '/vendor';
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }

    /**
     * Setup tables for test ActiveRecord
     */
    protected function setupTestDbData()
    {
        $db = Yii::$app->getDb();

        // Structure :

        $table = 'Group';
        $columns = [
            'id' => 'pk',
            'name' => 'string',
            'code' => 'string',
        ];
        $db->createCommand()->createTable($table, $columns)->execute();

        $table = 'Item';
        $columns = [
            'id' => 'pk',
            'groupId' => 'integer',
            'name' => 'string',
            'price' => 'float',
        ];
        $db->createCommand()->createTable($table, $columns)->execute();

        // Data :

        $db->createCommand()->batchInsert('Group', ['name', 'code'], [
            ['group1', 'g1'],
            ['group2', 'g2'],
        ])->execute();

        $db->createCommand()->batchInsert('Item', ['groupId', 'name', 'price'], [
            [1, 'item1', 10],
            [2, 'item2', 12],
            [1, 'item3', 14],
            [2, 'item4', 16],
        ])->execute();
    }
}
