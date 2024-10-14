<?php
declare(strict_types=1);

namespace App\UI\Api;

use App\Entity\Animal;
use App\Entity\Category;
use App\Services\AnimalApiClient;
use App\Services\AnimalService;
use App\Services\InputValidator;
use App\Services\XmlManager;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tracy\Debugger;
use Tracy\ILogger;

class ApiPresenter extends Presenter
{

    private const STATUS_AVAILABLE = 'available',
        STATUS_PENDING = 'pending',
        STATUS_SOLD = 'sold';

    public const ANIMAL_STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_PENDING,
        self::STATUS_SOLD,
    ];
    private $httpRequest;

    public function __construct(
        private readonly AnimalApiClient $animalApiClient,
        private readonly XmlManager      $xmlManager,
        private readonly AnimalService $animalService,
        private readonly InputValidator $inputValidator,
    )
    {
        parent::__construct();
    }

    public function actionPet(int $petId = null): void
    {
        $httpRequest = $this->getHttpRequest();  // Získaš HTTP požiadavku

        switch ($httpRequest->getMethod()) {
            case 'GET':
                // GET - nájdi zviera podľa ID
                $this->findById($petId);
                break;
            case 'POST':
                // POST - update zvieraťa podľa ID
                if ($petId === null) {
                     $this->add($httpRequest);
                } else {
                    $this->update($httpRequest);
                }
                break;
            case 'DELETE':
                // DELETE - zmaž zviera podľa ID
                $this->delete($petId);
                break;
            default:
                $this->error('Method not allowed', IResponse::S405_MethodNotAllowed);
        }
    }

    /**
     * Pridanie nového maznáčika (POST)
     */
    private function add(IRequest $request): JsonResponse
    {
        $postData = $request->getPost();
        $files = $request->getFiles();
        $errors = $this->inputValidator->validate($postData);

        if (!empty($errors)) {
            $this->sendResponse(new JsonResponse([
                'status' => 'error',
                'message' => 'nepodarilo sa pridat zviera: ' . implode(', ', $errors),
            ]));
        }

        $animal = $this->animalService->hydratePet($postData, $files);

        $lowestAvailableId = $this->xmlManager->getLowestAvailableId();
        $animal->setId($lowestAvailableId);
        $this->xmlManager->writeToFile($animal);

        $this->sendResponse(new JsonResponse([
            'status' => 'success',
            'message' => 'Pet bol úspešne pridaný!',
        ]));

    }

    /**
     * Aktualizácia maznáčika (PUT)
     */
    public function update(IRequest $request): void
    {
        $postData = $request->getPost();
        $files = $request->getFiles();
        $errors = $this->inputValidator->validate($postData);

        if (!empty($errors)) {
            $this->sendResponse(new JsonResponse([
                'status' => 'error',
                'message' => 'nepodarilo sa pridat zviera: ' . implode(', ', $errors),
            ]));
        }

        $animal = $this->animalService->hydratePet($postData, $files);

        $this->xmlManager->updateExisting($animal);

        // Vrátenie JSON odpovede
        $this->sendResponse(new JsonResponse(['message' => 'Pet updated successfully', 'status' => 'success']));
    }

    /**
     * Vyhľadanie maznáčikov podľa stavu (GET)
     */
    public function actionFindByStatus(string $status): void
    {
        Debugger::log($status, Debugger::INFO);
        // Simulácia vyhľadávania maznáčikov podľa stavu
        if (!in_array($status, self::ANIMAL_STATUSES)) {
            $this->error('Invalid animal status: ' . $status);
        }

//        $animals = $this->animalApiClient->getAllAnimalsByStatus($status);
        $animals = $this->xmlManager->readAnimalsFromFileByStatus($status);
        Debugger::log($animals, Debugger::INFO);
        $this->sendResponse(new JsonResponse([
            'status' => 'success',
            'data' => $animals
        ]));
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
    public function findById(int $petId): void
    {
        $pet = $this->xmlManager->getAnimalById($petId);
        if ($pet !== null) {
            $this->sendResponse(new JsonResponse(['status' => 'success', 'data' => $pet]));
        } else {
            $this->sendResponse(new JsonResponse(['status' => 'error']));
        }

    }


    /**
     * Mazanie maznáčika podľa ID (DELETE)
     */
    private function delete(int $petId): void
    {
        Debugger::log('som vo funkcii delete ' . $petId, Debugger::INFO);
        Debugger::log($this->getRequest()->getParameters(), Debugger::INFO);
        $animal = $this->xmlManager->getAnimalById($petId);
        $undeletedFiles = $this->animalService->deleteImages($animal);
        $this->xmlManager->deletePet($petId);
        $message = 'zviera bolo uspesne zmazane';

        if (count($undeletedFiles) > 0) {
            $message = sprintf('%s ale niektore obrazky sa nepodarilo vymazat : %s', $message, implode(', ', $undeletedFiles));
        }
        $this->sendResponse(new JsonResponse(['status' => 'success', 'message' => $message]));
    }

    /**
     * Nahratie obrázka pre maznáčika (POST)
     */
    public function actionUploadImage(int $petId): void
    {
        // Simulácia nahratia obrázka pre maznáčika
        $this->sendResponse(new JsonResponse(['message' => 'Image uploaded successfully for pet', 'petId' => $petId]));
    }


    public function actionSelectPet(): JsonResponse
    {
        $animals = $this->xmlManager->readAnimalsFromFile();
        $this->sendResponse(new JsonResponse([
            'status' => 'success',
            'data' => $animals
        ]));
    }

}