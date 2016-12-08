<?php

namespace yii2tech\tests\unit\ar\eagerjoin\data;

use yii\db\ActiveRecord;
use yii2tech\ar\eagerjoin\EagerJoinTrait;

/**
 * @property int $id
 * @property int $groupId
 * @property string $name
 * @property float $price
 *
 * @property Group $group
 */
class Item extends ActiveRecord
{
    use EagerJoinTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['groupId', 'name', 'price'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     * @return ItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ItemQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'groupId']);
    }
}