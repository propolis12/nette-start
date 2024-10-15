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


    public function createComponentCreatePet(): Form
    {
        $form = new Form;

        $form->getElementPrototype()->class('styled-form');

        $form->addHidden('id', 'create');

        $form->addText('name', 'Meno')
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');

        $categoryContainer = $form->addContainer('category');

        $categoryContainer->addText('id', 'Category Id:')
            ->setRequired()
            ->addRule(Form::Integer, 'Zadajte celé číslo')
            ->addRule(Form::Range, 'Číslo musí byť nezáporné', [0, null])
            ->setHtmlAttribute('class', 'form-control');

        $categoryContainer->addText('name', 'Category Name:')
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');

        $tagContainer = $form->addContainer('tags');

        $tagContainer->addText('name', 'Tag Name:')
            ->setHtmlAttribute('placeholder', 'Tagy oddeľte čiarkami')
            ->setHtmlAttribute('class', 'form-control');

        $form->addUpload('photoUrls', 'Nahrávanie obrázkov')
            ->setHtmlAttribute('multiple', 'multiple') // Povolenie nahrávať viacero súborov
            ->addRule(Form::Image, 'Môžete nahrať iba obrázky.')
            ->addRule(Form::MaxFileSize, 'Maximálna veľkosť súboru je 2 MB', 2 * 1024 * 1024)
            ->setHtmlAttribute('class', 'form-control');

        $form->addCheckbox('removePhotoUrls', 'Vymazať aktuálne obrázky');

        $form->addSelect('status', 'Status', [
            'available' => ApiPresenter::STATUS_AVAILABLE,
            'pending' => ApiPresenter::STATUS_PENDING,
            'sold' => ApiPresenter::STATUS_SOLD,
        ])
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');

        $form->getElementPrototype()->enctype = 'multipart/form-data';


        $form->addSubmit('send', 'Uložiť')
            ->setHtmlAttribute('class', 'btn btn-primary');

        return $form;
    }

}