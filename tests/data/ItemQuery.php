<?php

namespace yii2tech\tests\unit\ar\eagerjoin\data;

use yii\db\ActiveQuery;
use yii2tech\ar\eagerjoin\EagerJoinQueryTrait;

class ItemQuery extends ActiveQuery
{
    use EagerJoinQueryTrait;

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