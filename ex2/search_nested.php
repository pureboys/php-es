<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = Elasticsearch\ClientBuilder::fromConfig([
    'hosts'   => ['localhost:9200'],
    'retries' => 2,
]);

//因为商铺和商品是嵌套关系，所以在查询时需要使用”嵌套查询”语法。
//
//我的查找需求表达如下：
//
//若某商铺的名称或者其售卖的”商品”的名称，能够匹配”搜索关键字”，那么返回该商铺的信息。

// 嵌套查询

$keyword = "东方宫拉面";
$result = $client->search([
    'index' => 'merchant',
    'type'  => 'doc',
    'search_type' => 'dfs_query_then_fetch',  // 汇总IDF计算相关 <- 解决下面返回不准的问题 ！！！！！
    'body'  => [
        'query' => [
            // 查询请求,影响相关性打分
            'bool' => [ // 布尔组合
                'should' => [ // 各个子句相当于或的关系
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

//分析一下这个查询的组成部分（注意配合代码注释理解）：
//
//query：查询语句必须放在其内部。
//bool：组合查询，可以表达多个子句之间的AND（must），OR（should），NOT（must_not）关系。
//should：OR的意思，里面多个子句满足任意一个即匹配，这个例子有2个子句。
//should的总相关性是这样计算的：所有子句的相关性和/子句的数量。
//match：全文匹配，会对$keyword分词，然后分别进行倒排查找。
//该查询是should的第一个子句，会匹配得到一个相关性。
//nested：嵌套查询，是should的第二个子句。
//path：嵌套查询指向的子文档路径。
//score_mode：嵌套文档有多个，该参数指定nested子句的总相关性是如何计算的。
//这里指定max，表示取多个商品中的最大相关性。
//query：对于嵌套查询来说，查询语句必须放在其内部。
//match：全文匹配，会对$keyword分词，然后分别进行倒排查找。
//该查询是nested query的唯一子句，产生的相关性是nested的总相关性，也是should第二个子句的相关性。


/*
  直观来看，”东方宫兰州拉面”应该更符合我的预期，为什么”鑫明明拉面”的相关性却高于”东方宫兰州拉面”呢？
  出现这个现象的原因是因为不准确的IDF！
  ES在计算IDF的时候是基于分片内的数据统计的，分片1内的”拉面”只出现在”东方宫兰州拉面”内，相当于100%的IDF（在所有文档内出现）；分片2内的”拉面”只出现在”鑫明明拉面”内，而”开海饭店”里并没有出现，相当于50%的IDF（在1/2的文档内出现），讲到这里我们就明白了：”东方宫兰州拉面”和”鑫明明拉面”中”拉面”都出现了1次，但是前者的IDF是1，而后者是1/2，经过TF/IDF计算显然是后者的值更大，也就是更相关了！

    这个问题在数据规模较大的情况下可以忽略，在我们开发阶段可以通过指定一个参数解决：search_type=dfs_query_then_fetch，它将获取集群所有分片的IDF和之后再计算TF/IDF，因此更加准确。

 */


