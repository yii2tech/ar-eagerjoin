<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\ar\eagerjoin;

use yii\db\BaseActiveRecord;

/**
 * EagerJoinTrait provides support for ActiveRecord relation eager loading via join without extra query.
 * This trait should be used in the [[\yii\db\ActiveRecord]] descendant class.
 *
 * Setup example:
 *
 * ```php
 * use yii\db\ActiveRecord;
 * use yii2tech\ar\eagerjoin\EagerJoinTrait;
 *
 * class Item extends ActiveRecord
 * {
 *     use EagerJoinTrait;
 *
 *     public function getGroup()
 *     {
 *         return $this->hasOne(Group::className(), ['id' => 'groupId']);
 *     }
 *
 *     // ...
 * }
 * ```
 *
 * Usage example:
 *
 * ```php
 * $items = Item::find()
 *     ->select(['{{item}}.*', '{{group}}.[[name]] AS group__name', '{{group}}.[[code]] AS group__code'])
 *     ->joinWith('group', false) // no regular eager loading!!!
 *     ->all();
 *
 * foreach ($items as $item) {
 *     var_dump($item->isRelationPopulated('group')); // outputs `true`!!!
 *     echo $item->group->name; // outputs group name
 *     echo $item->group->code; // outputs group code
 * }
 * ```
 *
 * @see EagerJoinQueryTrait
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait EagerJoinTrait
{
    /**
     * Populates an active record object using a row of data from the database/storage.
     * Populates related records if corresponding keys are present in the data set ($row).
     * @see BaseActiveRecord::populateRecord()
     *
     * @param BaseActiveRecord|static $record the record to be populated. In most cases this will be an instance
     * created by [[instantiate()]] beforehand.
     * @param array $row attribute values (name => value)
     */
    public static function populateRecord($record, $row)
    {
        $boundary = '__';
        $relatedAttributes = [];
        foreach ($row as $name => $value) {
            if (strpos($name, $boundary) === false) {
                continue;
            }
            list($relationName, $attribute) = explode($boundary, $name, 2);
            $relatedAttributes[$relationName][$attribute] = $value;
            unset($row[$name]);
        }

        parent::populateRecord($record, $row);

        foreach ($relatedAttributes as $relationName => $attributes) {
            $record->populateRelation($relationName, $record->instantiateEagerJoinRelated($relationName, $attributes));
        }
    }

    /**
     * Creates an instance of the ActiveRecord related via particular relation.
     * @param string $relationName name of the relation to be instantiated.
     * @param array $attributes
     * @return BaseActiveRecord related model instance.
     */
    protected function instantiateEagerJoinRelated($relationName, $attributes)
    {
        $isNull = true;
        foreach ($attributes as $name => $value) {
            if ($value !== null) {
                $isNull = false;
                break;
            }
        }
        if ($isNull) {
            return null;
        }

        /* @var $this BaseActiveRecord */
        /* @var $modelClass BaseActiveRecord */

        $relation = $this->getRelation($relationName);
        $modelClass = $relation->modelClass;

        if ($relation->via === null) {
            foreach ($relation->link as $relationAttribute => $ownerAttribute) {
                if (!isset($attributes[$relationAttribute])) {
                    $attributes[$relationAttribute] = $this->{$ownerAttribute};
                }
            }
        }

        $model = $modelClass::instantiate($attributes);
        $model->populateRecord($model, $attributes);

        $model->afterFind();

        return $model;
    }
}