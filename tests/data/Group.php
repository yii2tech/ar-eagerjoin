<?php

namespace yii2tech\tests\unit\ar\eagerjoin\data;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 *
 * @property Item[] $items
 */
class Group extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['groupId' => 'id']);
    }
}