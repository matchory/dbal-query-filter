<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

use Codin\DBAL\Adapters\EloquentAdapter;
use Codin\DBAL\QueryBuilder;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as Query;

$conn = new Connection(new PDO('sqlite::memory'));
$query = $conn->query()->from('tbl')->select('*');
$eloquent = new Builder($query);

parse_str('package_id=1&category[]=payment&is_credit=1&amount_gte=2000&status[]=paid', $params);

$adapter = new EloquentAdapter($eloquent);
$builder = new QueryBuilder($adapter);

$sql = $builder
    ->match('package_id')
    ->match('invoice_id')
    ->match('created_by')
    ->range('amount')
    ->range('created_at')
    ->nullable('is_credit', 'received_from')
    ->nullable('is_debit', 'sent_to')
    ->contains('category')
    ->contains('keys', 'id')
    ->callback('oub_ref', function (Query $query, array $params) {
        $query->whereIn('package_id', function (Query $query) use ($params) {
            $query->select('account_id')
                  ->from('accounts')
                  ->where('oub_ref', $params['oub_ref']);
        });
    })
    ->callback('paid_at_gte', function (Query $query, array $params) {
        $query->whereIn('id', function (Query $query) use ($params) {
            $query->select('billing_transfer_id')
                  ->from('billing_transfer_events')
                  ->where('created_at', '>=', $params['paid_at_gte'])
                  ->where('status', $params['status']);
        });
    })
    ->callback('paid_at_lte', function (Query $query, array $params) {
        $query->whereIn('id', function (Query $query) use ($params) {
            $query->select('billing_transfer_id')
                  ->from('billing_transfer_events')
                  ->where('created_at', '<=', $params['paid_at_lte'])
                  ->where('status', 'paid');
        });
    })
    ->callback('status', function (Builder $query, array $params) {
        $query->whereIn('id', function (Query $query) use ($params) {
            $query->select('billing_transfer_id')
                  ->from('billing_transfer_events')
                  ->whereIn('id', function (Query $query) {
                      $query->selectRaw('max(id)')
                            ->from('billing_transfer_events')
                            ->groupBy('billing_transfer_id');
                  })
                  ->whereIn('status', $params['status']);
        });
    })
    ->build($params)
    ->toSql();

var_dump($sql);
