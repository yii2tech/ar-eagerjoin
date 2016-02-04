<?php

namespace yii2tech\tests\unit\ar\eagerjoin\data;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $groupId
 * @property string $name
 * @property float $price
 *
 * @property Group $group
 */
class Item extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            '' => [
                //'class' => Behavior::className(),
            ],
        ];
    }

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
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'groupId']);
    }
}