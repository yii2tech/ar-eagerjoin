<?php

namespace yii2tech\tests\unit\ar\eagerjoin;

use yii\db\Expression;
use yii2tech\tests\unit\ar\eagerjoin\data\Group;
use yii2tech\tests\unit\ar\eagerjoin\data\Item;

class EagerJoinTraitTest extends TestCase
{
    public function testEagerJoin()
    {
        $item = Item::find()
            ->select(['Item.*', 'group__name' => 'Group.name', 'group__code' => 'Group.code'])
            ->joinWith('group', false)
            ->andWhere(['groupId' => 2])
            ->limit(1)
            ->one();

        $this->assertTrue($item->isRelationPopulated('group'));
        $this->assertTrue($item->group instanceof Group);
        $this->assertEquals('group2', $item->group->name);
        $this->assertEquals('g2', $item->group->code);
        $this->assertEquals($item->groupId, $item->group->id);
        $this->assertFalse($item->group->isNewRecord);
    }

    public function testSkipEagerJoin()
    {
        $item = Item::find()
            ->select(['Item.*', new Expression('1 AS foo')])
            ->andWhere(['{{Item}}.[[id]]' => 2])
            ->limit(1)
            ->one();

        $this->assertFalse($item->isRelationPopulated('group'));
    }

    /**
     * @depends testEagerJoin
     */
    public function testRelatedIsNull()
    {
        $item = Item::find()
            ->select(['Item.*', 'group__name' => 'Group.name', 'group__code' => 'Group.code'])
            ->joinWith('group', false)
            ->andWhere(['groupId' => null])
            ->limit(1)
            ->one();

        $this->assertTrue($item->isRelationPopulated('group'));
        $this->assertNull($item->group);
    }
}