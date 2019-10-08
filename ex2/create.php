<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = Elasticsearch\ClientBuilder::fromConfig([
    'hosts'   => ['localhost:9200'],
    'retries' => 2,
]);

// 批量插入测试数据
$client->bulk([
    'index' => 'merchant',
    'type'  => 'doc',
    'body'  => [
        // index索引请求，元信息是['_id':1]
        [
            'index' => ['_id' => 1],
        ],
        // 请求体
        [
            // 主文档
            'merchant_name'      => '鑫明明拉面',
            'merchant_score'     => 4,
            'merchant_type'      => '美食',
            'merchant_img'       => 'http://merchant.com/1.jpg',
            'merchant_avg_price' => 2100,
            'merchant_location'  => [120.3945890000, 36.0705170000],
            'merchant_product'   => [ // 嵌套文档列表
                [
                    'product_id'    => 1,
                    'product_name'  => '羊肉烩面',
                    'product_type'  => '面食',
                    'product_img'   => 'http://product.com/2.jpg',
                    'product_sold'  => 11,
                    'product_price' => 2200,
                ],
                [
                    'product_id'    => 2,
                    'product_name'  => '烤羊肉串',
                    'product_type'  => '烤串',
                    'product_img'   => 'http://product.com/3.jpg',
                    'product_sold'  => 12,
                    'product_price' => 2300,
                ],
            ]
        ],

        ['index' => ['_id' => 2]],
        [
            'merchant_name'      => '东方宫兰州拉面',
            'merchant_score'     => 3,
            'merchant_type'      => '美食',
            'merchant_img'       => 'http://merchant.com/2.jpg',
            'merchant_avg_price' => 1800,
            'merchant_location'  => [36.0693500000, 12.3928290000],
            'merchant_product'   => [
                [
                    'product_id'    => 3,
                    'product_name'  => '牛肉炒面',
                    'product_type'  => '面食',
                    'product_img'   => 'http://product.com/4.jpg',
                    'product_sold'  => 10,
                    'product_price' => 2400,
                ],
                [
                    'product_id'    => 4,
                    'product_name'  => '蛋炒饭',
                    'product_type'  => '主食',
                    'product_img'   => 'http://product.com/5.jpg',
                    'product_sold'  => 14,
                    'product_price' => 2300,
                ],
                [
                    'product_id'    => 5,
                    'product_name'  => '羊肉汤',
                    'product_type'  => '汤粉',
                    'product_img'   => 'http://product.com/6.jpg',
                    'product_sold'  => 10,
                    'product_price' => 2200,
                ],
            ]
        ],


        ['index' => ['_id' => 3]],
        [
            'merchant_name'      => '开海饭店',
            'merchant_score'     => 3,
            'merchant_type'      => '美食',
            'merchant_img'       => 'http://merchant.com/3.jpg',
            'merchant_avg_price' => 3500,
            'merchant_location'  => [120.4051170000, 36.0683000000],
            'merchant_product'   => [
                [
                    'product_id'    => 6,
                    'product_name'  => '海鲜炒饭',
                    'product_type'  => '主食',
                    'product_img'   => 'http://product.com/7.jpg',
                    'product_sold'  => 10,
                    'product_price' => 2400,
                ],
                [
                    'product_id'    => 7,
                    'product_name'  => '西红柿鸡蛋面',
                    'product_type'  => '面食',
                    'product_img'   => 'http://product.com/8.jpg',
                    'product_sold'  => 10,
                    'product_price' => 2300,
                ],
                [
                    'product_id'    => 8,
                    'product_name'  => '鸭血粉丝汤',
                    'product_type'  => '汤粉',
                    'product_img'   => 'http://product.com/9.jpg',
                    'product_sold'  => 10,
                    'product_price' => 2200,
                ],
                [
                    'product_id'    => 9,
                    'product_name'  => '兰州炒饭',
                    'product_type'  => '主食',
                    'product_img'   => 'http://product.com/10.jpg',
                    'product_sold'  => 15,
                    'product_price' => 2500,
                ],
            ]
        ]


    ],
]);

// 商铺ID作为ES文档的唯一_id。
// 商品ID作为普通字段保存在嵌套文档的product_id字段。
