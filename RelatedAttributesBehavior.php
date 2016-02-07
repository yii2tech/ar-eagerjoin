<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\ar\eagerjoin;

use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * RelatedAttributesBehavior provides ability to get list of attributes of the related ActiveRecord.
 * It uses internal static cache for the better performance.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class RelatedAttributesBehavior extends Behavior
{
    /**
     * @var array related model attribute names cache.
     */
    private static $relatedAttributes = [];


    /**
     * Get the list of related model attributes for the given ActiveRecord instance or class.
     * @param string $modelOrClass model instance or class name.
     * @param string $relationName name of the relation to be searched.
     * @return array related model attribute names.
     */
    public function getRelatedAttributes($modelOrClass, $relationName)
    {
        $className = is_object($modelOrClass) ? get_class($modelOrClass) : $modelOrClass;
        if (!isset(self::$relatedAttributes[$className][$relationName])) {
            self::$relatedAttributes[$className][$relationName] = $this->findRelatedAttributes($modelOrClass, $relationName);
        }
        return self::$relatedAttributes[$className][$relationName];
    }

    /**
     * @param string $modelOrClass model instance or class name.
     * @param string $relationName name of the relation to be searched.
     * @return array related model attribute names.
     */
    protected function findRelatedAttributes($modelOrClass, $relationName)
    {
        /* @var $primaryModel ActiveRecord */
        if (is_object($modelOrClass)) {
            $primaryModel = $modelOrClass;
        } else {
            $primaryModel = new $modelOrClass();
        }

        $relation = $primaryModel->getRelation($relationName);
        $modelClass = $relation->modelClass;

        $getTableSchemaCallback = [$modelClass, 'getTableSchema'];
        if (is_callable($getTableSchemaCallback, false)) {
            $tableSchema = call_user_func($getTableSchemaCallback);
            return array_keys($tableSchema->columns);
        }

        /* @var $model ActiveRecord */
        $model = new $modelClass();
        return $model->attributes();
    }

    /**
     * Clears related attributes cache.
     */
    public function clearRelatedAttributes()
    {
        self::$relatedAttributes = [];
    }
}