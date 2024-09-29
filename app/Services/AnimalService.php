<?php

namespace App\Services;

use App\Entity\Animal;
use App\Entity\Tag;

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

}