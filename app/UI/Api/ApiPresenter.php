<?php
declare(strict_types=1);

namespace App\UI\Api;

use App\Services\AnimalService;
use App\Services\InputValidator;
use App\Services\XmlManager;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tracy\Debugger;

class ApiPresenter extends Presenter
{

    public const STATUS_AVAILABLE = 'available',
        STATUS_PENDING = 'pending',
        STATUS_SOLD = 'sold';

    public const ANIMAL_STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_PENDING,
        self::STATUS_SOLD,
    ];

    public function __construct(
        private readonly XmlManager      $xmlManager,
        private readonly AnimalService $animalService,
        private readonly InputValidator $inputValidator,
    )
    {
        parent::__construct();
    }

    public function actionPet(int $petId = null): void
    {
        $httpRequest = $this->getHttpRequest();

        switch ($httpRequest->getMethod()) {
            case 'GET':
                $this->findById($petId);
                break;
            case 'POST':
                if ($petId === null) {
                     $this->add($httpRequest);
                } else {
                    $this->update($httpRequest);
                }
                break;
            case 'DELETE':
                $this->delete($petId);
                break;
            default:
                $this->error('Method not allowed', IResponse::S405_MethodNotAllowed);
        }
    }

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

        $currentStoredAnimal = $this->xmlManager->getAnimalById($animal->getId());
        if (isset($postData['removePhotoUrls']) && $postData['removePhotoUrls']) {

            $undeletedFiles = $this->animalService->deleteImages($currentStoredAnimal);
            if (count($undeletedFiles) > 0) {
                $this->flashMessage(sprintf( 'Tieto subory sa nepodarilo zmazat %s', implode(', ', $undeletedFiles)), 'warning');
            }

        } else {
            Debugger::log($files, Debugger::INFO);

            $animal->setPhotoUrls(array_merge($currentStoredAnimal->getPhotoUrls(), $this->animalService->processPhotoUrls($files['photoUrls'])));
        }

        $this->xmlManager->updateExisting($animal);

        $this->sendResponse(new JsonResponse(['message' => 'Pet updated successfully', 'status' => 'success']));
    }

    public function actionFindByStatus(string $status): void
    {
        if (!in_array($status, self::ANIMAL_STATUSES)) {
            $this->error('Invalid animal status: ' . $status);
        }

        $animals = $this->xmlManager->readAnimalsFromFileByStatus($status);
        $this->sendResponse(new JsonResponse([
            'status' => 'success',
            'data' => $animals
        ]));
    }

    public function findById(int $petId): void
    {
        $pet = $this->xmlManager->getAnimalById($petId);
        if ($pet !== null) {
            $this->sendResponse(new JsonResponse(['status' => 'success', 'data' => $pet]));
        } else {
            $this->sendResponse(new JsonResponse(['status' => 'error']));
        }

    }

    private function delete(int $petId): void
    {
        $animal = $this->xmlManager->getAnimalById($petId);
        $undeletedFiles = $this->animalService->deleteImages($animal);
        $this->xmlManager->deletePet($petId);
        $message = 'zviera bolo uspesne zmazane';

        if (count($undeletedFiles) > 0) {
            $message = sprintf('%s ale niektore obrazky sa nepodarilo vymazat : %s', $message, implode(', ', $undeletedFiles));
        }
        $this->sendResponse(new JsonResponse(['status' => 'success', 'message' => $message]));
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