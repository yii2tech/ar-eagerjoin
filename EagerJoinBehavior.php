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
     * @var string name of the 'has one' relation, which should be eager joined.
     */
    public $relation;
    /**
     * @var array
     */
    public $attributeMap = [];
    /**
     * @var string
     */
    public $attributePrefix;

    /**
     * @var array
     */
    private static $relatedAttributeNames = [];

    /**
     * @return ActiveRecord related model instance
     */
    protected function ensureRelated()
    {
        if ($this->owner->isRelationPopulated($this->relation)) {
            return $this->owner->{$this->relation};
        }

        $relation = $this->owner->getRelation($this->relation);
        $modelClass = $relation->modelClass;
        $model = new $modelClass();
        $this->owner->populateRelation($this->relation, $model);
        return $model;
    }

    /**
     * @return array related model attribute names.
     */
    protected function getRelatedAttributeNames()
    {
        $key = get_class($this->owner) . '::' . $this->relation;
        if (!isset(self::$relatedAttributeNames[$key])) {
            self::$relatedAttributeNames[$key] = $this->findRelatedAttributeNames();
        }
        return self::$relatedAttributeNames[$key];
    }

    /**
     * @return array related model attribute names.
     */
    protected function findRelatedAttributeNames()
    {
        /* @var $model ActiveRecord */
        $relation = $this->owner->getRelation($this->relation);
        $modelClass = $relation->modelClass;
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

        $relatedAttributes = $this->getRelatedAttributeNames();
        if (in_array($name, $relatedAttributes, true)) {
            return true;
        }

        if ($this->attributePrefix === null) {
            return false;
        }
        return strncmp($this->attributePrefix, $name, strlen($this->attributePrefix)) === 0;
    }

    /**
     * @param string $name attribute name.
     * @param mixed $value attribute value.
     * @return boolean whether related model attribute has been set or not.
     */
    protected function setRelatedAttribute($name, $value)
    {
        if (isset($this->attributeMap[$name])) {
            $attribute = $this->attributeMap[$name];
        } elseif ($this->attributePrefix !== null && strncmp($this->attributePrefix, $name, strlen($this->attributePrefix)) === 0) {
            $attribute = substr($name, strlen($this->attributePrefix));
        } else {
            $relatedAttributes = $this->getRelatedAttributeNames();
            if (in_array($name, $relatedAttributes, true)) {
                $attribute = $name;
            } else {
                return false;
            }
        }
        $model = $this->ensureRelated();
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