<?php

namespace App\Services;

use App\Models\ImageToTemplate;
use App\Models\Template;
use App\DTOModels\DTOTemplate;

use App\Factories\TemplateFactory;
use App\Factories\ImageToTemplateFactory;
use App\Factories\CategoryFactory;
use App\Factories\ResponseErrorFactory;

use App\Repositories\TemplateRepository;
use App\Repositories\ImageToTemplateRepository;
use App\Repositories\CategoryRepository;

use App\Services\ImageService;
use App\Validators\TemplateValidationService;
use App\Validators\CategoryValidationService;

use App\Responses\ResponseError;

class TemplateService
{
    private TemplateFactory $templateFactory;
    private ImageToTemplateFactory $imageToTemplateFactory;
    private CategoryFactory $categoryFactory;
    private ResponseErrorFactory $responseErrorFactory;
    private TemplateRepository $templateRepository;
    private ImageToTemplateRepository $imageToTemplateRepository;
    private CategoryRepository $categoryRepository;
    private TemplateValidationService $templateValidationService;
    private CategoryValidationService $categoryValidationService;
    private ImageService $imageService;

    public function __construct(
        TemplateFactory $templateFactory,
        ImageToTemplateFactory $imageToTemplateFactory,
        CategoryFactory $categoryFactory,
        ResponseErrorFactory $responseErrorFactory,
        TemplateRepository $templateRepository,
        ImageToTemplateRepository $imageToTemplateRepository,
        CategoryRepository $categoryRepository,
        TemplateValidationService $templateValidationService,
        CategoryValidationService $categoryValidationService,
        ImageService $imageService
    ){
        $this->templateFactory = $templateFactory;
        $this->imageToTemplateFactory = $imageToTemplateFactory;
        $this->categoryFactory = $categoryFactory;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->templateRepository = $templateRepository;
        $this->imageToTemplateRepository = $imageToTemplateRepository;
        $this->categoryRepository = $categoryRepository;
        $this->templateValidationService = $templateValidationService;
        $this->categoryValidationService = $categoryValidationService;
        $this->imageService = $imageService;
    }

    public function getAllTemplates(): array|ResponseError
    {
        try{
            $templates = $this->templateRepository->getAllTemplates();
            $arrayDTOTemplate = [];
            $images = [];
            $categories = [];
            $arrayDTOTemplate = [];
            foreach($templates as $template){
                $images = [];
                $categories = [];
                $images[] = $this->imageToTemplateRepository->getThumbnailImage(templateId: $template->id);
                $categories[] = $this->categoryRepository->getCategoriesOfTemplate(templateId: $template->id);
                $DTOTemplate = $this->templateFactory->createDynamic(template: $template,fields: ['id','title','description'],images: $images,categories: $categories);
                $arrayDTOTemplate[] = $DTOTemplate;
            }
            return $arrayDTOTemplate;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function getTemplateById(string $id): DTOTemplate|ResponseError
    {
        try{
            $template = $this->templateRepository->getTemplateById(id: $id);
            $images = [];
            $categories = [];
            $images[] = $this->imageToTemplateRepository->getThumbnailImage(templateId: $template->id);
            $categories[] = $this->categoryRepository->getCategoriesOfTemplate(templateId: $template->id);
            $DTOTemplate = $this->templateFactory->createDynamic(template: $template,fields: ['id','title','html','description'],images: $images,categories: $categories);
            return $DTOTemplate;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function addTemplate(Template $template, ?array $images = null, ?array $categories = null): DTOTemplate|ResponseError
    {
        try{
            $this->templateValidationService->validate(template: $template);
            $newTemplate = $this->templateRepository->addTemplate(template: $template);
            
            $imageNames = [];
            if($images){
                foreach($images as $image){
                    $imageName = $newTemplate->id . '_' . random_int(min: 100000, max: 999999);
                    $uploadResult = $this->imageService->resizeImage(base64: $image, fileName: $imageName, targetPath: __DIR__ . '/../../img/template/');
                    if($uploadResult instanceof ResponseError){
                        return $uploadResult;
                    }
                    $id = uniqid();
                    $imageToTemplate = $this->imageToTemplateFactory->createFromArray(data: ['id' => $id, 'templateId' => $newTemplate->id, 'fileName' => $imageName], isThumbnail: false);
                    $this->imageToTemplateRepository->addImageToTemplate(imageToTemplate: $imageToTemplate);
                    $imageNames[] = $imageName;
                }
            }
            
            if($categories){
                foreach($categories as $category){
                    $newCategory = $this->categoryFactory->createFromArray(data:['name' => $category]);
                    $this->categoryRepository->addCategory(category: $newCategory);
                    $this->categoryRepository->addCategoryToTemplate(templateId: $newTemplate->id,categoryId: $newCategory->id);
                }
            }
            $DTOTemplate = $this->templateFactory->createDynamic(template: $newTemplate,fields: ['id','title','description'],images: $imageNames,categories: $categories);
            return $DTOTemplate;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}