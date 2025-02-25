<?php

namespace App\Repositories;

use PDO;

use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\Template;

use App\Factories\TemplateFactory;

class TemplateRepository
{
    private DBConnection $db;
    private Tools $tools;
    private TemplateFactory $templateFactory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        TemplateFactory $templateFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->templateFactory = $templateFactory;
    }

    public function getAllTemplates(): array
    {
        try{
            $query = "SELECT * FROM templates WHERE isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute();
            $templates = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $template = $this->templateFactory->createFromArray(data: $row);
                $templates[] = $template;
            }
            return $templates;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getTemplateById(string $id): Template|null
    {
        try{
            $query = "SELECT * FROM templates WHERE id = :id AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param:':id',var: $id);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($row === false){
                return null;
            }else{
                $template = $this->templateFactory->createFromArray(data: $row);
                return $template;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addTemplate(Template $template): Template|null
    {
        try{
            $template->id = uniqid();
            $query = "INSERT INTO templates(id,description,html,isDeleted)VALUES(:id,:description,:html,:isDeleted)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param:':id',var: $template->id);
            $stmt->bindParam(param:':description',var: $template->description);
            $stmt->bindParam(param:':html',var: $template->html);
            $stmt->bindParam(param:':isDeleted',var: $template->isDeleted);
            $stmt->execute();
            return $template;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre template = " . json_encode(value: $template), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function updateTemplate(Template $template): bool|null
    {
        try{
            $columnsToUpdate = [];
            $parameters = [':id' => $template->id];
            if($template->description !== null){
                $columnsToUpdate[] = "description = :description";
                $parameters[':description'] = $template->description;
            }
            if ($template->html !== null) {
                $columnsToUpdate[] = "html = :html";
                $parameters[":html"] = $template->html;
            }
            if ($template->isDeleted !== null) {
                $columnsToUpdate[] = "isDeleted = :isDeleted";
                $parameters[":isDeleted"] = $template->isDeleted;
            }
            $query = "UPDATE templates SET " . implode(separator: ', ', array: $columnsToUpdate) . " WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute(params: $parameters);
            return true;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre template = " . json_encode(value: $template), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}