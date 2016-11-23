<?php

namespace yii2tech\tests\unit\ar\eagerjoin;

use yii2tech\tests\unit\ar\eagerjoin\data\Item;

class EagerJoinQueryTraitTest extends TestCase
{
    public function testEagerJoinWith()
    {
        $query = Item::find()->eagerJoinWith('group');

        $this->assertNotEmpty($query->select);
        $this->assertNotEmpty($query->joinWith);

        $item = $query->andWhere(['{{Item}}.[[name]]' => 'item1'])->one();

        $this->assertTrue($item->isRelationPopulated('group'));
        $this->assertEquals('item1', $item->name);
        $this->assertEquals('1', $item->groupId);
        $this->assertEquals('1', $item->group->id);
        $this->assertEquals('group1', $item->group->name);
        $this->assertEquals('g1', $item->group->code);
    }

    /**
     * @depends testEagerJoinWith
     */
    public function testEagerJoinWithCallback()
    {
        $rows = Item::find()
            ->eagerJoinWith(['group' => function ($query) {
                /* @var $query \yii\db\ActiveQuery */
                $query->andOnCondition(['{{Group}}.[[id]]' => 1]);
            }], 'INNER JOIN')
            ->all();

        $this->assertCount(2, $rows);
    }
}