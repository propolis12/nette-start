<?php
declare(strict_types=1);

namespace App\Services;

use App\Entity\Animal;
use App\Entity\Category;
use App\Entity\Tag;
use SimpleXMLElement;

class XmlManager
{

    private const FILE_PATH = __DIR__ . '/../Data/animals.xml';
    private const COUNTER_ID_STARTING_POINT = 1;

    public function loadFile(): SimpleXMLElement
    {
        $xml = @simplexml_load_file(self::FILE_PATH);
        if ($xml === false) {
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><animals></animals>');
        }
        return $xml;
    }

    public function writeToFile(Animal $animal): void
    {

        $xml = $this->loadFile();

        $newAnimal = $xml->addChild('animal');
        $newAnimal->addChild('id',(string) $animal->getId());
        $newAnimal->addChild('name', $animal->getName());

        $category = $newAnimal->addChild('category');
        $category->addChild('id', (string)$animal->getCategory()->getId()); // predpokladám, že Category je objekt
        $category->addChild('name', $animal->getCategory()->getName());

        $newAnimal->addChild('status', $animal->getStatus());

        $photos = $newAnimal->addChild('photoUrls');
        foreach ($animal->getPhotoUrls() as $photoUrl) {
            $photos->addChild('photoUrl', $photoUrl);
        }

        $tags = $newAnimal->addChild('tags');
        foreach ($animal->getTags() as $tag) {
            $tagElement = $tags->addChild('tag');
            $tagElement->addChild('id', (string)$tag->getId());
            $tagElement->addChild('name', $tag->getName());
        }

        $xml->asXML(self::FILE_PATH);

    }

    public function readAnimalsFromFile(): array
    {
        $animals = [];

        $currentXmlFile = $this->loadFile();

        foreach ($currentXmlFile->animal as $animal) {
            $animalToWrite = $this->getAnimalById((int) $animal->id);
            $animals[] = $animalToWrite;
        }

        return $animals;
    }

    public function checkIfExists(Animal $animal): bool
    {
        $currentXmlFile = $this->loadFile();
        foreach ($currentXmlFile->animal as $animalItem) {
            if ((int) $animalItem->id === $animal->getId()) {
                return true;
            }
        }

        return false;
    }

    public function updateExisting(Animal $animal)
    {
        $currentXmlFile = $this->loadFile();
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

    public function deletePet(int $animalId): void
    {
        $currentXmlFile = $this->loadFile();

        $dom = dom_import_simplexml($currentXmlFile)->ownerDocument;

        foreach ($currentXmlFile->animal as $animal) {
            if ((int) $animal->id === $animalId) {
                $animalDom = dom_import_simplexml($animal);
                $animalDom->parentNode->removeChild($animalDom);
                break;
            }
        }

        $dom->save(self::FILE_PATH);

        $currentXmlFile->asXML(self::FILE_PATH);
    }

    public function getLowestAvailableId(): int
    {
        $currentXmlFile = $this->loadFile();
        $ids = [];
        foreach ($currentXmlFile->animal as $animal) {
            $ids[] = (int) $animal->id;
        }

        sort($ids);
        $counter = self::COUNTER_ID_STARTING_POINT;

        if (empty($ids)) {
            return self::COUNTER_ID_STARTING_POINT;
        }

        while (in_array($counter, $ids)) {
            $counter++;
        }

        return $counter;
    }

    public function getAnimalById(int $id): ?Animal
    {
        $currentXmlFile = $this->loadFile();
        foreach ($currentXmlFile->animal as $animal) {
            if ((int) $animal->id === $id) {
                $animalToReturn = (new Animal())
                    ->setId((int) $animal->id)
                    ->setName((string) $animal->name)
                    ->setStatus((string) $animal->status)
                    ->setCategory((new Category())->setId((int) $animal->category->id)->setName((string) $animal->category->name));
                $photoUrls = [];
                foreach ($animal->tags->tag as $tag) {
                    $animalToReturn->addTag((new Tag())->setId((int) $tag->id)->setName((string) $tag->name));
                }

                foreach ($animal->photoUrls->photoUrl as $photoUrl) {
                    $photoUrls[] = (string) $photoUrl;
                }
                $animalToReturn->setPhotoUrls($photoUrls);
                return $animalToReturn;
            }
        }
        return null;
    }

}