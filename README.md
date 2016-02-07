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
Imagine we have the following database structure:

```sql
CREATE TABLE `Group`
(
   `id` integer NOT NULL AUTO_INCREMENT,
   `name` varchar(64) NOT NULL,
   `code` varchar(10) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE InnoDB;

CREATE TABLE `Item`
(
   `id` integer NOT NULL AUTO_INCREMENT,
   `groupId` integer NOT NULL,
   `name` varchar(64) NOT NULL,
   `price` float,
    PRIMARY KEY (`id`)
    FOREIGN KEY (`groupId`) REFERENCES `Group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
) ENGINE InnoDB;
```

If you need to display listing of items with the groups they belong to ordered by group name or code,
you'll have to `JOIN` SQL statement and thus - `\yii\db\ActiveQuery::joinWith()` method:

```php
$items = Item::find()
    ->joinWith('group')
    ->orderBy(['{{group}}.[[name]]' => SORT_ASC])
    ->all();
```

However the code above will perform 2 SQL queries: one - for the item fetching (including `JOIN` and
`ORDER BY` statements) and second - for the group fetching. While second query will be very simple and fast,
it is still redundant and unefficient, since all group columns may be selected along with the item ones.

This extension provides [[yii2tech\ar\eagerjoin\EagerJoinBehavior]] behavior, which once attached to the
ActiveRecord class allows selecting related records without extra SQL query.

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
                'boundary' => '__',
            ],
        ];
    }

    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'groupId']);
    }
}
```

In order to populate related record though 'join' query, you'll need to manually append its columns
into the `SELECT` query section and alias them by names in following format:

```
{relationName}{boundary}{columnName}
```

where:

 - 'relationName' - name of the relation to be populated
 - 'columnName' - name of the column(attribute) of the related record to be filled
 - 'boundary' - separator configured by [[yii2tech\ar\eagerjoin\EagerJoinBehavior::boundary]]

For example:

```php
$items = Item::find()
    ->select(['{{item}}.*', '{{group}}.[[name]] AS group__name', '{{group}}.[[code]] AS group__code'])
    ->joinWith('group', false) // disable regular eager loading!!!
    ->all();

foreach ($items as $item) {
    var_dump($item->isRelationPopulated('group')); // outputs `true`!!!
    echo $item->group->name; // no extra query performed!
    echo $item->group->code; // no extra query performed!
    echo get_class($item->group); // outputs 'Group'!
}
```

Here 'group__name' column of the query result set is passed to `$item->group->name`, 'group__code' -
to `$item->group->code` and so on.

**Heads up!** Do not forget to disable eager loading, passing `false` as second argument of `joinWith()`
method, otherwise you'll gain no benefit.

> Note: choose `boundary` carefully: it should not be present as a part of the columns (or aliases), which
  are not meant to be passed to the related records. Thus double underscore ('__') is used as default.

> Tip: if you use 'camelCase' notation for your table columns, you may use single underscore ('_') as a
  boundary to make select statements more clear.

You may speed up composition of the query for the eager join using [[\yii2tech\ar\eagerjoin\EagerJoinQueryBehavior]] behavior.
This behavior should be attached to the [[\yii\db\ActiveQuery]] instance:

```php
use yii\db\ActiveQuery;
use yii2tech\ar\eagerjoin\EagerJoinQueryBehavior;
use yii\db\ActiveRecord;
use yii2tech\ar\eagerjoin\EagerJoinBehavior;

class ItemQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'eagerJoin' => [
                'class' => EagerJoinQueryBehavior::className(),
            ],
        ];
    }

    // ...
}

class Item extends ActiveRecord
{
    /**
     * @inheritdoc
     * @return ItemQuery|EagerJoinQueryBehavior the active query used by this AR class.
     */
    public static function find()
    {
        return new ItemQuery(get_called_class());
    }

    // ...
}
```

Then you''ll be able to use `eagerJoinWith()` method while building a query:

```php
$items = Item::find()->eagerJoinWith('group')->all();
```

Composition of the proper 'select' and 'join' statements will be performed automatically.


## Restrictions and drawbacks <span id="internationalization"></span>

While reducing the number of executed queries, this extension has several restrictions and drawbacks.

1) Only 'has-one' relations are supported. Extension is unable to handle 'has-many' relations.

2) If all selected related model fields will be `null` the whole related record will be set to `null`.
