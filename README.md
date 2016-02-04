ActiveRecord Eager Join Extension for Yii 2
===========================================

This extension provides support for ActiveRecord relation eager loading via join without extra query.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/yii2tech/ar-eagerjoin/v/stable.png)](https://packagist.org/packages/yii2tech/ar-eagerjoin)
[![Total Downloads](https://poser.pugx.org/yii2tech/ar-eagerjoin/downloads.png)](https://packagist.org/packages/yii2tech/ar-eagerjoin)
[![Build Status](https://travis-ci.org/yii2tech/ar-eagerjoin.svg?branch=master)](https://travis-ci.org/yii2tech/ar-eagerjoin)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2tech/ar-eagerjoin
```

or add

```json
"yii2tech/ar-eagerjoin": "*"
```

to the require section of your composer.json.


Usage
-----

This extension provides support for ActiveRecord relation eager loading via join without extra query.

Configuration example:

```php
use yii\db\ActiveRecord;
use yii2tech\ar\eagerjoin\EagerJoinBehavior;

class Item extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'eagerJoin' => [
                'class' => EagerJoinBehavior::className(),
            ],
        ];
    }

    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'groupId']);
    }
}
```

Usage example:

```php
$items = Item::find()
    ->select(['{{item}}.*', '{{group}}.[[name]] AS group__name', '{{group}}.[[code]] AS group__code'])
    ->joinWith('group', false) // no regular eager loading!!!
    ->all();

foreach ($items as $item) {
    var_dump($item->isRelationPopulated('group')); // outputs `true`!!!
    echo $item->group->name; // outputs group name
    echo $item->group->code; // outputs group code
}
```