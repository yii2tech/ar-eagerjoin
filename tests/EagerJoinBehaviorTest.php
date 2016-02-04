<?php

namespace yii2tech\tests\unit\ar\eagerjoin;

use yii\db\Expression;
use yii2tech\tests\unit\ar\eagerjoin\data\Group;
use yii2tech\tests\unit\ar\eagerjoin\data\Item;

class EagerJoinBehaviorTest extends TestCase
{
    public function testEagerJoin()
    {
        $item = Item::find()
            ->select(['{{item}}.*', '{{group}}.code'])
            ->joinWith('group', false)
            ->one();

        $this->assertTrue($item->isRelationPopulated('group'));
        $this->assertTrue($item->group instanceof Group);
        $this->assertNotEmpty($item->group->code);
    }

    public function testSkipEagerJoin()
    {
        $item = Item::find()
            ->select(['{{item}}.*', new Expression('1 AS foo')])
            ->one();

        $this->assertFalse($item->isRelationPopulated('group'));
    }
}