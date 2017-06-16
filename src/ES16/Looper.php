<?php

namespace Clozed2u\Elasticsearch\ES16;

use \Exception;

class Looper
{
  public $client;
  public $search_params;
  public $callback;

  public function createClient($hosts, $client_builder)
  {
    $this->client = $client_builder->setHosts($hosts)->build();
    return $this;
  }

  public function setSearchParams($params)
  {
    if (is_array($params)) {
      $params['search_type'] = isset($params['search_type']) ? $params['search_type'] : 'scan';
      $params['scroll'] = isset($params['scroll']) ? $params['scroll'] : '5m';
      $params['size'] = isset($params['size']) ? $params['size'] : 1000;
      if (!isset($params['index'])) {
        throw new Exception('Index key require in params');
      }
      $this->search_params = $params;
      return $this;
    } else {
      throw new Exception('Search params must be an array');
    }
  }

  public function setCallback($callback)
  {
    if (is_callable($callback)) {
      $this->callback = $callback;
      return $this;
    } else {
      throw new Exception('Callback must be a function');
    }
  }

  public function get()
  {
    if (is_null($this->client) || is_null($this->search_params) || is_null($this->callback)) {
      throw new Exception('Client, search_params and callback must be set before calling get');
    } else {
      $response = $this->client->search($this->search_params);
      $scroll_id = $response['_scroll_id'];

      while (true) {
        $docs = $this->client->scroll([
          'scroll_id' => $scroll_id,
          'scroll' => '5m'
        ]);

        if (count($docs['hits']['hits']) > 0) {
          ($this->callback)($docs);
          $scroll_id = $docs['_scroll_id'];
        } else {
          return true;
        }
      }
    }
  }
}
