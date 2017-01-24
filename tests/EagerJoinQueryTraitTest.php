<?php

namespace yii2tech\tests\unit\ar\eagerjoin;

use yii2tech\tests\unit\ar\eagerjoin\data\Group;
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

    /**
     * @depends testEagerJoinWith
     */
    public function testAlias()
    {
        // SELECT Item.*, group_alias.id AS group__id, ...
        $query = Item::find()->eagerJoinWith('group AS group_alias', 'INNER JOIN');

        $this->assertArrayHasKey('group' . Item::eagerJoinBoundary() . 'id', $query->select);
        $this->assertEquals('group_alias.id', $query->select['group' . Item::eagerJoinBoundary() . 'id']);

        $item = $query->one();
        $this->assertTrue($item->isRelationPopulated('group'));
        $this->assertEquals($item->groupId, $item->group->id);

        // SELECT Item.*, Group.id AS group__id, ...
        $query = Item::find()->eagerJoinWith('group', 'INNER JOIN');

        $this->assertArrayHasKey('group' . Item::eagerJoinBoundary() . 'id', $query->select);
        $this->assertEquals(Group::tableName() . '.id', $query->select['group' . Item::eagerJoinBoundary() . 'id']);

        $item = $query->one();
        $this->assertTrue($item->isRelationPopulated('group'));
        $this->assertEquals($item->groupId, $item->group->id);
    }
}