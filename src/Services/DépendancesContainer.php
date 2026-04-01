<?php

namespace App\Services;

use App\Lib\Tools;

// Factories
use App\Factories\ResponseErrorFactory;
use App\Factories\SessionFactory;
use App\Factories\ResponseFactory;
use App\Factories\AuthorizedDeviceFactory;
use App\Factories\EventFactory;
use App\Factories\UserFactory;
use App\Factories\ImageToEventFactory;
use App\Factories\CategoryFactory;
use App\Factories\LogsBadFactory;
use App\Factories\EmailFactory;
use App\Factories\ReservationFactory;
use App\Factories\TemplateFactory;
use App\Factories\ImageToTemplateFactory;
use App\Factories\BlacklistUserFactory;
use App\Factories\BlacklistIpFactory;

// Repositories
use App\Repositories\SessionRepository;
use App\Repositories\AuthorizedDeviceRepository;
use App\Repositories\EventRepository;
use App\Repositories\UserRepository;
use App\Repositories\ImageToEventRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\LogsBadRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\ImageToTemplateRepository;
use App\Repositories\BlacklistUserRepository;
use App\Repositories\BlacklistIpRepository;

// Validators
use App\Validators\SessionValidationService;
use App\Validators\AuthorizedDeviceValidationService;
use App\Validators\EventValidationService;
use App\Validators\UserValidationService;
use App\Validators\EmailValidationService;
use App\Validators\LogsBadValidationService;
use App\Validators\BlacklistUserValidationService;
use App\Validators\BlacklistIpValidationService;
use App\Validators\ReservationValidationService;
use App\Validators\TemplateValidationService;
use App\Validators\CategoryValidationService;
use App\Validators\ImageValidationService;

class DépendancesContainer {
    public DBConnection $db;
    public Tools $tools;

    // Factories
    public ResponseErrorFactory $responseErrorFactory;
    public SessionFactory $sessionFactory;
    public ResponseFactory $responseFactory;
    public AuthorizedDeviceFactory $authorizedDeviceFactory;
    public EventFactory $eventFactory;
    public UserFactory $userFactory;
    public ImageToEventFactory $imageToEventFactory;
    public CategoryFactory $categoryFactory;
    public LogsBadFactory $logsBadFactory;
    public EmailFactory $emailFactory;
    public ReservationFactory $reservationFactory;
    public TemplateFactory $templateFactory;
    public ImageToTemplateFactory $imageToTemplateFactory;
    public BlacklistUserFactory $blacklistUserFactory;
    public BlacklistIpFactory $blacklistIpFactory;

    // Repositories
    public SessionRepository $sessionRepository;
    public AuthorizedDeviceRepository $authorizedDeviceRepository;
    public EventRepository $eventRepository;
    public UserRepository $userRepository;
    public ImageToEventRepository $imageToEventRepository;
    public CategoryRepository $categoryRepository;
    public LogsBadRepository $logsBadRepository;
    public ReservationRepository $reservationRepository;
    public TemplateRepository $templateRepository;
    public ImageToTemplateRepository $imageToTemplateRepository;
    public BlacklistUserRepository $blacklistUserRepository;
    public BlacklistIpRepository $blacklistIpRepository;

    // Validators
    public SessionValidationService $sessionValidationService;
    public AuthorizedDeviceValidationService $authorizedDeviceValidationService;
    public EventValidationService $eventValidationService;
    public UserValidationService $userValidationService;
    public EmailValidationService $emailValidationService;
    public LogsBadValidationService $logsBadValidationService;
    public BlacklistUserValidationService $blacklistUserValidationService;
    public BlacklistIpValidationService $blacklistIpValidationService;
    public ReservationValidationService $reservationValidationService;
    public TemplateValidationService $templateValidationService;
    public CategoryValidationService $categoryValidationService;
    public ImageValidationService $imageValidationService;

    // Services
    public SessionService $sessionService;
    public AuthorizedDeviceService $authorizedDeviceService;
    public EventService $eventService;
    public EmailService $emailService;
    public UserService $userService;
    public TemplateService $templateService;
    public ReservationService $reservationService;
    public LogsBadService $logsBadService;
    public BlacklistUserService $blacklistUserService;
    public BlacklistIpService $blacklistIpService;

    public ImageService $imageService;

    public function __construct() {
        // === LIBRARIES ===
        $this->db = new DBConnection();
        $this->tools = new Tools();

        // === FACTORIES ===
        $this->responseErrorFactory = new ResponseErrorFactory();
        $this->sessionFactory = new SessionFactory();
        $this->responseFactory = new ResponseFactory();
        $this->authorizedDeviceFactory = new AuthorizedDeviceFactory();
        $this->eventFactory = new EventFactory();
        $this->userFactory = new UserFactory();
        $this->imageToEventFactory = new ImageToEventFactory();
        $this->categoryFactory = new CategoryFactory();
        $this->logsBadFactory = new LogsBadFactory();
        $this->emailFactory = new EmailFactory();
        $this->reservationFactory = new ReservationFactory();
        $this->templateFactory = new TemplateFactory();
        $this->imageToTemplateFactory = new ImageToTemplateFactory();
        $this->blacklistUserFactory = new BlacklistUserFactory();
        $this->blacklistIpFactory = new BlacklistIpFactory();

        // === REPOSITORIES ===
        $this->sessionRepository = new SessionRepository(db: $this->db, tools: $this->tools, sessionFactory: $this->sessionFactory);
        $this->authorizedDeviceRepository = new AuthorizedDeviceRepository(db: $this->db, tools: $this->tools, authorizedDeviceFactory: $this->authorizedDeviceFactory);
        $this->eventRepository = new EventRepository(db: $this->db, tools: $this->tools, eventFactory: $this->eventFactory);
        $this->userRepository = new UserRepository(db: $this->db, tools: $this->tools, userFactory: $this->userFactory);
        $this->imageToEventRepository = new ImageToEventRepository(db: $this->db, tools: $this->tools, imageToEventFactory: $this->imageToEventFactory);
        $this->categoryRepository = new CategoryRepository(db: $this->db, tools: $this->tools, categoryFactory: $this->categoryFactory);
        $this->logsBadRepository = new LogsBadRepository(db: $this->db, tools: $this->tools, logsBadFactory: $this->logsBadFactory);
        $this->reservationRepository = new ReservationRepository(db: $this->db, tools: $this->tools, factory: $this->reservationFactory);
        $this->templateRepository = new TemplateRepository(db: $this->db, tools: $this->tools, templateFactory: $this->templateFactory);
        $this->imageToTemplateRepository = new ImageToTemplateRepository(db: $this->db, tools: $this->tools, imageToTemplateFactory: $this->imageToTemplateFactory);
        $this->blacklistUserRepository = new BlacklistUserRepository(db: $this->db, tools: $this->tools, blacklistUserFactory: $this->blacklistUserFactory);
        $this->blacklistIpRepository = new BlacklistIpRepository(db: $this->db, tools: $this->tools, blacklistIpFactory: $this->blacklistIpFactory);

        // === VALIDATORS ===
        $this->sessionValidationService = new SessionValidationService();
        $this->authorizedDeviceValidationService = new AuthorizedDeviceValidationService(tools: $this->tools, authorizedDeviceRepository: $this->authorizedDeviceRepository);
        $this->eventValidationService = new EventValidationService(eventRepository: $this->eventRepository);
        $this->userValidationService = new UserValidationService(userRepository: $this->userRepository);
        $this->emailValidationService = new EmailValidationService();
        $this->logsBadValidationService = new LogsBadValidationService(logsBadRepository: $this->logsBadRepository);
        $this->blacklistUserValidationService = new BlacklistUserValidationService(blacklistUserRepository: $this->blacklistUserRepository);
        $this->blacklistIpValidationService = new BlacklistIpValidationService(blacklistIpRepository: $this->blacklistIpRepository);
        $this->reservationValidationService = new ReservationValidationService(tools: $this->tools, reservationRepository: $this->reservationRepository);
        $this->templateValidationService = new TemplateValidationService();
        $this->categoryValidationService = new CategoryValidationService();
        $this->imageValidationService = new ImageValidationService();

        // === SERVICES ===
        $this->imageService = new ImageService(
            $this->responseErrorFactory,
            $this->imageValidationService
        );

        $this->emailService = new EmailService(
            tools: $this->tools,
            emailFactory: $this->emailFactory,
            responseErrorFactory: $this->responseErrorFactory,
            emailValidationService: $this->emailValidationService
        );
        
        $this->sessionService = new SessionService(
            tools: $this->tools,
            sessionFactory: $this->sessionFactory,
            sessionRepository: $this->sessionRepository,
            sessionValidationService: $this->sessionValidationService,
            responseErrorFactory: $this->responseErrorFactory
        );
        
        $this->authorizedDeviceService = new AuthorizedDeviceService(
            tools: $this->tools,
            authorizedDeviceFactory: $this->authorizedDeviceFactory,
            responseErrorFactory: $this->responseErrorFactory,
            responseFactory: $this->responseFactory,
            logsBadFactory: $this->logsBadFactory,
            authorizedDeviceRepository: $this->authorizedDeviceRepository,
            userRepository: $this->userRepository,
            logsBadRepository: $this->logsBadRepository,
            authorizedDeviceValidationService: $this->authorizedDeviceValidationService,
            userValidationService: $this->userValidationService,
            emailService: $this->emailService
        );
        
        $this->eventService = new EventService(
            eventRepository: $this->eventRepository,
            reservationRepository: $this->reservationRepository,
            userRepository: $this->userRepository,
            imageToEventRepository: $this->imageToEventRepository,
            categoryRepository: $this->categoryRepository,
            eventValidationService: $this->eventValidationService,
            eventFactory: $this->eventFactory,
            categoryFactory: $this->categoryFactory,
            imageToEventFactory: $this->imageToEventFactory,
            responseErrorFactory: $this->responseErrorFactory,
            imageService: $this->imageService
        );
        
        $this->userService = new UserService(
            tools: $this->tools,
            userRepository: $this->userRepository,
            userValidationService: $this->userValidationService,
            userFactory: $this->userFactory,
            responseFactory: $this->responseFactory,
            responseErrorFactory: $this->responseErrorFactory,
            emailService: $this->emailService
        );
        
        $this->templateService = new TemplateService(
            templateFactory: $this->templateFactory,
            imageToTemplateFactory: $this->imageToTemplateFactory,
            categoryFactory: $this->categoryFactory,
            responseErrorFactory: $this->responseErrorFactory,
            templateRepository: $this->templateRepository,
            imageToTemplateRepository: $this->imageToTemplateRepository,
            categoryRepository: $this->categoryRepository,
            templateValidationService: $this->templateValidationService,
            categoryValidationService: $this->categoryValidationService
        );
        
        $this->reservationService = new ReservationService(
            reservationFactory: $this->reservationFactory,
            reservationRepository: $this->reservationRepository,
            eventRepository: $this->eventRepository,
            reservationValidationService: $this->reservationValidationService,
            responseErrorFactory: $this->responseErrorFactory,
            emailService: $this->emailService
        );
        
        $this->logsBadService = new LogsBadService(
            tools: $this->tools,
            logsBadFactory: $this->logsBadFactory,
            responseErrorFactory: $this->responseErrorFactory,
            logsBadRepository: $this->logsBadRepository,
            userRepository: $this->userRepository,
            logsBadValidationService: $this->logsBadValidationService
        );
        
        $this->blacklistUserService = new BlacklistUserService(
            tools: $this->tools,
            responseErrorFactory: $this->responseErrorFactory,
            blacklistUserRepository: $this->blacklistUserRepository,
            blacklistUserValidationService: $this->blacklistUserValidationService
        );
        
        $this->blacklistIpService = new BlacklistIpService(
            tools: $this->tools,
            blacklistIpFactory: $this->blacklistIpFactory,
            responseErrorFactory: $this->responseErrorFactory,
            blacklistIpRepository: $this->blacklistIpRepository,
            blacklistIpValidationService: $this->blacklistIpValidationService
        );
    }
}