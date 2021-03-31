<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

$conn = Doctrine\DBAL\DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => ':memory:']);
$query = $conn->createQueryBuilder()->from('tbl')->select('*');

parse_str('package_id=1&category[]=payment&is_credit=1&amount_gte=2000&status[]=paid', $params);

$filter = (new Codin\DBAL\QueryFilter())
    ->match('package_id')
    ->match('invoice_id')
    ->match('created_by')

    ->range('amount')
    ->range('created_at')

    ->nullable('is_credit', 'received_from')
    ->nullable('is_debit', 'sent_to')

    ->contains('category')
    ->contains('keys', 'id')

    ->callback('oub_ref', function ($query, $params) {
        $query->andWhere('package_id in (select account_id from accounts where oub_ref = :oub_ref)')
            ->setParameter('oub_ref', $params['oub_ref'])
        ;
    })

    ->callback('paid_at_gte', function ($query, $params) {
        $query->andWhere('id in (select billing_transfer_id from billing_transfer_events where created_at >= :paid_at_gte and status = :status)')
            ->setParameter('paid_at_gte', $params['paid_at_gte'])
            ->setParameter('status', 'paid')
        ;
    })

    ->callback('paid_at_lte', function ($query, $params) {
        $query->andWhere('id in (select billing_transfer_id from billing_transfer_events where created_at <= :paid_at_lte and status = :status)')
            ->setParameter('paid_at_lte', $params['paid_at_lte'])
            ->setParameter('status', 'paid')
        ;
    })

    ->callback('status', function ($query, $params) {
        $bindings = implode(', ', $this->bindParams($query, 'status', $params['status']));
        $query->andWhere(sprintf('id in (
            select billing_transfer_id
            from billing_transfer_events
            where id in (select max(id) from billing_transfer_events group by billing_transfer_id)
            and status in (%s)
        )', $bindings));
    })
;

$filter->build($query, $params);

var_dump($query->getSQL(), $query->getParameters());
