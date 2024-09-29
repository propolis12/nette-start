<?php

namespace App\Services;

use App\Entity\Animal;
use App\Entity\Category;
use App\Entity\Tag;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AnimalApiClient
{

    public const ACTION_CREATE = 'create',
        ACTION_UPDATE = 'update',
        ACTION_DELETE = 'delete';
    private Client $client;
    private string $apiUrl;

    public function __construct(string $apiUrl, Client $client)
    {
        $this->client = $client;
        $this->apiUrl = $apiUrl;
    }

    public function getAllAnimalsByStatus(string $status): ?array
    {

        try {
            $query = http_build_query(['status' => $status]);
            $url = sprintf('%spet/findByStatus?%s', $this->apiUrl, $query);
            $response = $this->client->get($url,
                [
                    'headers' => [
                        'Accept' => 'application/xml',
                    ]
                ]
            );
            $body = $response->getBody()->getContents();
            $xml = simplexml_load_string($body);


            if ($xml === false) {
                return null;
            }
            $animals = [];

            if (isset($xml->item[0])) {
                foreach ($xml->item as $item) {
                    $animal = (new Animal())
                        ->setId((int) $item->id)
                        ->setStatus($status)
                        ->setCategory((new Category())->setId((int) $item->category->id)->setName($item->category->name))
                        ->setName($item->name);

                    if (!empty($item->tags->tag)) {
                        foreach ($item->tags->tag as $tag) {
                            $animal->addTag((new Tag())->setId((int) $tag->id)->setName($tag->name));
                        }
                    }

                    if (!empty($item->photoUrls->photoUrl)) {
                        foreach ($item->photoUrls->photoUrl as $photoUrl) {
                            $animal->addPhotoUrl($photoUrl);
                        }
                    }

                    $animals[] = $animal;
                }

            } else {
                echo "Žiadne zvieratá nenájdené.";
            }
            return $animals;
        } catch (RequestException $e) {
            throw new \Exception("API request failed: " . $e->getMessage());
        }
    }

    public function existsOnServer(int $id): bool
    {
        try {
            $url = sprintf('%spet/%s', $this->apiUrl, $id);
            $response = $this->client->get($url);
            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            return false;
        }
    }

    public function createAnimal(Animal $data, $action = 'POST'): bool
    {
        try {
            $url = sprintf('%spet', $this->apiUrl);

            $response = $this->client->request($action, $url, [
                'json' => $data
            ]);

            return $response->getStatusCode() === 200;

        } catch (RequestException $e) {
            throw new \Exception("API request failed: " . $e->getMessage());
        }
    }

    public function updateAnimal(Animal $data): bool
    {
        $method = 'PUT';
        if (!$this->existsOnServer($data->getId())) {
            $method = 'POST';
        }
        try {
            $url = sprintf('%spet', $this->apiUrl);

            $response = $this->client->request($method, $url, [
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
            $response = $this->client->delete(sprintf('%spet/%s', $this->apiUrl, $id));
            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            throw new \Exception("API request failed: " . $e->getMessage());
        }
    }
}
