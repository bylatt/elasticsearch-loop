<?php

use \PHPUnit\Framework\TestCase;
use \Clozed2u\Elasticsearch\ES16\Looper;
use \Elasticsearch\Client as ESClient;
use \Elasticsearch\ClientBuilder as ESClientBuilder;

class LooperTest extends TestCase
{
  public function testAlwaysPass()
  {
    $this->assertTrue(true);
  }

  public function testLooperIsInstanceOfLooper()
  {
    $looper = new Looper();
    $this->assertInstanceOf(Looper::class, $looper);
  }

  public function testLooperCanCreateConnectionCorrectly()
  {
    $client_builder = new ESClientBuilder();
    $looper = new Looper();
    $hosts = ['localhost:9200'];
    $looper->createClient($hosts, $client_builder);
    $this->assertInstanceOf(ESClient::class, $looper->client);
  }

  public function testLooperCanSetSearchParamsCorrectly()
  {
    $looper = new Looper();
    $search_params = [
      'index' => 'instagram',
      'type' => 'instagram',
      'body' => [
        'query' => [
          'match_all' => []
        ]
      ],
      'search_type' => 'scan',
      'scroll' => '5m',
      'size' => 1000
    ];
    $looper->setSearchParams($search_params);
    $this->assertEquals($looper->search_params, $search_params);
  }

  /**
   * @expectedException Exception
   */
  public function testLooperMustThrowExceptionWhenSetSearchParamsWithInvalidType()
  {
    $looper = new Looper();
    $params = 'instagram';
    $looper->setSearchParams($params);
  }

  public function testLooperCanSetCallbackCorrectly()
  {
    $looper = new Looper();
    $callback = function ($result) {
      foreach ($result['hits']['hits'] as $item) {
        var_dump($item);
      }
    };

    $looper->setCallback($callback);
    $this->assertTrue(is_callable($looper->callback));
  }

  /**
   * @expectedException Exception
   */
  public function testLooperMustThrowExceptionWhenSetCallbackWithInvalidType()
  {
    $looper = new Looper();
    $callback = "callback";
    $looper->setCallback($callback);
  }

  public function testLooperCanGetMessageCorrectly()
  {
    $client = $this->createMock(ESClient::class);
    $client->method('search')->willReturn(['_scroll_id' => 1234]);
    $client->method('scroll')->willReturn(['_scroll_id' => 5678, 'hits' => ['hits' => []]]);

    $client_builder = $this->createMock(ESClientBuilder::class);
    $client_builder->method('setHosts')->will($this->returnSelf());
    $client_builder->method('build')->willReturn($client);

    $looper = new Looper();
    $looper->createClient(['localhost:9200'], $client_builder);
    $looper->setSearchParams([
      'index' => 'instagram',
      'type' => 'instagram',
      'body' => [
        'query' => [
          'match_all' => []
        ]
      ],
      'search_type' => 'scan',
      'scroll' => '5m',
      'size' => 1000
    ]);
    $looper->setCallback(function ($response) { var_dump($response); });
    $this->assertTrue($looper->get());
  }

  /**
   * @expectedException Exception
   */
  public function testCallingGetWithUncompleteSetting()
  {
    $looper = new Looper();
    $looper->get();
  }
}
