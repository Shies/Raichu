Document of Raichu
---

### 目录结构规范
├── App\<br>  
│   ├── Bootstrap.php\<br>  
│   ├── Console\<br>  
│   │   ├── HelloCommand.php\<br>  
│   │   └── WorldCommand.php\<br>  
│   ├── Middleware\<br>  
│   │   ├── AsyncMiddleware.php\<br>  
│   │   ├── CSRFMiddleware.php\<br>  
│   │   └── FilterMiddleware.php\<br>  
│   └── Modules\<br>  
│       ├── hello\<br>  
│       │   ├── controller\<br>  
│       │   │   └── Hello.php\<br>  
│       │   ├── model\<br>  
│       │   │   └── Hello.php\<br>  
│       │   ├── provider\<br>  
│       │   │   └── Hello.php\<br>  
│       │   └── route.php\<br>  
│       └── world\<br>  
│           ├── controller\<br>  
│           │   └── World.php\<br>  
│           ├── model\<br>  
│           │   └── World.php\<br>  
│           ├── provider\<br>  
│           │   └── World.php\<br>  
│           └── router.php\<br>  
├── Config\<br>  
│   ├── config.php\<br>  
│   ├── database.php\<br>  
│   └── defined.php\<br>  
├── Public\<br>  
│   └── index.php\<br>  
├── README.md\<br>  
├── System\<br>  
│   ├── Engine\<br>  
│   │   ├── AbstructController.php\<br>  
│   │   ├── AbstructModel.php\<br>  
│   │   ├── App.php\<br>  
│   │   ├── Container.php\<br>  
│   │   ├── Controller.php\<br>  
│   │   ├── Dispatcher.php\<br>  
│   │   ├── Loader.php\<br>  
│   │   ├── Middleware.php\<br>  
│   │   ├── Model.php\<br>  
│   │   ├── Request.php\<br>  
│   │   ├── Response.php\<br>  
│   │   ├── Router.php\<br>  
│   │   └── View.php\<br>  
│   ├── Middleware\<br>  
│   │   └── Clockwork\<br>  
│   │       ├── CacheStorage.php\<br>  
│   │       ├── DataSource.php\<br>  
│   │       └── Monitor.php\<br>  
│   └── Provider\<br>  
│       ├── Async\<br>  
│       │   ├── CoroutineReturnValue.php\<br>  
│       │   ├── Schedule.php\<br>  
│       │   ├── SysCall.php\<br>  
│       │   ├── Task.php\<br>  
│       │   └── test.php\<br>  
│       ├── Elk.php\<br>  
│       ├── Http.php\<br>  
│       ├── Logger.php\<br>  
│       └── Session.php\<br>  
├── composer.json\<br>  
└── tool\<br>  

