<?php

Raichu\Provider\Session::init();

// App Instance
$app = Raichu\Engine\App::getInstance();
if ($app->getRequest()->getMethod() == 'OPTIONS') {
    // jsonp cross
    $app->getResponse()->abort(200);
    exit;
}

// Init common function library
$app->loadConfig("defined");

// Init common config
$app->loadConfig("config");

// Enable Debug
$app->openDebug();

// Init Database
$options = $app->loadConfig('database');
$app->setDB($options);

// Init Router
$router = $app->getRouter();

// init Dispatcher
$dispatcher = $app->dispatcher();

// Init Loader
$app->autoload();


try {
    $path = $app->getRequest()->getUrlPath();
    foreach ($app->config["modules"] AS $module => $prefix) {
        if (strpos($path, $prefix) !== false) {
            include APP_PATH.'/Modules/'.ucfirst($module).'/route.php';
            break;
        }
    }

    $dispatcher->dispatch($app->getRequest());
} catch (Exception $e) {
    $data['code'] = $e->getCode();
    $data['message'] = $e->getMessage();

    // $app->getLogger()->error('[Exception]: '.$data['message']);

    $app->getResponse()->ajaxReturn($data);
}
