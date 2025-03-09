<?php

namespace App\Repositories;

use PDO;

use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\ImageToEvent;

use App\Factories\ImageToEventFactory;

class ImageToEventRepository
{
    private DBConnection $db;
    private Tools $tools;
    private ImageToEventFactory $imageToEventFactory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        ImageToEventFactory $imageToEventFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->imageToEventFactory = $imageToEventFactory;
    }

    public function getImageToEventById(string $id): ImageToEvent|null
    {
        try{
            $query = "SELECT * FROM images_to_events WHERE id = :id AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':id', var: $id);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($row){
                $image = $this->imageToEventFactory->createFromArray(data: $row);
                return $image;
            }else{
                return null;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getImageToEventByEventId(string $eventId): array|null
    {
        try{
            $query = "SELECT * FROM images_to_events WHERE eventId = :eventId AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':eventId', var: $eventId);
            $stmt->execute();
            $images = [];
            while($row = $stmt->fetch( mode: PDO::FETCH_ASSOC )){
                $image = $this->imageToEventFactory->createFromArray(data: $row);
                $images[] = $image;
            }
            return $images;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre eventId = {$eventId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getThumbnailImage(string $eventId): ImageToEvent|null
    {
        try{
            $query = "SELECT * FROM images_to_events WHERE eventId = :eventId AND isThumbnail = 1";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':eventId', var: $eventId);
            $stmt->execute();
            $row = $stmt->fetch( mode: PDO::FETCH_ASSOC );
            if($row){
                $image = $this->imageToEventFactory->createFromArray(data: $row);
                return $image;
            }else{
                return null;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre eventId = {$eventId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addImageToEvent(ImageToEvent $imageToEvent): ?ImageToEvent
    {
        try{
            $imageToEvent->id = uniqid();
            $query = "INSERT INTO images_to_events (id, eventId, fileName, isThumbnail) VALUES (:id, :eventId, :fileName, :isThumbnail)";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':id', var: $imageToEvent->id);
            $stmt->bindParam(param: ':eventId', var: $imageToEvent->eventId);
            $stmt->bindParam(param: ':fileName', var: $imageToEvent->fileName);
            $stmt->bindParam(param:':isThumbnail', var: $imageToEvent->isThumbnail);
            $stmt->execute();
            return $imageToEvent;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre imageToEvent = " . json_encode(value: $imageToEvent), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function updateImageToEvent(ImageToEvent $imageToEvent): bool
    {
        try{
            $columnsToUpdate = [];
            $parameters = ['id' => $imageToEvent->id];
            if($imageToEvent->eventId !== null){
                $columnsToUpdate[] = "eventId = :eventId";
                $parameters['eventId'] = $imageToEvent->eventId;
            }
            if($imageToEvent->fileName !== null){
                $columnsToUpdate[] = "fileName = :fileName";
                $parameters['fileName'] = $imageToEvent->fileName;
            }
            if($imageToEvent->isThumbnail){
                $columnsToUpdate[] = 'isThumbnail = :isThumbnail';
                $parameters['isThumbnail'] = $imageToEvent->isThumbnail;
            }
            if($imageToEvent->isDeleted !== null){
                $columnsToUpdate[] = "isDeleted = :isDeleted";
                $parameters['isDeleted'] = $imageToEvent->isDeleted;
            }
            $query = "UPDATE images_to_events SET " . implode(separator: ', ', array: $columnsToUpdate) . " WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute(params: $parameters);
            return true;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre imageToEvent = " . json_encode(value: $imageToEvent), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}