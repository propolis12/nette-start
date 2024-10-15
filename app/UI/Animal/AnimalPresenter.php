<?php

namespace App\UI\Animal;

use App\Entity\Animal;
use App\Entity\Category;
use App\Services\AnimalApiClient;
use App\Services\AnimalService;
use App\Services\XmlManager;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

class AnimalPresenter extends Presenter
{
    private const STATUS_AVAILABLE = 'available',
        STATUS_PENDING = 'pending',
        STATUS_SOLD = 'sold';

    private const ANIMAL_STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_PENDING,
        self::STATUS_SOLD,
    ];

    public function __construct(
        private readonly AnimalApiClient $animalApiClient,
        private readonly XmlManager      $xmlManager,
        private readonly AnimalService $animalService
    )
    {
        parent::__construct();
    }

    public function renderStatuses(): void
    {
        $this->template->statuses = self::ANIMAL_STATUSES;
    }

    public function renderAnimalsByStatus(): void
    {
//        if (!in_array($status, self::ANIMAL_STATUSES)) {
//            $this->error('Invalid animal status: ' . $status);
//        }

//        $animals = $this->animalApiClient->getAllAnimalsByStatus($status);
//        $this->template->animals = $animals;
    }

    public function actionCreatePet()
    {
    }

    public function renderCreatePet()
    {
    }

    public function renderSelectPetToDelete(): void
    {
        $animals = $this->xmlManager->readAnimalsFromFile();
        $this->template->animals = $animals;
        $this->template->action = 'delete';
        $this->setView('selectPetToUpdate');

    }

    public function renderSelectPetToUpdate()
    {
        $animals = $this->xmlManager->readAnimalsFromFile();
        $this->template->animals = $animals;
        $this->template->action = 'update';
    }

    public function actionUpdatePet(int $animalId): void
    {
        $this->getComponent('createPet')->addCheckbox('removePhotoUrls', 'Vymazať aktuálne obrázky');
    }



    public function renderUpdatePet(int $animalId)
    {
        $animals = $this->xmlManager->readAnimalsFromFile();
        foreach ($animals as $animal) {
            if ($animal->getId() === $animalId) {
                $this->template->animal = $animal;

//                $this->getComponent('createPet')->getComponent('name')->setValue($animal->getName());
                $this->getComponent('createPet')->getComponent('id')->setValue($animal->getId());
//                $this->getComponent('createPet')->getComponent('category')->getComponent('id')->setValue($animal->getCategory()->getId())->setHtmlAttribute('readonly', 'readonly');
//                $this->getComponent('createPet')->getComponent('category')->getComponent('name')->setValue($animal->getCategory()->getName());
//                $tags = $animal->getTags();
//                foreach ($tags as $tag) {
//                    $names[] = $tag->getName();
//                }
//                $tagsString = implode(',', $names);
//                $this->getComponent('createPet')->getComponent('tags')->getComponent('name')->setValue($tagsString);
//
//                $this->getComponent('createPet')->getComponent('photoUrls')->setValue(implode(', ', $animal->getPhotoUrls()));

                break;
            }
        }
        $this->template->animal = $animal;


    }


    public function actionDeletePet(int $animalId): void
    {
        try {
            $success = $this->animalApiClient->deleteAnimal($animalId);
            if ($success) {
                $animal = $this->xmlManager->getAnimalById($animalId);
                $undeletedFiles = $this->animalService->deleteImages($animal);
                $this->xmlManager->deletePet($animalId);
                if (count($undeletedFiles) > 0) {
                    $this->flashMessage(sprintf( 'Tieto subory sa nepodarilo zmazat %s', implode(', ', $undeletedFiles)), 'warning');
                }
                $this->flashMessage('Zviera bolo úspešne zmazané.', 'success');
            } else {
                $this->flashMessage('Nepodarilo sa zmazať zviera.', 'error');
            }
        } catch (\Exception $e) {
            $this->flashMessage('Nastala chyba pri mazaní zvieraťa: ' . $e->getMessage(), 'error');
        }
        $this->redirect('Home:default');
    }

    public function createComponentCreatePet(): Form
    {
        $form = new Form;

        // Pridanie CSS triedy k celému formuláru
        $form->getElementPrototype()->class('styled-form');

        // Skryté pole pre ID
        $form->addHidden('id', AnimalApiClient::ACTION_CREATE);

        // Pole pre meno
        $form->addText('name', 'Meno')
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');

        // Kontajner pre kategóriu
        $categoryContainer = $form->addContainer('category');

        $categoryContainer->addText('id', 'Category Id:')
            ->setRequired()
            ->addRule(Form::Integer, 'Zadajte celé číslo')
            ->addRule(Form::Range, 'Číslo musí byť nezáporné', [0, null])
            ->setHtmlAttribute('class', 'form-control');

        $categoryContainer->addText('name', 'Category Name:')
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');

        // Kontajner pre tagy
        $tagContainer = $form->addContainer('tags');

        $tagContainer->addText('name', 'Tag Name:')
            ->setHtmlAttribute('placeholder', 'Tagy oddeľte čiarkami')
            ->setHtmlAttribute('class', 'form-control');

        // Nahrávanie obrázkov
        $form->addUpload('photoUrls', 'Nahrávanie obrázkov')
            ->setHtmlAttribute('multiple', 'multiple') // Povolenie nahrávať viacero súborov
            ->addRule(Form::Image, 'Môžete nahrať iba obrázky.')
            ->addRule(Form::MaxFileSize, 'Maximálna veľkosť súboru je 2 MB', 2 * 1024 * 1024)
            ->setHtmlAttribute('class', 'form-control');

        $form->addCheckbox('removePhotoUrls', 'Vymazať aktuálne obrázky');



        // Select pre status
        $form->addSelect('status', 'Status', [
            'available' => self::STATUS_AVAILABLE,
            'pending' => self::STATUS_PENDING,
            'sold' => self::STATUS_SOLD,
        ])
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');

        // Pridanie enctype pre formulár, aby podporoval nahrávanie súborov
        $form->getElementPrototype()->enctype = 'multipart/form-data';


        $form->addSubmit('send', 'Uložiť')
            ->setHtmlAttribute('class', 'btn btn-primary');


        // Nastavenie callbacku po úspešnom submitnutí
//        $form->onSuccess[] = $this->createPetSucceeded(...);

        return $form;
    }


    public function createComponentUpdatePet(): Form
    {
        return $this->createComponentCreatePet();
    }

    private function createPetSucceeded(Form $form): void
    {

        $values = (array) $form->getValues();

        $tags = explode(',', $values['tags']['name']);

        $tags = array_filter($tags, fn($tag) => trim($tag) !== '');
        $tagsEntitiesArray = $this->animalService->processTags($tags);

        $animal = (new Animal())
            ->setName($values['name'])
            ->setCategory((new Category())->setId((int) $values['category']['id'])->setName($values['category']['name']))
            ->setStatus((string) $values['status'])
            ->setTags($tagsEntitiesArray);

//        ak sa zviera vytvara
        if ($values['id'] === AnimalApiClient::ACTION_CREATE) {

            $animal->setPhotoUrls($this->animalService->processPhotoUrls($values['photoUrls']));
            $lowestAvailableId = $this->xmlManager->getLowestAvailableId();
            $animal->setId($lowestAvailableId);
            try {

                $this->animalApiClient->createAnimal($animal);
            } catch (\Exception $e) {
                $this->flashMessage('Nepodarilo sa vytvorit zviera.', 'error');
                return;
            }
            $this->xmlManager->writeToFile($animal);
            $this->flashMessage('zviera uspesne vytvorene');

//            ak sa zviera updatuje
        } else {
            $animal->setId($values['id']);
            $currentStoredAnimal = $this->xmlManager->getAnimalById($animal->getId());
            if (isset($values['removePhotoUrls']) && $values['removePhotoUrls']) {

                $undeletedFiles = $this->animalService->deleteImages($currentStoredAnimal);
                if (count($undeletedFiles) > 0) {
                    $this->flashMessage(sprintf( 'Tieto subory sa nepodarilo zmazat %s', implode(', ', $undeletedFiles)), 'warning');
                }

            } else {
                $animal->setPhotoUrls(array_merge($currentStoredAnimal->getPhotoUrls(), $this->animalService->processPhotoUrls($values['photoUrls'])));
            }

            try {

                $this->animalApiClient->updateAnimal($animal);

            } catch (\Exception $e) {
                $this->flashMessage($e->getMessage(), 'error');
                return;
            }

            $this->xmlManager->updateExisting($animal);
            $this->flashMessage('zviera spesne updatnute');
        }

        $this->redirect('Home:default');
    }

}