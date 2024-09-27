<?php

namespace App\UI\Animal;

use App\Entity\Animal;
use App\Entity\Category;
use App\Entity\Tag;
use App\Services\AnimalApiClient;
use App\Services\XmlManager;
use Contributte\FormMultiplier\Multiplier;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;

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
        private readonly XmlManager      $xmlManager
    )
    {
        parent::__construct();
    }

    public function renderStatuses(): void
    {
        $this->template->statuses = self::ANIMAL_STATUSES;
    }

    public function renderAnimalsByStatus(string $status): void
    {
        if (!in_array($status, self::ANIMAL_STATUSES)) {
            $this->error('Invalid animal status: ' . $status);
        }

        $animals = $this->animalApiClient->getAllAnimalsByStatus($status);
        $this->template->animals = $animals;
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
        $this->template->action = 'deletePet';
        $this->setView('selectPetToUpdate');

    }

    public function renderSelectPetToUpdate()
    {
        $animals = $this->xmlManager->readAnimalsFromFile();
        $this->template->animals = $animals;
        $this->template->action = 'updatePet';
    }

    public function renderUpdatePet(int $animalId)
    {
        $animals = $this->xmlManager->readAnimalsFromFile();
        foreach ($animals as $animal) {
            if ($animal->getId() === $animalId) {
                $this->template->animal = $animal;

                $this->getComponent('createPet')->setDefaults($animal->toArray());
                $this->getComponent('createPet')->getComponent('id')->setValue($animal->getId());
                $this->getComponent('createPet')->getComponent('category')->getComponent('id')->setValue($animal->getCategory()->getId())->setHtmlAttribute('readonly', 'readonly');
                $this->getComponent('createPet')->getComponent('category')->getComponent('name')->setValue($animal->getCategory()->getName());
                $tags = $animal->getTags();
                foreach ($tags as $tag) {
                    $names[] = $tag->getName();
                }
                $tagsString = implode(',', $names);
                $this->getComponent('createPet')->getComponent('tags')->getComponent('name')->setValue($tagsString);

//                $photoUrls = $animal->getPhotoUrls();
                $this->getComponent('createPet')->getComponent('photoUrls')->setValue(implode(', ', $animal->getPhotoUrls()));
//                $this->getComponent('createPet')->getComponent('action')->setvalue('update');
                break;
            }
        }


    }

    public function actionDeletePet(int $animalId): void
    {
        try {
            $success = $this->animalApiClient->deleteAnimal($animalId);
            if ($success) {
                $this->xmlManager->deletePet($animalId);
            }
            if ($success) {
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

        $form->addHidden('id', AnimalApiClient::ACTION_CREATE);

        $form->addText('name', 'Meno')
            ->setRequired();

        $categoryContainer = $form->AddContainer('category');

        $categoryContainer->addText('id', 'Category Id:')
            ->setRequired();

        $categoryContainer->addText('name', 'Category Name:')
            ->setRequired();

        $tagContainer = $form->AddContainer('tags');

        $tagContainer->addText('name', 'Tag Name:')->setHtmlAttribute('placeholder', 'tagy oddelte ciarkami');

        $form->addText('photoUrls', 'Photo Urls');

        $form->addSelect('status', 'status', [
            'available' => self::STATUS_AVAILABLE,
            'pending' => self::STATUS_PENDING,
            'sold' => self::STATUS_SOLD,
        ])
            ->setRequired();

        $form->addSubmit('send', 'Ulozit');

        $form->onSuccess[] = $this->createPetSucceeded(...);

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
        $tagsEntitiesArray = [];
        $values['tags'] = [];
        $counter = 1;

        foreach ($tags as $tag) {
            $values['tags'][] =  [
                    'id' => $counter,
                    'name' => $tag
            ];
            $tagsEntitiesArray[] = (new Tag())->setId($counter)->setName($tag);
            $counter++;
        }

        $photoUrls = explode(',', $values['photoUrls']);
        $values['photoUrls'] = [];
        foreach ($photoUrls as $photoUrl) {
            $values['photoUrls'][] =  $photoUrl;
        }

        $animal = (new Animal())
            ->setName($values['name'])
            ->setCategory((new Category())->setId((int) $values['category']['id'])->setName($values['category']['name']))
            ->setStatus((string) $values['status'])
            ->setPhotoUrls($values['photoUrls'])
            ->setTags($tagsEntitiesArray);

        if ($values['id'] === AnimalApiClient::ACTION_CREATE) {

            $lowestAvailableId = $this->xmlManager->getLowestAvailableId();
            $animal->setId($lowestAvailableId);
            $values['id'] = $lowestAvailableId;
            try {
                $this->animalApiClient->createAnimal($values);
            } catch (\Exception $e) {
                $this->flashMessage('Nepodarilo sa vytvorit zviera.', 'error');
                return;
            }
            $this->xmlManager->writeToFile($animal);

        } else {
                $animal->setId($values['id']);
            try {
                $this->animalApiClient->updateAnimal($values);
            } catch (\Exception $e) {
                $this->flashMessage('Nepodarilo sa updatnut zviera.', 'error');
                return;
            }

            $this->xmlManager->updateExisting($animal);
        }
    }

}