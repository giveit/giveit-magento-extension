<?php

namespace GiveIt\SDK;

class Collection
{
    public      $type;

    private     $client;
    private     $data;
    private     $pages;

    public function __construct($type)
    {
        $this->type = $type;

        $this->setupClient();

        $this->pages = (object) array(
            'size'      => 10,
            'current'   => 1,
        );

        // print_r($this); exit;
    }

    private function setupClient()
    {
        $this->client = Client::getInstance();
    }

    private function getBaseURL()
    {
        return '/' . strtolower($this->type) . 's';
    }

    public function setLimit($limit)
    {
        $this->pages->size = $limit;
    }

    public function addFilter($name, $value)
    {
        $this->filters[] = "$name=$value";
    }

    public function nextPage()
    {
        if (! $this->pages->next) {
            return false;
        }

        $options = array(
            'page'      => $this->pages->next,
        );

        $url            = $this->buildURL($options);
        $response       = $this->client->sendGET($url);

        return $this->parseCollectionResponse($response);

    }

    public function previousPage()
    {
        $options = array(
            'page'      => $this->pages->previous,
        );

        $url            = $this->buildURL($options);
        $response       = $this->client->sendGET($url);

        return $this->parseCollectionResponse($response);

    }

    public function buildURL($overrideOptions = array())
    {
        $url  = $this->getBaseURL();

        $options = array(
            'limit'     => $this->pages->size,
            'page'      => $this->pages->current,
        );

        foreach ($overrideOptions as $key => $val) {
            if (isset($options[$key])) {
                $options[$key] = $val;
            }
        }

        $url .= '?' . http_build_query($options);

        return $url;
    }

    // TODO: check response in case of error
    private function parseCollectionResponse($response)
    {
        if (! is_object($response)) {
            return false;
        }

        $this->pages    = $response->pages;
        $this->data     = $response->data;

        return $this->data;
    }

    public function all()
    {
        $url            = $this->buildURL();
        $response       = $this->client->sendGET($url);

        return $this->parseCollectionResponse($response);
    }

    public function since($date)
    {

        if ($date == 'yesterday') {
            $date = date('Y-m-d', strtotime('yesterday'));
        }

        $url = $this->getBaseURL() . "?filter:created_at=]$date";

        $collection = $this->client->sendGET($url);

        return $collection;

    }

    public function get($id)
    {
        $url = $this->getBaseURL() . "/$id";

        $response = $this->client->sendGET($url);

        // TODO: error checking - valid response? no errors?

        $class = '\GiveIt\SDK\\' . $this->type;

        $object = new $class($response);

        return $object;
    }
}
