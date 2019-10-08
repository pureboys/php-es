<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = Elasticsearch\ClientBuilder::fromConfig([
    'hosts'   => ['localhost:9200'],
    'retries' => 2,
]);


$keyword = "兰拉面";
$result = $client->search([
    'index' => 'merchant',
    'type'  => 'doc',
    'search_type' => 'dfs_query_then_fetch',  // 汇总IDF计算相关 <- 解决下面返回不准的问题 ！！！！！
    'body'  => [
        'query' => [
            // 查询请求,影响相关性打分
            'dis_max' => [ // 布尔组合
                'queries' => [ // 各个子句相当于或的关系
                    // 第一项
                    [
                        // 全文匹配
                        'match' => ['merchant_name' => $keyword]
                    ],
                    // 第二项
                    [
                        // 嵌套
                        'nested' => [
                            'path'       => 'merchant_product', // 子文档的路径
                            'score_mode' => 'max', // 子文档的评分方式(max表示取最多个子文档中最匹配的那个的相关性)
                            'query'      => [ // 子文档查询请求，影响相关性打分
                                'match' => [ // 全文匹配
                                    'merchant_product.product_name' => $keyword // 商品名（必须全路径）
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

print_r($result);





