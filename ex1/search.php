<?php

require_once __DIR__ . '/vendor/autoload.php';

$hosts = [
    'afilter_es-search-01:9200',
    'afilter_es-search-02:9200',
    'afilter_es-search-03:9200'
];

// 创建官方客户端
$client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

// 创建搜索体
$search = new \ONGR\ElasticsearchDSL\Search();

// bool子查询
$boolQuery = new \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery();

// 增加bool -> must 子句
$boolQuery->add(
  new \ONGR\ElasticsearchDSL\Query\FullText\MatchPhraseQuery("article_title", "西装"), \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::MUST
);

// 增加bool -> filter 子句
$boolQuery->add(
    new \ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery(
        "publish_time",
        [
            \ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery::LTE => time()
        ]
    ),
    \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::FILTER
);

// 增加bool -> must_not 子句
$boolQuery -> add(
    new \ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery(
        'article_type', ['食品','家居']
    ),
    \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::MUST_NOT
);

// 希望返回的文章，标题必须包含”西装”，发布时间必须小于当前时间，文章类型不允许是”食品”和”家居”。

// bool 查询添加到 search 上
$search->addQuery($boolQuery);

// 设置翻页
$search->setFrom(0);
$search->setSize(10);

// 增加一个排序规则 发布时间倒排
$search->addSort(new \ONGR\ElasticsearchDSL\Sort\FieldSort('publish_time', 'desc', ['missing' => 0]));

// 希望对上述查询结果顺便做一个聚合+统计
//聚合方法是使用bucket agg，它的类型是filters agg。
//
//生成2个桶，匿名发布的文章anony_articles和实名发布的文章no_anony_articles

$filterAgg = new \ONGR\ElasticsearchDSL\Aggregation\Bucketing\FiltersAggregation('anonymous_bucketing',[
    'anony_articles' => new \ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery("is_anonymous",[1])
]);

$filterAgg->setParameters([
    'other_bucket_key' => 'no_anony_articles'
]);

//这个agg的规则名（自己定义）叫做anonymous_bucketing，然后is_anonymous=1的文章被划入anony_articles桶。
//
//额外的给这个agg设置一个参数叫做other_bucket_key，它的意思是没有被划入任何桶的文章放入no_anony_articles这个桶。

//文章被分入2个桶后，现在希望对每个桶再做统计，希望每个桶内只保留前10篇文章，排序规则是按照发布时间倒排。

// 11, 对每个桶执行top hits metrics agg
$metricsAgg = new \ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation(
    'latest_articles',
    10,
    0,
    new \ONGR\ElasticsearchDSL\Sort\FieldSort('publish_time', 'desc')
);

// top his agg返回的文档只包含article_title字段
$metricsAgg->addParameter('_source', ['article_title']);

// metrics agg添加到bucket agg下面
$filterAgg->addAggregation($metricsAgg);

//这里创建了一个metrics agg，它的类型是top hits agg。
//
//它对桶内文章按照publish_time倒排，然后保留从偏移量0开始的10篇文章在桶内；额外的，希望桶内返回的10篇文档只包含article_title字段，因为对其他字段不感兴趣。
//
//因为metrics agg是对桶内进行的统计操作，所以嵌入到bucekt agg内部，也就是先分桶后统计。


// 13, agg添加到search
$search->addAggregation($filterAgg);

//最后将search对象序列化一个查询体，提交到ES即可：

$body = $search->toArray();

$request = [
    'index' => 'article_oliver',
    'type' => 'doc',
    'body' => $body,
];

$response = $client->search($request);

echo json_encode($request) . PHP_EOL;
echo PHP_EOL;
echo PHP_EOL;
echo PHP_EOL;
echo json_encode($response) . PHP_EOL;


//应答中hits对应请求中query部分的查询结果，aggregations对应请求中aggregations部分的聚合统计结果。
//
//可以看出通过filters bucket agg聚合出了2个桶分别叫做anony_articles和no_anony_articles，每个桶内通过top hits metrics agg保留了最近发布的最多10篇文章（我数据库里一共就2条记录），并且只有article_title字段被保留。






