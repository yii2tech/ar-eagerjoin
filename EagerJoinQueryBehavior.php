<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\ar\eagerjoin;

use yii\db\ActiveQuery;

/**
 * EagerJoinQueryBehavior simplifies building of the query, which is suitable for [[EagerJoinBehavior]] usage.
 *
 * Configuration example:
 *
 * ```php
 * class ItemQuery extends ActiveQuery
 * {
 *     public function behaviors()
 *     {
 *         return [
 *             'eagerJoin' => [
 *                 'class' => EagerJoinQueryBehavior::className(),
 *             ],
 *         ];
 *     }
 *     // ...
 * }
 * ```
 *
 * Usage example:
 *
 * ```php
 * $items = Item::find()->eagerJoinWith('group')->all();
 * ```
 *
 * @see EagerJoinBehavior
 *
 * @property ActiveQuery $owner
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class EagerJoinQueryBehavior extends RelatedAttributesBehavior
{
    /**
     * @var string boundary, which should be used to separate relation name from attribute name in selected column name.
     * Each field, mentioned in query 'select' statement, for the joined entity should be composed in format:
     * `relationName + boundary + attributeName`.
     * For example: 'group__name' will refer to the 'name' attribute of the relation 'group'.
     *
     * @see EagerJoinBehavior::boundary
     */
    public $boundary = '__';


    /**
     * Performs eager joins with the specified relations.
     * @param @param string|array $with the relations to be joined.
     * @param string|array $joinType the join type of the relations specified in `$with`.
     * @return ActiveQuery query self reference.
     */
    public function eagerJoinWith($with, $joinType = 'LEFT JOIN')
    {
        if ($this->owner->select === null) {
            $mainTableName = call_user_func([$this->owner->modelClass, 'tableName']);
            $this->owner->select(['{{' . $mainTableName . '}}.*']);
        }

        foreach ((array)$with as $relation) {
            $relatedAttributes = $this->getRelatedAttributes($this->owner->modelClass, $relation);
            $selectColumns = [];
            foreach ($relatedAttributes as $attribute) {
                $selectColumns[] = '{{' . $relation . '}}.[[' . $attribute . ']] AS ' . $relation . $this->boundary . $attribute;
            }
            $this->owner->addSelect($selectColumns);
        }
        return $this->owner->joinWith($with, false, $joinType);
    }

    /**
     * Performs eager left joins with the specified relations.
     * @param @param string|array $with the relations to be joined.
     * @return ActiveQuery query self reference.
     */
    public function eagerLeftJoinWith($with)
    {
        return $this->eagerJoinWith($with, 'LEFT JOIN');
    }

    /**
     * Performs eager inner joins with the specified relations.
     * @param @param string|array $with the relations to be joined.
     * @return ActiveQuery query self reference.
     */
    public function eagerInnerJoinWith($with)
    {
        return $this->eagerJoinWith($with, 'INNER JOIN');
    }
}