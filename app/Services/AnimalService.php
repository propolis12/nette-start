<?php

namespace App\Services;

use App\Entity\Tag;

class AnimalService
{

    private string $wwwDir;

    public function __construct(string $wwwDir, private readonly XmlManager $xmlManager)
    {
        $this->wwwDir = $wwwDir;
    }


    public function processTags(array $tags): array
    {
        $tagsEntitiesArray = [];
        $tagsForValues = [];
        $counter = 1;

        foreach ($tags as $tag) {
            // Vytvárame Tag entity
            $tagsEntitiesArray[] = (new Tag())->setId($counter)->setName($tag);

            // Pridávame tagy do poľa pripraveného na odoslanie vo forme ID a mena
            $tagsForValues[] = [
                'id' => $counter,
                'name' => $tag
            ];

            $counter++;
        }

        // Vraciame obe polia - jedno pre entity a druhé pre $values['tags']
        return [
            'tagsEntitiesArray' => $tagsEntitiesArray,
            'tagsForValues' => $tagsForValues
        ];
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

    public function deleteImages(int $animalId): ?array
    {
        $animal = $this->xmlManager->getAnimalById($animalId);

        if ($animal === null) {
            return null;
        }

        $problemImages = [];
        foreach ($animal->getPhotoUrls() as $photoUrl) {
            $filePath = $this->wwwDir . $photoUrl;

            // Over, či súbor existuje
            if (file_exists($filePath)) {
                echo 'subor existuje';
                // Pokús sa vymazať súbor
                if (!unlink($filePath)) {
                    $problemImages[] = $photoUrl;
                }
            }
        }
        return $problemImages;
    }

}