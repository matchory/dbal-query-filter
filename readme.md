# Doctrine DBAL query filter

Turn a uri query string into SQL search

Usage

```php
$conn = Doctrine\DBAL\DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => ':memory:']);
$query = $conn->createQueryBuilder()->from('tbl')->select('*');

parse_str('pid=1&cat[]=games&cat[]=books&updated_at_gte=today', $params);

$adapter = new Codin\DBAL\Adapters\DoctrineAdapter($query);
$builder = new Codin\DBAL\QueryBuilder($adapter);
$filter = $builder
    ->match('pid', 'product_id')
    ->range('updated_at')
    ->contains('cat', 'category')
;
$filter->build($params);

echo $query->getSQL()."\n";
// SELECT * FROM tbl
// WHERE (product_id = :product_id) AND (updated_at >= :updated_at_gte) AND (category in (:category0, :category1))
```
