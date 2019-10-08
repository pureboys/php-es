<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = Elasticsearch\ClientBuilder::fromConfig([
    'hosts' => ['localhost:9200'],
    'retries' => 2,
]);

// 创建商铺type
$indices = $client->indices();
// 删除旧的basic索引
$indices->delete(['index' => 'merchant']);

// 创建basic索引同时执行商铺的type mapping
$indices->create([
    'index' => 'merchant',
    'body' => [
        // index配置
        'settings' => [
            'number_of_shards' => 3, // 3个分区
            'number_of_replicas' => 2, // 每个分区有1个主分片和2个从分片
        ],
        // type映射
        'mappings' => [
            'doc' => [
                // 属性
                'properties' => [
                    // 商铺名称
                    'merchant_name' => [
                        'type' => 'string', // 字符串
                        'index' => 'analyzed', // 全文索引
                        //'analyzer' => 'ik_max_word', // 中文分词
                    ],
                    // 商铺图片
                    'merchant_img' => [
                        'type' => 'string', // 字符串
                        'index' => 'no', // 不索引
                    ],
                    // 商铺类型
                    'merchant_type' => [
                        'type' => 'string', // 字符串
                        'index' => 'not_analyzed', // 不分词,直接索引
                    ],
                    // 用户评分
                    'merchant_score' => [
                        'type' => 'integer', // 整形
                        'index' => 'not_analyzed', // 直接索引,用于过滤/排序
                    ],
                    // 人均价格
                    'merchant_avg_price' => [
                        'type' => 'integer', // 整形
                        'index' => 'not_analyzed', // 直接索引,用于过滤/排序
                    ],
                    // 地理坐标
                    'merchant_location' => [
                        'type' => 'geo_point', // 地址坐标
                    ],
                    'merchant_product' => [
                        'type' => 'nested',
                        'properties' => [
                            // 商品ID
                            'product_id' => [
                                'type' => 'long', // 长整形
                                'index' => 'not_analyzed', // 不分词,直接索引
                            ],
                            // 商品名称
                            'product_name' => [
                                'type' => 'string', // 字符串
                                'index' => 'analyzed', // 全文索引
                               // 'analyzer' => 'ik_max_word', // 中文分词
                            ],
                            // 商品图片
                            'product_img' => [
                                'type' => 'string', // 字符串
                                'index' => 'no', // 不索引
                            ],
                            // 商品类型
                            'product_type' => [
                                'type' => 'string', // 字符串
                                'index' => 'not_analyzed', // 不分词,直接索引
                            ],
                            // 商品价格
                            'product_price' => [
                                'type' => 'integer', // 整形
                                'index' => 'not_analyzed', // 直接索引,用于过滤/排序
                            ],
                            // 商品销量
                            'product_sold' => [
                                'type' => 'integer', // 整形
                                'index' => 'not_analyzed', // 直接索引,用于排序/过滤
                            ]
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

// 商品列表作为一个属性存储在商铺中（type=nested，嵌套的），一个商铺有多个商品
// curl localhost:9200/basic?pretty 查看mapping






