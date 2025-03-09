<?php

namespace App\Services;

use App\Models\ImageToTemplate;
use App\Models\Template;

use App\Factories\TemplateFactory;
use App\Factories\ImageToTemplateFactory;
use App\Factories\CategoryFactory;
use App\Factories\ResponseErrorFactory;

use App\Repositories\TemplateRepository;
use App\Repositories\ImageToTemplateRepository;
use App\Repositories\CategoryRepository;

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

    public function __construct(
        TemplateFactory $templateFactory,
        ImageToTemplateFactory $imageToTemplateFactory,
        CategoryFactory $categoryFactory,
        ResponseErrorFactory $responseErrorFactory,
        TemplateRepository $templateRepository,
        ImageToTemplateRepository $imageToTemplateRepository,
        CategoryRepository $categoryRepository,
        TemplateValidationService $templateValidationService,
        CategoryValidationService $categoryValidationService
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
    }

    public function getAllTempaltes(): array|ResponseError
    {
        try{
            $templates = $this->templateRepository->getAllTemplates();
            $arrayDTOTemplate = [];
            $images = [];
            $categories = [];
            foreach($templates as $template){
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
}