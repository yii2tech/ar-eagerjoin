<?php

namespace yii2tech\tests\unit\ar\eagerjoin\data;

use yii\db\ActiveQuery;
use yii2tech\ar\eagerjoin\EagerJoinQueryBehavior;

class ItemQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'eagerJoin' => [
                'class' => EagerJoinQueryBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     * @return Item[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Item|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}