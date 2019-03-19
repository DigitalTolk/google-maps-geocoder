<?php

namespace Jestillore\GoogleMapsGeocoder;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class Geocoder
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/';

    /**
     * @var array
     */
    private $components = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @param string $address
     * @return Geocoder
     */
    public function geocode($address)
    {
        $this->components = $this->fetch($address);
        return $this;
    }

    /**
     * @param array $results
     * @param string $key
     * @return array
     */
    private function getAddressComponents($results = [], $key = 'address_components')
    {
        $components = [];
        foreach ($results as $result) {
            if (is_array($result->$key)) {
                foreach ($result->$key as $component) {
                    $components[] = $component;
                }
            }
        }
        return $components;
    }

    /**
     * @param string $address
     * @param string $type
     * @return Collection
     */
    private function fetch($address, $type = 'json')
    {
        $url = $this->baseUrl . $type . '?';
        $url .= http_build_query([
            'address' => $address,
            'key' => $this->apiKey
        ]);
        $components = [];
        try {
            $response = $this->client->get($url);
            $response = json_decode($response->getBody()->getContents());
            if ($response->status === 'OK' && is_array($response->results)) {
                $components = $this->getAddressComponents($response->results);
            }
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            // TODO: do something
        } catch (\GuzzleHttp\Exception\ServerException $exception) {
            // TODO: do something
        }
        return Collection::make($components);
    }

    /**
     * Get component from response
     *
     * @param string $type
     * @param string $form
     * @return string|null
     */
    public function getComponent($type, $form = 'long_name')
    {
        foreach ($this->components as $component) {
            foreach ($component->types as $_type) {
                if ($type === $_type) {
                    return $component->$form;
                }
            }
        }
        return null;
    }

    /**
     * Get all components
     *
     * @return array
     */
    public function get()
    {
        return $this->components;
    }

    /**
     * Get first component
     *
     * @return array|null
     */
    public function first()
    {
        if (count($this->components) > 0) {
            return $this->components[0];
        }
        return null;
    }

    /**
     * @param string $apiKey
     * @return Geocoder
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @param Client $client
     * @return Geocoder
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = new Client();
        }
        return $this->client;
    }
}
