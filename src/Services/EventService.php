<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Event;
use App\Models\ImageToEvent;
use App\Models\Reservation;
use App\DTOModels\DTOEvent;

use App\Factories\ResponseErrorFactory;
use App\Factories\EventFactory;
use App\Factories\ImageToEventFactory;
use App\Factories\CategoryFactory;

use App\Repositories\EventRepository;
use App\Repositories\ImageToEventRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;

use App\Services\ImageService;

use App\Responses\ResponseError;

use App\Validators\EventValidationService;

class EventService
{
    private EventRepository $eventRepository;
    private ReservationRepository $reservationRepository;
    private UserRepository $userRepository;
    private ImageToEventRepository $imageToEventRepository;
    private CategoryRepository $categoryRepository;
    private EventValidationService $eventValidationService;
    private EventFactory $eventFactory;
    private CategoryFactory $categoryFactory;
    private ImageToEventFactory $imageToEventFactory;
    private ImageService $imageService;
    private ResponseErrorFactory $responseErrorFactory;
    public function __construct(
        EventRepository $eventRepository,
        ReservationRepository $reservationRepository,
        UserRepository $userRepository,
        ImageToEventRepository $imageToEventRepository,
        CategoryRepository $categoryRepository,
        EventValidationService $eventValidationService,
        EventFactory $eventFactory,
        CategoryFactory $categoryFactory,
        ImageToEventFactory $imageToEventFactory,
        ImageService $imageService,
        ResponseErrorFactory $responseErrorFactory
    ){
        $this->eventRepository = $eventRepository;
        $this->reservationRepository = $reservationRepository;
        $this->userRepository = $userRepository;
        $this->imageToEventRepository = $imageToEventRepository;
        $this->categoryRepository = $categoryRepository;
        $this->eventValidationService = $eventValidationService;
        $this->eventFactory = $eventFactory;
        $this->categoryFactory = $categoryFactory;
        $this->imageToEventFactory = $imageToEventFactory;
        $this->imageService = $imageService;
        $this->responseErrorFactory = $responseErrorFactory;
    }

    public function getEventById(string $id): DTOEvent|ResponseError
    {
        try{
            $event = $this->eventRepository->getEventById(id: $id);
            $user = $this->userRepository->getUserById(id: $event->userId);
            $userName = "{$user->firstName} {$user->lastName}";
            $reservations = $this->reservationRepository->getResevationsOfEvent(eventId: $event->id);
            $images = $this->imageToEventRepository->getImageToEventByEventId(eventId: $event->id);
            $categories = $this->categoryRepository->getCategoriesOfEvent(eventId: $event->id);
            $arrayAddress = ['street' => $event->street, 'streetNumer' => $event->streetNumber, 'zipCode' => $event->zipCode, 'city' => $event->city, 'country' => $event->country];
            $arrayReservations = [];
                foreach($reservations as $reservation){
                    $arrayReservation = [];
                    $arrayReservation = ['lastName' => $reservation->lastName,'firstName' =>$reservation->firstName,'dateReservation' => $reservation->dateReservation];
                    $arrayReservations[] = $arrayReservation;
                }
                $DTOEvent = $this->eventFactory->createDynamic(event: $event,fields: ['id','title','startDate','html','endDate','maxReservation','ageRestriction','price'],address: $arrayAddress,reservation: $arrayReservations,images: $images,categories: $categories,userName: $userName);
            return $DTOEvent;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function getEventsOfUser(string $userId): array|ResponseError
    {
        try{
            $events = $this->eventRepository->getEventByUserId(userId: $userId);
            $user = $this->userRepository->getUserById(id: $userId);
            $userName = "{$user->firstName} {$user->lastName}";
            $arrayDTOEvent = [];
            foreach($events as $event){
                $arrayAddress = ['street' => $event->street, 'streetNumer' => $event->streetNumber, 'zipCode' => $event->zipCode, 'city' => $event->city, 'country' => $event->country];
                $reservations = $this->reservationRepository->getResevationsOfEvent(eventId: $event->id);
                $arrayReservations = [];
                foreach($reservations as $reservation){
                    $arrayReservation = [];
                    $arrayReservation = ['lastName' => $reservation->lastName,'firstName' =>$reservation->firstName,'dateReservation' => $reservation->dateReservation];
                    $arrayReservations[] = $arrayReservation;
                }
                $imagesOfEvent = $this->imageToEventRepository->getImageToEventByEventId(eventId: $event->id);
                $arrayDTOEvent[] = $this->eventFactory->createDynamic(event: $event,fields: ['id','title','startDate','endDate','maxReservation','ageRestriction','price'],userName: $userName,address: $arrayAddress,reservation: $arrayReservations,images: $imagesOfEvent);
            }
            return $arrayDTOEvent;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function getAllEvents(): array|ResponseError
    {
        try{
            $events = $this->eventRepository->getAllEvents();
            $arrayDTOEvent = []; // ⬅️ Sortir de la boucle foreach($events)
            foreach($events as $event){
                $user = $this->userRepository->getUserById(id: $event->userId);
                $userName = "{$user->firstName} {$user->lastName}";
                $arrayAddress = ['street' => $event->street, 'streetNumer' => $event->streetNumber, 'zipCode' => $event->zipCode, 'city' => $event->city, 'country' => $event->country];
                $reservations = $this->reservationRepository->getResevationsOfEvent(eventId: $event->id);
                $arrayReservations = [];
                foreach($reservations as $reservation){
                    $arrayReservation = ['lastName' => $reservation->lastName,'firstName' =>$reservation->firstName,'dateReservation' => $reservation->dateReservation];
                    $arrayReservations[] = $arrayReservation;
                }
                $imagesOfEvent = $this->imageToEventRepository->getImageToEventByEventId(eventId: $event->id);
                $arrayDTOEvent[] = $this->eventFactory->createDynamic(event: $event,fields: ['id','title','startDate','endDate','maxReservation','ageRestriction','price'],userName: $userName,address: $arrayAddress,reservation: $arrayReservations,images: $imagesOfEvent);
            }
            return $arrayDTOEvent;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function search(string $search): array|ResponseError
    {
        try{
            $events = $this->eventRepository->search(search: $search);
            foreach($events as $event){
                $user = $this->userRepository->getUserById(id: $event->userId);
                $userName = "{$user->firstName} {$user->lastName}";
                $arrayDTOEvent = [];
                $arrayAddress = ['street' => $event->street, 'streetNumer' => $event->streetNumber, 'zipCode' => $event->zipCode, 'city' => $event->city, 'country' => $event->country];
                $reservations = $this->reservationRepository->getResevationsOfEvent(eventId: $event->id);
                foreach($reservations as $reservation){
                    $arrayReservations = [];
                    $arrayReservation = [];
                    $arrayReservation = ['lastName' => $reservation->lastName,'firstName' =>$reservation->firstName,'dateReservation' => $reservation->dateReservation];
                    $arrayReservations[] = $arrayReservation;
                }
                $imagesOfEvent = $this->imageToEventRepository->getImageToEventByEventId(eventId: $event->id);
                $arrayDTOEvent[] = $this->eventFactory->createDynamic(event: $event,fields: ['id','title','startDate','endDate','maxReservation','ageRestriction','price'],userName: $userName,address: $arrayAddress,reservation: $arrayReservations,images: $imagesOfEvent);
            }
            return $arrayDTOEvent;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function createEvent(Event $event, array $images, array $categories): bool|ResponseError
    {
        try{
            $this->eventValidationService->validate(event: $event);
            $newEvent = $this->eventRepository->addEvent(event: $event);
            foreach($images as $image){
                $imageName = $event->id . '_' . random_int(min: 100000,max: 999999);
                $uploadResult = $this->imageService->resizeImage(base64: $image, fileName: $imageName, targetPath: __DIR__ . '/../../img/events/');
                if($uploadResult instanceof ResponseError){
                    return $uploadResult;
                }
                $imageToEvent = $this->imageToEventFactory->createFromArray(data: ['eventId' => $event->id,'fileName' => $imageName]);
                $this->imageToEventRepository->addImageToEvent(imageToEvent: $imageToEvent);
                $images[] = $imageName;
            }
            foreach($categories as $category){
                $Category = $this->categoryFactory->createFromArray(data: ['eventId' => $newEvent->id,'name' => $category]);
                $newCategory = $this->categoryRepository->addCategory(category: $Category);
                $this->categoryRepository->addCategoryToEvent(eventId: $newCategory->id,categoryId: $event->id);
            }
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function updateEvent(Event $event): bool|ResponseError
    {
        try{
            $this->eventValidationService->validate(event: $event);
            $this->eventRepository->updateEvent(event: $event);
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function deleteEvent(string $id): bool|ResponseError
    {
        try{
            $event = $this->eventRepository->getEventById( id: $id);
            if($event){
                $event->isDeleted = true;
                $this->eventRepository->updateEvent(event: $event);
            }
            return true;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}