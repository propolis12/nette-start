<?php
declare(strict_types=1);

namespace App\UI\Pet;

use App\Services\AnimalApiClient;
use App\Services\AnimalService;
use App\Services\XmlManager;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;

class PetApiPresenter extends Presenter
{

    private const STATUS_AVAILABLE = 'available',
        STATUS_PENDING = 'pending',
        STATUS_SOLD = 'sold';

    private const ANIMAL_STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_PENDING,
        self::STATUS_SOLD,
    ];
    private $httpRequest;

    public function __construct(
        private readonly AnimalApiClient $animalApiClient,
        private readonly XmlManager      $xmlManager,
        private readonly AnimalService $animalService
    )
    {
        parent::__construct();
    }

    /**
     * Pridanie nového maznáčika (POST)
     */
    public function actionAdd(): void
    {
        // Simulácia pridania maznáčika do databázy
        $newPet = [
            'id' => rand(1, 1000),
            'name' => 'New Pet',
            'status' => 'available'
        ];

        // Vrátenie JSON odpovede
        $this->sendResponse(new JsonResponse(['message' => 'Pet added successfully', 'pet' => $newPet]));
    }

    /**
     * Aktualizácia maznáčika (PUT)
     */
    public function actionUpdate(): void
    {
        // Skontroluj, že požiadavka je PUT
        if ($this->httpRequest->getMethod() !== 'PUT') {
            $this->error('Invalid request method', 405);
        }

        // Simulácia aktualizácie maznáčika v databáze
        $updatedPet = [
            'id' => 1, // Dummy ID
            'name' => 'Updated Pet',
            'status' => 'sold'
        ];

        // Vrátenie JSON odpovede
        $this->sendResponse(new JsonResponse(['message' => 'Pet updated successfully', 'pet' => $updatedPet]));
    }

    /**
     * Vyhľadanie maznáčikov podľa stavu (GET)
     */
    public function actionFindByStatus(): void
    {
        // Simulácia vyhľadávania maznáčikov podľa stavu
        $status = $this->getParameter('status');
        $pets = [
            ['id' => 1, 'name' => 'Buddy', 'status' => $status],
            ['id' => 2, 'name' => 'Milo', 'status' => $status],
        ];

        // Vrátenie JSON odpovede
        $this->sendResponse(new JsonResponse(['message' => 'Pets found by status', 'pets' => $pets]));
    }

    /**
     * Vyhľadanie maznáčikov podľa tagov (GET)
     */
    public function actionFindByTags(): void
    {
        // Simulácia vyhľadávania maznáčikov podľa tagov
        $tags = $this->getParameter('tags');
        $pets = [
            ['id' => 3, 'name' => 'Max', 'tags' => explode(',', $tags)],
            ['id' => 4, 'name' => 'Charlie', 'tags' => explode(',', $tags)],
        ];

        // Vrátenie JSON odpovede
        $this->sendResponse(new JsonResponse(['message' => 'Pets found by tags', 'pets' => $pets]));
    }

    /**
     * Vyhľadanie maznáčika podľa ID (GET)
     */
    public function actionFindById(int $petId): void
    {
        // Simulácia vyhľadania maznáčika podľa ID
        $pet = [
            'id' => $petId,
            'name' => 'Buddy',
            'status' => 'available'
        ];

        // Vrátenie JSON odpovede
        $this->sendResponse(new JsonResponse(['message' => 'Pet found by ID', 'pet' => $pet]));
    }

    /**
     * Aktualizácia maznáčika s formulárom (POST)
     */
    public function actionUpdateWithForm(int $petId): void
    {
        // Simulácia aktualizácie maznáčika podľa ID a údajov z formulára
        $updatedPet = [
            'id' => $petId,
            'name' => 'Updated Pet from Form',
            'status' => 'pending'
        ];

        // Vrátenie JSON odpovede
        $this->sendResponse(new JsonResponse(['message' => 'Pet updated successfully with form', 'pet' => $updatedPet]));
    }

    /**
     * Mazanie maznáčika podľa ID (DELETE)
     */
    public function actionDelete(int $petId): void
    {
        // Skontroluj, že požiadavka je DELETE
        if ($this->httpRequest->getMethod() !== 'DELETE') {
            $this->error('Invalid request method', 405);
        }

        // Simulácia mazania maznáčika podľa ID
        $this->sendResponse(new JsonResponse(['message' => 'Pet deleted successfully', 'petId' => $petId]));
    }

    /**
     * Nahratie obrázka pre maznáčika (POST)
     */
    public function actionUploadImage(int $petId): void
    {
        // Simulácia nahratia obrázka pre maznáčika
        $this->sendResponse(new JsonResponse(['message' => 'Image uploaded successfully for pet', 'petId' => $petId]));
    }
}