<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\ar\eagerjoin;

use yii\base\Behavior;
use yii\base\UnknownPropertyException;
use yii\db\ActiveRecord;

/**
 * EagerJoinBehavior
 *
 * @property ActiveRecord $owner
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class EagerJoinBehavior extends Behavior
{
    /**
     * @var string boundary, which should be used to separate relation name from attribute name
     * in selected column name.
     */
    public $boundary = '__';
    /**
     * @var array[] map for the virtual properties, which should trigger related model population,
     * in format: selectedName => [relationName, attributeName]
     * For example:
     *
     * ```php
     * [
     *     'owner' => ['user', 'name'],
     *     'email' => ['user', 'email'],
     *     'groupTag' => ['group', 'tag'],
     * ]
     * ```
     */
    public $attributeMap = [];

    /**
     * @var array related model attribute names cache.
     */
    private static $relatedAttributes = [];


    /**
     * @param string $relationName name of the relation to be ensured.
     * @return ActiveRecord related model instance
     */
    protected function ensureRelated($relationName)
    {
        if ($this->owner->isRelationPopulated($relationName)) {
            return $this->owner->{$relationName};
        }

        $relation = $this->owner->getRelation($relationName);
        $modelClass = $relation->modelClass;
        $model = new $modelClass();
        foreach ($relation->link as $relationAttribute => $ownerAttribute) {
            $model->{$relationAttribute} = $this->owner->{$ownerAttribute};
        }

        $this->owner->populateRelation($relationName, $model);
        return $model;
    }

    /**
     * @param string $relationName name of the relation to be searched.
     * @return array related model attribute names.
     */
    protected function getRelatedAttributes($relationName)
    {
        $key = get_class($this->owner) . '::' . $relationName;
        if (!isset(self::$relatedAttributes[$key])) {
            self::$relatedAttributes[$key] = $this->findRelatedAttributes($relationName);
        }
        return self::$relatedAttributes[$key];
    }

    /**
     * @param string $relationName name of the relation to be searched.
     * @return array related model attribute names.
     */
    protected function findRelatedAttributes($relationName)
    {
        /* @var $model ActiveRecord */
        $relation = $this->owner->getRelation($relationName);
        $modelClass = $relation->modelClass;

        $getTableSchemaCallback = [$modelClass, 'getTableSchema'];
        if (is_callable($getTableSchemaCallback, false)) {
            $tableSchema = call_user_func($getTableSchemaCallback);
            return array_keys($tableSchema->columns);
        }

        $model = new $modelClass();
        return $model->attributes();
    }

    /**
     * Checks if related model attribute can be set.
     * @param string $name attribute name
     * @return boolean whether related model attribute exists or not.
     */
    protected function hasRelatedAttribute($name)
    {
        if (isset($this->attributeMap[$name])) {
            return true;
        }
        if (strpos($name, $this->boundary) === false) {
            return false;
        }
        list($relation, $attribute) = explode($this->boundary, $name, 2);

        $relatedAttributes = $this->getRelatedAttributes($relation);
        if (in_array($attribute, $relatedAttributes, true)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $name attribute name.
     * @param mixed $value attribute value.
     * @return boolean whether related model attribute has been set or not.
     */
    protected function setRelatedAttribute($name, $value)
    {
        if (isset($this->attributeMap[$name])) {
            list($relation, $attribute) = $this->attributeMap[$name];
        } else {
            if (strpos($name, $this->boundary) === false) {
                return false;
            }
            list($relation, $attribute) = explode($this->boundary, $name, 2);
        }

        $model = $this->ensureRelated($relation);
        $model->{$attribute} = $value;
        return true;
    }

    // Property Access Extension:

    /**
     * PHP setter magic method.
     * This method is overridden so that joined relation attributes can be set like properties.
     * @param string $name property name
     * @param mixed $value property value
     * @throws UnknownPropertyException if the property is not defined
     */
    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (UnknownPropertyException $exception) {
            if ($this->owner !== null) {
                if ($this->setRelatedAttribute($name, $value)) {
                    return;
                }
            }
            throw $exception;
        }
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if (parent::canSetProperty($name, $checkVars)) {
            return true;
        }
        if ($this->owner == null) {
            return false;
        }
        return $this->hasRelatedAttribute($name);
    }
}