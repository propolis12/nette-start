<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AnimalApiClient
{

    private const API_URL = 'https://petstore3.swagger.io/api/v3/';
    private Client $client;
    private string $apiUrl;

    public function __construct(string $apiUrl)
    {
        $this->client = new Client();
    }

    public function getAllAnimalsByStatus(int $status): array
    {
        try {

            $query = http_build_query(['status' => $status]);
            $response = $this->client->get(sprintf('%s/pet/findByStatus?%s', $this->apiUrl, $query));
            $body = $response->getBody()->getContents();
            return json_decode($body, true);
        } catch (RequestException $e) {
            throw new \Exception("API request failed: " . $e->getMessage());
        }
    }

    public function getAnimalById(int $id): ?array
    {
        try {
            $response = $this->client->get($this->apiUrl . '/animals/' . $id);
            $body = $response->getBody()->getContents();
            return json_decode($body, true);
        } catch (RequestException $e) {
            throw new \Exception("API request failed: " . $e->getMessage());
        }
    }

    public function createAnimal(array $data): bool
    {
        try {
            $response = $this->client->post($this->apiUrl . '/animals', [
                'json' => $data
            ]);
            return $response->getStatusCode() === 201;
        } catch (RequestException $e) {
            throw new \Exception("API request failed: " . $e->getMessage());
        }
    }

    public function updateAnimal(int $id, array $data): bool
    {
        try {
            $response = $this->client->put($this->apiUrl . '/animals/' . $id, [
                'json' => $data
            ]);
            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            throw new \Exception("API request failed: " . $e->getMessage());
        }
    }

    public function deleteAnimal(int $id): bool
    {
        try {
            $response = $this->client->delete($this->apiUrl . '/animals/' . $id);
            return $response->getStatusCode() === 204;
        } catch (RequestException $e) {
            throw new \Exception("API request failed: " . $e->getMessage());
        }
    }
}
