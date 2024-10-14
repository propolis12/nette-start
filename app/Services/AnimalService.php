<?php

namespace App\Services;

use App\Entity\Animal;
use App\Entity\Category;
use App\Entity\Tag;
use Tracy\Debugger;

class AnimalService
{

    private string $wwwDir;

    public function __construct(string $wwwDir)
    {
        $this->wwwDir = $wwwDir;
    }


    public function processTags(array $tags): array
    {
        $tagsEntitiesArray = [];
        $counter = 1;

        foreach ($tags as $tag) {
            $tagsEntitiesArray[] = (new Tag())->setId($counter)->setName($tag);
            $counter++;
        }

        return $tagsEntitiesArray;
    }

    public function processPhotoUrls(array $photoUrls): array
    {
        $photoUrlsToSend = [];

        foreach ($photoUrls as $photoUrl) {
            if ($photoUrl->isOk() && $photoUrl->isImage()) {
                $originalFileName = $photoUrl->getName();
                $uniqueFileName = md5(uniqid(rand(), true)) . '_' . $originalFileName;
                $filePath = $this->wwwDir . '/images/' . $uniqueFileName;
                $photoUrl->move($filePath);
                $photoUrlsToSend[] = '/images/' . $uniqueFileName;
            }
        }

        return $photoUrlsToSend;
    }

    public function deleteImages(Animal $animal): ?array
    {
        $problemImages = [];
        foreach ($animal->getPhotoUrls() as $photoUrl) {
            $filePath = $this->wwwDir . $photoUrl;

            if (file_exists($filePath)) {

                if (!unlink($filePath)) {
                    $problemImages[] = $photoUrl;
                }
            }
        }
        return $problemImages;
    }

    public function hydratePet(array $postData, array $files)
    {
        $id = $postData['id'];
        $name = $postData['name'];
        $categoryId = $postData['category']['id'];
        $categoryName = $postData['category']['name'];
        $tags = $postData['tags']['name'];
        $status = $postData['status'];

        $tags = explode(',', $tags);

        $tags = array_filter($tags, fn($tag) => trim($tag) !== '');
        $tagsEntitiesArray = $this->processTags($tags);

        $animal = (new Animal())
            ->setName($name)
            ->setCategory((new Category())->setId((int) $categoryId)->setName($categoryName))
            ->setStatus($status)
            ->setTags($tagsEntitiesArray);

        if ($id !== 'create') {
            $animal->setId((int) $id);
        }

        $uploadedPhotos = $files['photoUrls'];

        Debugger::log($uploadedPhotos, Debugger::INFO);

        if (reset($uploadedPhotos) !== null) {
            $animal->setPhotoUrls($this->processPhotoUrls($uploadedPhotos));
        }

        return $animal;
    }

}