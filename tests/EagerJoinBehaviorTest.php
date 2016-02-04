<?php

namespace yii2tech\tests\unit\ar\eagerjoin;

use yii2tech\tests\unit\ar\eagerjoin\data\Group;
use yii2tech\tests\unit\ar\eagerjoin\data\Item;

class EagerJoinBehaviorTest extends TestCase
{
    public function testFoo()
    {
        $item = Item::find()
            ->addSelect(['{{group}}.code'])
            ->joinWith('group', false)
            ->one();

        $this->assertTrue($item->isRelationPopulated('group'));
        $this->assertTrue($item->group instanceof Group);
        $this->assertNotEmpty($item->group->code);
    }
}