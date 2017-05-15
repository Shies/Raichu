Document of Raichu
---

### 目录结构规范
├── App\
│   ├── Bootstrap.php\
│   ├── Console\
│   │   ├── HelloCommand.php\
│   │   └── WorldCommand.php\
│   ├── Middleware\
│   │   ├── AsyncMiddleware.php\
│   │   ├── CSRFMiddleware.php\
│   │   └── FilterMiddleware.php\
│   └── Modules\
│       ├── Hello\
│       │   ├── Controller\
│       │   │   └── HelloController.php\
│       │   ├── Model\
│       │   │   └── HelloModel.php\
│       │   ├── Provider\
│       │   │   └── HelloProvider.php\
│       │   └── route.php\
│       └── World\
│           ├── Controller\
│           │   └── WorldController.php\
│           ├── Model\
│           │   └── WorldModel.php\
│           ├── Provider\
│           │   └── WorldProvider.php\
│           └── router.php\
├── Config\
│   ├── config.php\
│   ├── database.php\
│   └── defined.php\
├── Public\
│   └── index.php\
├── README.md\
├── System\
│   ├── Engine\
│   │   ├── AbstructController.php\
│   │   ├── AbstructModel.php\
│   │   ├── App.php\
│   │   ├── Container.php\
│   │   ├── Controller.php\
│   │   ├── Dispatcher.php\
│   │   ├── Loader.php\
│   │   ├── Middleware.php\
│   │   ├── Model.php\
│   │   ├── Request.php\
│   │   ├── Response.php\
│   │   ├── Router.php\
│   │   └── View.php\
│   ├── Middleware\
│   │   └── Clockwork\
│   │       ├── CacheStorage.php\
│   │       ├── DataSource.php\
│   │       └── Monitor.php\
│   └── Provider\
│       ├── Async\
│       │   ├── CoroutineReturnValue.php\
│       │   ├── Schedule.php\
│       │   ├── SysCall.php\
│       │   ├── Task.php\
│       │   └── test.php\
│       ├── Elk.php\
│       ├── Http.php\
│       ├── Logger.php\
│       └── Session.php\
├── composer.json\
└── tool

