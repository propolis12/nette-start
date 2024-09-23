<?php

namespace App\UI\Animal;

use App\Services\AnimalApiClient;
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

    public function __construct(private AnimalApiClient $animalApiClient)
    {
    }

    public function renderStatuses(): void
    {
        $this->template->statuses = self::ANIMAL_STATUSES;
    }
    public function renderAnimalsByStatus(string $status)
    {
        if (!in_array($status, self::ANIMAL_STATUSES)) {
            $this->error('Invalid animal status: ' . $status);
        }
        $animals = $this->animalApiClient->getAllAnimalsByStatus($status);
    }

    public function renderShowByStatus()
    {

    }
}