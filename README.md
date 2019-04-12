# Phindexer

PHP data indexer that speeds up your data operations.  

When you need to filter or search item from a collection, the operation could take time and effort to
retrieve your data. And unless you index your data manually (with a group by strategy for example) the 
operation could slow down your program drastically.  

This projects helps you to index your data and retrieve them simply and quickly.  


## Requirements

- php 7.1


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


## Usage

### Index and search on columns

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

// Results is an new instance of ArrayCollection that contains the results
foreach ($results as $result) {
    print_r($result);
}

// Print rows with ids 1, 2 and 3.

```
