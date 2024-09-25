<?php

namespace App\UI\Animal;

use App\Services\AnimalApiClient;
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

    public function __construct(private readonly AnimalApiClient $animalApiClient)
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

    public function renderCreatePet()
    {

    }

    public function renderUpdatePet()
    {
        $this->template->isEdit = true;
    }

    public function createComponentCreatePet(bool $isEdit = false): Form
    {
        $form = new Form;

        $form->addText('id', 'Id')
            ->setRequired();

        $form->addText('name', 'Meno')
            ->setRequired();

        $categoryContainer = $form->AddContainer('category');

        $categoryContainer->addText('id', 'Category Id:')
            ->setRequired();

        $categoryContainer->addText('name', 'Category Name:')
            ->setRequired();

        $tagContainer = $form->AddContainer('tag');

        $tagContainer->addText('name', 'Tag Name:')->setHtmlAttribute('placeholder', 'tagy oddelte ciarkami');

        $form->addText('imagePath', 'Image path:');

        $form->addSelect('status', 'status', [
            'available' => self::STATUS_AVAILABLE,
            'pending' => self::STATUS_PENDING,
            'sold' => self::STATUS_SOLD,
        ])
            ->setRequired();

        $form->addSubmit('send', 'Vytvorit zviera');
        $form->onSuccess[] = $this->createPetSucceeded(...);

        return $form;
    }

    public function createComponentUpdatePet()
    {
        return $this->createComponentCreatePet(true);
    }

    private function createPetSucceeded(Form $form): void
    {
        $values = $form->getValues();
//        print_r($values);
//        die();
        $this->animalApiClient->createAnimal($values);
    }
}