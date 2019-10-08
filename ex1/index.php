<?php

require_once __DIR__ . '/../vendor/autoload.php';

$hosts = [
    'afilter_es-search-01:9200',
    'afilter_es-search-02:9200',
    'afilter_es-search-03:9200'
];

$client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();


$request = [
    'index' => 'article_oliver',
    'type'  => 'doc',
    'body'  => [
        'article_title' => '只卖88元的高级西装',
        'publish_time'  => time(),
        'article_type'  => '西装',
        'is_anonymous'  => 1,
    ]
];

$client->index($request);

$request = [
    'index' => 'article_oliver',
    'type'  => 'doc',
    'body'  => [
        'article_title' => '只卖2000元的垃圾西装',
        'publish_time'  => time(),
        'article_type'  => '高级西装',
        'is_anonymous'  => 0,
    ]
];

$client->index($request);