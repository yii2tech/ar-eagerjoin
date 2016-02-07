<?php

namespace yii2tech\tests\unit\ar\eagerjoin;

use yii2tech\tests\unit\ar\eagerjoin\data\Item;

class EagerJoinQueryBehaviorTest extends TestCase
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
}