<?php

namespace App\UI\Animal;

use App\UI\Api\ApiPresenter;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

class AnimalPresenter extends Presenter
{

    public function renderAnimalsByStatus(): void
    {
    }

    public function actionCreatePet()
    {
    }

    public function renderCreatePet()
    {
    }

    public function renderSelectPetToDelete(): void
    {
        $this->template->action = 'delete';
        $this->setView('selectPetToUpdate');
    }

    public function renderSelectPetToUpdate()
    {
        $this->template->action = 'update';
    }

    public function renderUpdatePet(int $animalId)
    {
    }

    public function createComponentCreatePet(): Form
    {
        $form = new Form;

        // Pridanie CSS triedy k celému formuláru
        $form->getElementPrototype()->class('styled-form');

        // Skryté pole pre ID
        $form->addHidden('id', 'create');

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
            'available' => ApiPresenter::STATUS_AVAILABLE,
            'pending' => ApiPresenter::STATUS_PENDING,
            'sold' => ApiPresenter::STATUS_SOLD,
        ])
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');

        // Pridanie enctype pre formulár, aby podporoval nahrávanie súborov
        $form->getElementPrototype()->enctype = 'multipart/form-data';


        $form->addSubmit('send', 'Uložiť')
            ->setHtmlAttribute('class', 'btn btn-primary');

        return $form;
    }

}