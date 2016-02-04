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
                $model = $this->ensureRelated();
                if ($model->hasAttribute($name)) {
                    $model->{$name} = $value;
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
        $model = $this->ensureRelated();
        return $model->hasAttribute($name);
    }
}