# Phindexer

PHP data indexer that speeds up your data operations.  

When you need to filter or search item from a collection, the operation could take time and effort to
retrieve your data. And unless you index your data manually (with a group by strategy for example) the 
operation could slow down your program drastically.  

This projects helps you to index your data and retrieve them simply and quickly.  


## Requirements

- php 7.1
- ext-json


## Install

Warning: the project is actually in dev status! So you have to add the repository manually:

```json
{
   ...
   "repositories": [
     {
       "type": "vcs",
       "url": "git@github.com:nmarniesse/phindexer.git"
     }
   ],
   "require": {
     ...
     "nmarniesse/phindexer": "dev-master"
   }
   ...
 }
```


## Documentation

### Index and search on a collection

```php
use NMarniesse\Phindexer\Collection\Collection;

$list = [
    ['name' => 'A', 'color' => 'green', 'price' => 60],
    ['name' => 'B', 'color' => 'green', 'price' => 80],
    ['name' => 'C', 'color' => 'blue', 'price' => 10],
    ['name' => 'D', 'color' => 'green', 'price' => 40],
    ['name' => 'E', 'color' => 'red', 'price' => 50],
];

$collection = new Collection($list);

$collection->addKeyIndex('color');
$results = $collection->findWhere('color', 'green');

// Results is a new instance of Collection that contains the results
foreach ($results as $result) {
    print_r($result);
}
```


### Using custom index

Maybe you want index your data with more complex condition. You can pass the function you want with
`ExpressionIndex`:

```php
// Create the custom index
$expression_index = new ExpressionIndex(function (array $item) {
    return $item['color'] === 'green' && $item['price'] <= 50;
});

// Add the index in your collection
$collection->addExpressionIndex($expression_index);

// Search using the index
$results = $collection->findWhereExpression($expression_index, true);

// Search the items that do not satisfy the expression index
$results = $collection->findWhereExpression($expression_index, false);
```


### Using collection of objects


The `Collection` class can also contains a set of objects. You can even mix objects and associative arrays.  
When you index by key, the system tries to index the property. It can be a public properties or protected/private
property if the getter function is available.  

```php
use NMarniesse\Phindexer\Collection\Collection;
use NMarniesse\Phindexer\IndexType\ExpressionIndex;

// $list can be an array or an iterator
$list = [
   new Planet('Earth', 'Solar system'),
   new Planet('Mars', 'Solar system'),
   new Planet('Kepler 186-f', 'Kepler 186 system'),
];

$collection = new Collection($list);

// Index with property
$collection->addKeyIndex('system');
$results = $collection->findWhere('system', 'Solar system');

// Index with custom expression
$expression_index = new ExpressionIndex(function (Planet $planet) {
    return strpos(strtolower($planet->getSystem()), 'solar') !== false;
});
$collection->addExpressionIndex($expression_index);
$results = $collection->findWhereExpression($expression_index, true);
```


### Add items in a collection

Once the collection is initialize, you can add items in it. You can even create an empty collection
in one hand and add items in other hand. Added items are indexed the same way.   

Here is an example with `Collection`, the same behavior exists with `Collection`:

```php
use NMarniesse\Phindexer\Collection\Collection;

$collection = new Collection([]);
$collection->addKeyIndex('system');

$collection->addItem(new Planet('Earth', 'Solar system'));
$collection->addItem(new Planet('Mars', 'Solar system'));
$collection->addItem(new Planet('Kepler 186-f', 'Kepler 186 system'));

$results = $collection->findWhere('system', 'Solar system');
```


### Validation constraint

If you need to ensure the items have always the good structure, or the same type, you can specified some constraints
in your collection.  
The validation is checked on each items when the collection is instantiated, and on each item you add further.

```php
use NMarniesse\Phindexer\Collection\Collection;
use Symfony\Component\Validator\Constraints as Assert;

// Valiation ok.
$constraint = new Assert\Type(['type' => Planet::class]);
$collection = new Collection([new Planet('Earth', 'Solar system')], $constraint);

// Valiation ok.
$collection->addItem(new Planet('Mars', 'Solar system'));
$collection->addItem(new Planet('Kepler 186-f', 'Kepler 186 system'));

// Valiation fails.
$collection->addItem(new Star('Sun'));
```
