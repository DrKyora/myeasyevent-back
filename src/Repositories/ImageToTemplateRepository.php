<?php

namespace App\Repositories;

use PDO;

use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\ImageToTemplate;

use App\Factories\ImageToTemplateFactory;

class ImageToTemplateRepository
{
    private $db;
    private $tools;
    private $imageToTemplateFactory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        ImageToTemplateFactory $imageToTemplateFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->imageToTemplateFactory = $imageToTemplateFactory;
    }

    public function getImageToTemplateById(string $id): ImageToTemplate|null
    {
        try{
            $query = "SELECT * FROM images_to_templates WHERE id = :id AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':id', var: $id);
            $stmt->execute();
            $image = $this->imageToTemplateFactory->createFromArray(data: $stmt->fetch(mode: PDO::FETCH_ASSOC));
            return $image;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getImageToTemplateByTemplateId(string $templateId): array|null
    {
        try{
            $query = "SELECT * FROM images_to_templates WHERE templateId = :templateId AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':templateId', var: $templateId);
            $stmt->execute();
            $images = [];
            while($row = $stmt->fetch( mode: PDO::FETCH_ASSOC )){
                $image = $this->imageToTemplateFactory->createFromArray(data: $row);
                $images[] = $image;
            }
            return $images;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre templateId = {$templateId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getThumbnailImage(string $templateId): ImageToTemplate|null
    {
        try{
            $query = "SELECT * FROM images_to_templates WHERE templateId = :templateId AND isThumbnail = 1";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':templateId', var: $templateId);
            $stmt->execute();
            $row = $stmt->fetch( mode: PDO::FETCH_ASSOC );
            if($row){
                $image = $this->imageToTemplateFactory->createFromArray(data: $row);
                return $image;
            }else{
                return null;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre templateId = {$templateId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addImageToTemplate(ImageToTemplate $imageToTemplate): ?ImageToTemplate
    {
        try{
            $imageToTemplate->id = uniqid();
            $query = "INSERT INTO images_to_templates (id, templateId, fileName, isThumbnail) VALUES (:id, :templateId, :fileName, :isThumbnail)";
            $stmt = $this->db->getConnection()->prepare( query: $query );
            $stmt->bindParam(param: ':id', var: $imageToTemplate->id);
            $stmt->bindParam(param: ':templateId', var: $imageToTemplate->templateId);
            $stmt->bindParam(param: ':fileName', var: $imageToTemplate->fileName);
            $stmt->bindParam(param:':, :isThumbnail', var: $imageToTemplate->isThumbnail);
            $stmt->execute();
            return $imageToTemplate;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre imageToTemplate = " . json_encode(value: $imageToTemplate), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function updateImageToTemplate(ImageToTemplate $imageToTemplate): bool
    {
        try{
            $columnsToUpdate = [];
            $parameters = ['id' => $imageToTemplate->id];
            if($imageToTemplate->templateId !== null){
                $columnsToUpdate[] = "templateId = :templateId";
                $parameters['templateId'] = $imageToTemplate->templateId;
            }
            if($imageToTemplate->fileName !== null){
                $columnsToUpdate[] = "fileName = :fileName";
                $parameters['fileName'] = $imageToTemplate->fileName;
            }
            if($imageToTemplate->isThumbnail !== null){
                $columnsToUpdate[] = "isThumbnail = :isThumbnail";
                $parameters['isThumbnail'] = $imageToTemplate->isThumbnail;
            }
            if($imageToTemplate->isDeleted !== null){
                $columnsToUpdate[] = "isDeleted = :isDeleted";
                $parameters['isDeleted'] = $imageToTemplate->isDeleted;
            }
            $query = "UPDATE images_to_templates SET " . implode(separator: ', ', array: $columnsToUpdate) . " WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute(params: $parameters);
            return true;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre imageToTemplate = " . json_encode(value: $imageToTemplate), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}