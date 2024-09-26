<?php
declare(strict_types=1);

namespace App\Services;

use App\Entity\Animal;
use App\Entity\Category;
use App\Entity\Tag;

class XmlManager
{

    private const FILE_PATH = __DIR__ . '/../Data/animals.xml';


    public function writeToFile(Animal $animal): void
    {

        // Načítanie existujúceho XML súboru
        $xml = simplexml_load_file(self::FILE_PATH);

        // Pridanie nového zvieraťa
        $newAnimal = $xml->addChild('animal');
        $newAnimal->addChild('id',(string) $animal->getId());
        $newAnimal->addChild('name', $animal->getName());

        // Pridanie kategórie
        $category = $newAnimal->addChild('category');
        $category->addChild('id', (string)$animal->getCategory()->getId()); // predpokladám, že Category je objekt
        $category->addChild('name', $animal->getCategory()->getName());

        // Pridanie ďalších elementov
        $newAnimal->addChild('status', $animal->getStatus());

        // Pridanie URL pre fotky
        $photos = $newAnimal->addChild('photoUrls');
        foreach ($animal->getPhotoUrls() as $photoUrl) {
            $photos->addChild('photoUrl', $photoUrl);
        }

        // Pridanie tagov
        $tags = $newAnimal->addChild('tags');
        foreach ($animal->getTags() as $tag) {
            $tagElement = $tags->addChild('tag');
            $tagElement->addChild('id', (string)$tag->getId());
            $tagElement->addChild('name', $tag->getName());
        }

        // Uloženie späť do XML súboru
        $xml->asXML(self::FILE_PATH);

    }

    public function readAnimalsFromFile(): array
    {
        $animals = [];

        $currentXmlFile = simplexml_load_file(self::FILE_PATH);

        foreach ($currentXmlFile->animal as $animal) {
            $animalToWrite = (new Animal())
                        ->setId((int) $animal->id)
                        ->setName((string) $animal->name)
                        ->setStatus((string) $animal->status)
                        ->setCategory((new Category())->setId((int) $animal->category->id)->setName((string) $animal->category->name));
            $tags = [];
            $photoUrls = [];
            foreach ($animal->tags->tag as $tag) {
//                $tags[] = (new Tag())->setId((int) $tag->id)->setName((string) $tag->name);
                $animalToWrite->addTag((new Tag())->setId((int) $tag->id)->setName((string) $tag->name));
            }

            foreach ($animal->photoUrls->photoUrl as $photoUrl) {
                $photoUrls[] = $photoUrl;
            }
            $animalToWrite->setPhotoUrls($photoUrls);
            $animals[] = $animalToWrite;
        }

        return $animals;
    }

    public function checkIfExists(Animal $animal): bool
    {
        $currentXmlFile = simplexml_load_file(self::FILE_PATH);
        foreach ($currentXmlFile->animal as $animalItem) {
            if ((int) $animalItem->id === $animal->getId()) {
                return true;
            }
        }

        return false;
    }

    public function updateExisting(Animal $animal)
    {
        $currentXmlFile = simplexml_load_file(self::FILE_PATH);
        foreach ($currentXmlFile->animal as $animalItem) {
            if ((int) $animalItem->id === $animal->getId()) {
                $animalItem->name =  $animal->getName();
                $animalItem->status = $animal->getStatus();
                $animalItem->category->id =  $animal->getCategory()->getId();
                $animalItem->category->name = $animal->getCategory()->getName();
                unset($animalItem->tags->tag);
                unset($animalItem->photoUrls->photoUrl);
                foreach ($animal->getPhotoUrls() as $photoUrl) {
                    $animalItem->photoUrls->addChild('photoUrl', $photoUrl);
                }

                foreach ($animal->getTags() as $tag) {
                    $tagElement = $animalItem->tags->addChild('tag');
                    $tagElement->addChild('id', (string) $tag->getId());
                    $tagElement->addChild('name', $tag->getName());
                }
            }
        }

        $currentXmlFile->asXML(self::FILE_PATH);
    }

}