<?php

namespace App\Repositories;

use PDO;

use App\Services\DBConnection;
use App\Lib\Tools;

use App\Models\Category;

use App\Factories\CategoryFactory;

class CategoryRepository
{
    private DBConnection $db;	
    private Tools $tools;
    private CategoryFactory $categoryFactory;

    public function __construct(
        DBConnection $db,
        Tools $tools,
        CategoryFactory $categoryFactory
    ){
        $this->db = $db;
        $this->tools = $tools;
        $this->categoryFactory = $categoryFactory;
    }

    public function getAllCategories(): array|null
    {
        try{
            $query = "SELECT * FROM categories WHERE isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute();
            $categories = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $category = $this->categoryFactory->createFromArray(data: $row);
                $categories[] = $category;
            }
            return $categories;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__, errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getAllCategoryById(string $id): Category|null
    {
        try{
            $query = "SELECT * FROM categories WHERE id = :id AND isDeleted = 0";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $id);
            $stmt->execute();
            $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);
            if($row === false){
                return null;
            }else{
                $category = $this->categoryFactory->createFromArray(data: $row);
                return $category;
            }
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre id = {$id}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getCategoriesOfEvent(string $eventId): array
    {
        try{
            $query = "SELECT * FROM categories 
                LEFT JOIN join_categories_to_events ON join_categories_to_events.eventId = :eventId";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':eventId', var: $eventId);
            $stmt->execute();
            $categories = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $category = $this->categoryFactory->createFromArray(data: $row);
                $categories[] = $category;
            }
            return $categories;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre eventId = {$eventId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function getCategoriesOfTemplate(string $templateId): array
    {
        try{
            $query = "SELECT * FROM categories 
                LEFT JOIN join_categories_to_templates ON join_categories_to_templates.templateId = :templateId";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':templateId', var: $templateId);
            $stmt->execute();
            $categories = [];
            while($row = $stmt->fetch(mode: PDO::FETCH_ASSOC)){
                $category = $this->categoryFactory->createFromArray(data: $row);
                $categories[] = $category;
            }
            return $categories;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre templateId = {$templateId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addCategoryToTemplate(string $templateId, string $categoryId): bool
    {
        try{
            $joinId = uniqid();
            $query = "INSERT INTO join_categories_to_templates (id, templateId, categoryId) VALUES (:id, :templateId, :categoryId)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $joinId);
            $stmt->bindParam(param: ':templateId', var: $templateId);
            $stmt->bindParam(param: ':categoryId', var: $categoryId);
            $stmt->execute();    
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre templateId = {$templateId} et categoryId = {$categoryId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function removeCategoryFromTemplate(string $templateId, string $categoryId): bool
    {
        try{
            $query = "DELETE FROM join_categories_to_templates WHERE templateId = :templateId AND categoryId = :categoryId";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':templateId', var: $templateId);
            $stmt->bindParam(param: ':categoryId', var: $categoryId);
            $stmt->execute();    
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre templateId = {$templateId} et categoryId = {$categoryId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addCategoryToEvent(string $eventId, string $categoryId): bool
    {
        try{
            $joinId = uniqid();
            $query = "INSERT INTO join_categories_to_events (id, eventId, categoryId) VALUES (:id, :eventId, :categoryId)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $joinId);
            $stmt->bindParam(param: ':eventId', var: $eventId);
            $stmt->bindParam(param: ':categoryId', var: $categoryId);
            $stmt->execute();    
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre eventId = {$eventId} et categoryId = {$categoryId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function removeCategoryFromEvent(string $eventId, string $categoryId): bool
    {
        try{
            $query = "DELETE FROM join_categories_to_events WHERE eventId = :eventId AND categoryId = :categoryId";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':eventId', var: $eventId);
            $stmt->bindParam(param: ':categoryId', var: $categoryId);
            $stmt->execute();    
            return true;
        }catch(\Exception $e){
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre eventId = {$eventId} et categoryId = {$categoryId}", errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function addCategory(Category $category): ?Category
    {
        try{
            $category->id = uniqid();
            $query = "INSERT INTO categories (id,name,isDeleted) VALUES (:id,:name,:isDeleted)";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->bindParam(param: ':id', var: $category->id);
            $stmt->bindParam(param: ':name', var: $category->name);
            $stmt->bindParam(param: ':isDeleted', var: $category->isDeleted);
            $stmt->execute();
            return $category;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre category = " . json_encode(value: $category), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }

    public function updateCategory(Category $category): bool
    {
        try{
            $columnsToUpdate = [];
            $parameters = ['id' => $category->id];
            if($category->name !== null){
                $columnsToUpdate[] = 'name = :name';
                $parameters['name'] = $category->name;
            }
            if($category->isDeleted !== null){
                $columnsToUpdate[] = 'isDeleted = :isDeleted';
                $parameters['isDeleted'] = $category->isDeleted;
            }
            $query = "UPDATE categories SET " . implode(separator: ', ', array: $columnsToUpdate) . " WHERE id = :id";
            $stmt = $this->db->getConnection()->prepare(query: $query);
            $stmt->execute(params: $parameters);
            return true;
        } catch (\Exception $e) {
            $idError = uniqid();
            $this->tools->myErrorHandler(errno: $e->getCode(), errstr: $e->getMessage() . "Erreur SQL [" . $idError . "] : " . __METHOD__ . " avec le paramètre category = " . json_encode(value: $category), errfile: $e->getFile(), errline: $e->getLine());
            throw new \Exception(message: "Erreur SQL : {$idError}", code: 1000);
        }
    }
}