<?php

namespace App\Repositories;

use PDO;

use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\Event;

use App\Factories\EventFactory;

class EventRepository
{
    private DBConnection $db;
    private Tools $tools;
    private EventFactory $eventFactory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        EventFactory $eventFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->eventFactory = $eventFactory;
    }

    public function getEventById(string $id): Event|null
    {
        try{
            $query = "SELECT * FROM events WHERE id = :id AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':id', var: $id);
            $stmt->execute();
            $row = $stmt->fetch( mode: PDO::FETCH_ASSOC );
            if($row === false){
                return null;
            }else{
                $event = $this->eventFactory->createFromArray(data: $row);
                return $event;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getEventByUserId(string $userId): array|null
    {
        try{
            $query = "SELECT * FROM events WHERE userId = :userId AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':userId', var: $userId);
            $stmt->execute();
            $events = [];
            while($row = $stmt->fetch( mode: PDO::FETCH_ASSOC )){
                $event = $this->eventFactory->createFromArray(data: $row);
                $events[] = $event;
            }
            return $events;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre userId = {$userId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getAllEvents(): array|null
    {
        try {
            $query = "SELECT * FROM events WHERE isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->execute();
            $events = [];
            while($row = $stmt->fetch( mode: PDO::FETCH_ASSOC )){
                $event = $this->eventFactory->createFromArray(data: $row);
                $events[] = $event;
            }
            return $events;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function search(string $search): array|null
    {
        try{
            $searchCols = ["title","description","street","streetNumber","complement","zipCode","city","country"];
            $searchPatterns = explode(separator: ' ', string: $search);
            foreach($searchPatterns as &$term){
                $term = "%$term%";
            }
            $query = "SELECT DISTINCT events.* FROM events
                      INNER JOIN join_categories_to_events ON events.id = join_categories_to_events.eventId
                      INNER JOIN categories ON join_categories_to_events.categoryId = categories.id
                      WHERE ";
            foreach($searchPatterns as $term){
                $query .= "(";
                foreach($searchCols as $col){
                    $query .= "events.{$col} LIKE '$term' OR ";
                }
                $query .= "categories.name LIKE '$term' OR ";
                $query = rtrim(string: $query, characters: " OR ") . ") AND ";
            }
            $query .= "events.isDeleted = 0;";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute();
            $events = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $event = $this->eventFactory->createFromArray(data: $row);
                $events[] = $event;
            }
            return $events;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre search = {$search}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addEvent(Event $event): ?Event
    {
        try{
            $event->id = uniqid();
            $query = "INSERT INTO events(id,userId,title,description,html,street,streetNumber,zipCode,city,country,startDate,endDate,publishDate,openReservation,maxReservation,price,ageRestriction,isOnline,Isdeleted)
            VALUES(:id,:userId,:title,:description,:html,:street,:streetNumber,:zipCode,:city,:country,:startDate,:endDate,:publishDate,:openReservation,:maxReservation,:price,:ageRestriction,:isOnline,:isDeleted)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $event->id);
            $stmt->bindParam(param: ':userId', var: $event->userId);
            $stmt->bindParam(param: ':title', var: $event->title);
            $stmt->bindParam(param: ':description', var: $event->description);
            $stmt->bindParam(param: ':html', var:$event->html);
            $stmt->bindParam(param: ':street', var: $event->street);
            $stmt->bindParam(param: ':streetNumber', var: $event->streetNumber);
            $stmt->bindParam(param: ':zipCode', var: $event->zipCode);
            $stmt->bindParam(param: ':city', var: $event->city);
            $stmt->bindParam(param: ':country', var: $event->country);
            $stmt->bindParam(param: ':startDate', var: $event->startDate);
            $stmt->bindParam(param: ':endDate', var: $event->endDate);
            $stmt->bindParam(param: ':publishDate', var: $event->publishDate);
            $stmt->bindParam(param: ':openReservation', var: $event->openReservation);
            $stmt->bindParam(param: ':maxReservation', var: $event->maxReservation);
            $stmt->bindParam(param: ':price', var: $event->price);
            $stmt->bindParam(param: ':ageRestriction', var: $event->ageRestriction);
            $stmt->bindParam(param: ':isOnline', var: $event->isOnline);
            $stmt->bindParam(param: ':isDeleted', var: $event->isDeleted);
            $stmt->execute();
            return $event;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre user = " . json_encode(value: $event), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function updateEvent(Event $event): bool
    {
        try{
            $columnsToUpdate = [];
            $parameters = ['id' => $event->id];
            $parameters = ['userId' => $event->userId];
            if($event->title !== null){
                $columnsToUpdate[] = 'title = :title';
                $parameters['title'] = $event->title;
            }
            if($event->description !== null){
                $columnsToUpdate[] = 'description = :description';
                $parameters['description'] = $event->description;
            }
            if($event->html !== null){
                $columnsToUpdate[] = 'html = :html';
                $parameters['html'] = $event->html;
            }
            if($event->street !== null){
                $columnsToUpdate[] = 'street = :street';
                $parameters['street'] = $event->street;
            }
            if($event->streetNumber !== null){
                $columnsToUpdate[] = 'streetNumber = :streetNumber';
                $parameters['streetNumber'] = $event->streetNumber;
            }
            if($event->zipCode !== null){
                $columnsToUpdate[] = 'zipCode = :zipCode';
                $parameters['zipCode'] = $event->zipCode;
            }
            if($event->city !== null){
                $columnsToUpdate[] = 'city = :city';
                $parameters['city'] = $event->city;
            }
            if($event->country !== null){
                $columnsToUpdate[] = 'country = :country';
                $parameters['country'] = $event->country;
            }
            if($event->startDate !== null){
                $columnsToUpdate[] = 'startDate = :startDate';
                $parameters['startDate'] = $event->startDate;
            }
            if($event->endDate !== null){
                $columnsToUpdate[] = 'endDate = :endDate';
                $parameters['endDate'] = $event->endDate;
            }
            if($event->publishDate !== null){
                $columnsToUpdate[] = 'publishDate = :publishDate';
                $parameters['publishDate'] = $event->publishDate;
            }
            if($event->openReservation !== null){
                $columnsToUpdate[] = 'openReservation = :openReservation';
                $parameters['openReservation'] = $event->openReservation;
            }
            if($event->maxReservation !== null){
                $columnsToUpdate[] = 'maxReservation = :maxReservation';
                $parameters[''] = $event->maxReservation;
            }
            if($event->price !== null){
                $columnsToUpdate[] = 'price = :price';
                $parameters['price'] = $event->price;
            }
            if($event->ageRestriction !== null){
                $columnsToUpdate[] = 'ageRestriction = :ageRestriction';
                $parameters['ageRestriction'] = $event->ageRestriction;
            }
            if($event->isOnline !== null){
                $columnsToUpdate[] = 'isOnline = :isOnline';
                $parameters['isOnline'] = $event->isOnline;
            }
            if($event->isDeleted !== null){
                $columnsToUpdate[] = 'isDeleted = :isDeleted';
                $parameters['isDeleted'] = $event->isDeleted;
            }
            $query = "UPDATE events SET" . implode(separator: ", ", array: $columnsToUpdate) . "WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute(params: $parameters);
            return true;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre event = " . json_encode(value: $event), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}