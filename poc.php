<?php


class ExpressionIndex
{
	private $callable;

	private $fingerprint;

	public function __construct(callable $callable)
	{
		$this->callable = $callable;
		$this->fingerprint = uniqid('', true);
	}

	public function getFingerprint(): string
	{
		return $this->fingerprint;
	}

	public function getResult($row)
	{
		return call_user_func_array($this->callable, [$row]);
	}
}

class IndexStorage
{
	private $indexed = [];

	public function __construct(Collection $collection, ExpressionIndex $expression)
	{
		foreach ($collection->getArray() as &$row) {
			$value = (string) $expression->getResult($row);

			if (!array_key_exists($value, $this->indexed)) {
				$this->indexed[$value] = [];
			}

			$this->indexed[$value][] = $row;
		}
	}

	public function getResults($value): array
	{
		return $this->indexed[(string) $value] ?? [];
	}
}

class Collection
{
	private $array;

	private $index_storages = [];

	private $column_fingerprints = [];

	public function __construct(array $array)
	{
		$this->array = $array;
	}

	public function getArray(): array
	{
		return $this->array;
	}

	public function addItem()
	{
		// @todo
	}

	public function addColumnIndex(string $column): self
	{
		$expression = new ExpressionIndex(function ($row) use ($column) {
			return $row[$column];
		});

		$this->column_fingerprints[$column] = $expression->getFingerprint();

		return $this->addExpressionIndex($expression);
	}

	public function findWhere(string $column, string $value): Collection
	{
		$fingerprint = $this->column_fingerprints[$column];

		return new Collection($this->index_storages[$fingerprint]->getResults($value));
	}

	public function addColumnUniqueIndex(string $column): self
	{
		// @todo: unique index storage

		return $this;
	}

	public function addExpressionIndex(ExpressionIndex $expression): self
	{
		$this->index_storages[$expression->getFingerprint()] = new IndexStorage($this, $expression);

		return $this;
	}

	public function findWhereExpression(ExpressionIndex $expression, string $value): Collection
	{
		return new Collection($this->index_storages[$expression->getFingerprint()]->getResults($value));
	}
}



$array = [
	['name' => 'A', 'category' => 'enceinte', 'price' => 60],
	['name' => 'B', 'category' => 'enceinte', 'price' => 80],
	['name' => 'C', 'category' => 'ampli', 'price' => 10],
	['name' => 'D', 'category' => 'enceinte', 'price' => 40],
];

$collection = new Collection($array);

$collection->addColumnIndex('category');

print_r("Liste des enceintes :\n");
print_r($collection->findWhere('category', 'enceinte')->getArray());

print_r("Liste des amplis :\n");
print_r($collection->findWhere('category', 'ampli')->getArray());

print_r("Liste des casques (vide) :\n");
print_r($collection->findWhere('category', 'casque')->getArray());

print_r("Liste des enceintes dont le prix est supérieur à 50 :\n");
$expression = new ExpressionIndex(function ($row) {
	return $row['price'] > 50 && $row['category'] = 'enceintes';
});
$collection->addExpressionIndex($expression);
print_r($collection2 = $collection->findWhereExpression($expression, true)->getArray());

