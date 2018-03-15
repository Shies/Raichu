<?php

use Raichu\Engine\App;
use Raichu\Engine\Dispatcher;
use Raichu\Provider\Session;

include ROOT . '/Config/defined.php';
// If has been set in php.ini, then override.
Session::config();
assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_WARNING, true);
assert_options(ASSERT_BAIL, true);
// assert_options(ASSERT_CALLBACK, ASSERT_ALERT);


$app = App::getInstance();

// Init common config
$app->config = include ROOT . '/Config/config.php';

// Enable Debug
$app->openDebug();

// Init Database
$options = include ROOT . '/Config/database.php';
$app->setDB($options);

// Init Dispacher
$app->dispatcher()->parseRouterUrl($app->getRequest());

// Init Router
$router = $app->getRouter();

// Init Loader
$app->autoload();


try {
    $files = ROOT.'/App/Modules/*/route*.*';
    foreach (glob($files) AS $val) {
        require_once $val;
    }

    $router->run($app->getRequest());
} catch (Exception $e) {
    $data['code'] = $e->getCode();
    $data['msg'] = $e->getMessage();

    // $app->getLogger()->error('[Exception]: '.$data['msg']);

    $app->getResponse()->ajaxReturn($data);
}
