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

### Index and search on a collection of associative arrays

```php
use NMarniesse\Phindexer\Collection\ArrayCollection;

$list = [
    ['id' => 1, 'name' => 'A', 'category' => 'enceinte', 'price' => 60],
    ['id' => 2, 'name' => 'B', 'category' => 'enceinte', 'price' => 80],
    ['id' => 3, 'name' => 'C', 'category' => 'ampli', 'price' => 10],
    ['id' => 4, 'name' => 'D', 'category' => 'enceinte', 'price' => 40],
    ['id' => 5, 'name' => 'E', 'category' => null, 'price' => 50],
];

$collection = new ArrayCollection($list);

$collection->addColumnIndex('category');
$results = $collection->findWhere('category', 'enceinte');

// Results is a new instance of ArrayCollection that contains the results
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
    return $item['category'] === 'enceinte' && $item['price'] <= 50;
});

// Add the index in your collection
$collection->addExpressionIndex($expression_index);

// Search using the index
$results = $collection->findWhereExpression($expression_index, true);

// Search the items that do not satisfy the expression index
$results = $collection->findWhereExpression($expression_index, false);
```


### Using collection of objects

As the `ArrayCollection` is a collection of array, the `ObjectCollection` handles collection of... objects.  
Instead of key index, you can index properties. Public properties and protected/private property if the getter function
is available.

```php
use NMarniesse\Phindexer\Collection\ObjectCollection;
use NMarniesse\Phindexer\IndexType\ExpressionIndex;

// $list can be an array or an iterator
$list = [
   new Planet('Earth', 'Solar system'),
   new Planet('Mars', 'Solar system'),
   new Planet('Kepler 186-f', 'Kepler 186 system'),
];

$collection = new ObjectCollection($list);

// Index with property
$collection->addPropertyIndex('system');
$results = $collection->findWhere('system', 'Solar system');

// Index with custom expression
$expression_index = new ExpressionIndex(function (Planet $planet) {
    return $planet->getSystem() === 'Solar system';
});
$collection->addExpressionIndex($expression_index);
$results = $collection->findWhereExpression($expression_index, true);
```


### Add items in a collection of array/object

Once the collection is initialize, you can add items in it. You can even create an empty collection
in one hand and add items in other hand. Added items are of course indexed the same way.   

Here is an example with `ObjectCollection`, the same behavior exists with `ArrayCollection`:

```php
use NMarniesse\Phindexer\Collection\ObjectCollection;

$collection = new ObjectCollection([]);
$collection->addPropertyIndex('system');

$collection->addItem(new Planet('Earth', 'Solar system'));
$collection->addItem(new Planet('Mars', 'Solar system'));
$collection->addItem(new Planet('Kepler 186-f', 'Kepler 186 system'));

$results = $collection->findWhere('system', 'Solar system');
```


### Validation constraint

If you need to ensure the items are always good, you can specified some constraints in your collection.  
The validation is checked on each items when the collection is instantiated, and on each item you add further.

```php
use NMarniesse\Phindexer\Collection\ObjectCollection;
use Symfony\Component\Validator\Constraints as Assert;

// Valiation ok.
$collection = new ObjectCollection([new Planet('Earth', 'Solar system')], new Assert\Type(['type' => Planet::class]));

// Valiation ok.
$collection->addItem(new Planet('Mars', 'Solar system'));
$collection->addItem(new Planet('Kepler 186-f', 'Kepler 186 system'));

// Valiation KO.
$collection->addItem(new Star('Sun'));
```
