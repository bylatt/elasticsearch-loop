# Loop'n an es index'

### Run tests
```
composer test
```

### Usage example
```php
use \Elasticsearch\ClientBuilder;
use \Clozed2u\Elasticsearch\ES16\Looper;

require __DIR__ . '/vendor/autoload.php';

$hosts = ['localhost:9200'];
$client_builder = new ClientBuilder();

$looper = new Looper();
$looper->createClient($hosts, $client_builder);

$looper->setSearchParams([
  'index' => 'twitter',
  'type' => 'tweet',
  'body' => [
    'query' => [
      'match_all' => []
    ]
  ]
]);

$looper->setCallback(function ($response) {
  foreach ($response['hits']['hits'] as $item) {
    echo $item['_id'], PHP_EOL;
  }
});

$looper->get();
```
