<?php
define('ROOT', __DIR__.'/..');
include ROOT .'/Config/defined.php';

if (!defined('IN_ROOT')) {
    die('Hacking attempt.');
}

require ROOT .'/vendor/autoload.php';
require TPL_PATH .'/trait.php';

new \Raichu\Engine\Loader();
$app = \Raichu\Engine\App::getInstance();
$app->loadConfig('config');

// phpunit test
class DispatcherTest extends \Raichu\Engine\Dispatcher
{
    // example and simple sample
    use Contract;

    private $dispatcher;
    protected $instantly_flush;

    // core and kernel
    private static $_family = [
        'dispatcher',
        'container',
        'router',
    ];

    /**
     * DispatcherTest constructor.
     * @return void
     */
    public function __construct(\Raichu\Engine\App $app = null)
    {
        if (is_null($app)) {
            global $app;
        }
        $this->dispatcher = $app->dispatcher();
        parent::__construct();
    }


    /**
     * pass done
     * @return void
     */
    function segments()
    {
        $request = $this->app->getRequest();

        // 当前这里，是内部跳转路由地址, 不是外部请求路由地址
        $request = $request->setUri('/hello/hello/index');
        $this->app->parseRouterUrl($request);

        echo $this->app->getRouter()->fetchModule()."\n";
        echo $this->app->getRouter()->fetchController()."\n";
        echo $this->app->getRouter()->fetchMethod()."\n";

        print_r($this->app->getRouter()->fetchParams());
    }


    /**
     * pass done
     * @return void
     */
    function parseRoute()
    {
        // print_r(spl_classes());
        // exit;

        $request = $this->app->getRequest();

        ob_start();
        $request = $request->setUri('/hello/hello/listen');
        echo $this->dispatcher->dispatch($request);
        $buffer = @ob_get_clean();

        echo $buffer;
    }


    /**
     * pass done
     * Event dispatch and create object instance
     * @return void
     */
    function eventDispatcher()
    {
        $self = $this->app;
        $temp = static::$_family;

        $self->instance('pipe1', function(\Raichu\Engine\Container $di, array $params) use(&$self, &$temp) {
            $temp = array_merge($temp, $params);
            return $self;
        });

        $closure = $self->make('pipe1');
        $self = $closure(new \Raichu\Engine\Container(), ['foo', 'bar']);
        print_r($temp);

        $self->instance('pipe2', function(\Raichu\Engine\Container $di) use(&$self, &$temp) {
            if (isset($temp[4])) {
                unset($temp[4]);
            }
            return $self;
        });

        $closure = $self->make('pipe2');
        $self = $closure(new \Raichu\Engine\Container());
        print_r($temp);

        $self->instance('pipe3', function() use(&$temp) {
            unset($temp[3]);
        });

        $closure = $self->make('pipe3');
        $self = $closure();

        print_r($temp);
    }


    /**
     * pass done
     * clean global app object
     * window.location.href
     * @return void
     */
    function cleanObject()
    {
        $this->dispatcher->redirect('https://github.com/Shies/Raichu', 'raichu');

        // generate a clean is super global object
        $GLOBALS['app'] = null;

        global $app;
        $dispatcher = $app->dispatcher();

        // position refresh
        // $this->dispatcher->redirect('http://l.raichu.com/api/hello/listen');

        // relative location
        $dispatcher->redirect('/api/hello/hello');
        die();
    }


    /**
     * pass done
     * Render flashing
     * use smarty template engine and origin php
     * @return void
     */
    function flashing()
    {
        ini_set("short_open_tag", 0);

        $foo = 'foo';
        $bar = 'bar';

        // 开启即时刷新 (invalid value of instantly_flush for true)
        // $this->dispatcher->instantly_flush = true;

        // 强制即时刷新 @override
        $this->instantly_flush = !false;

        echo TPL_PATH.'/heredoc';
        // $this->render(TPL_PATH .'/heredoc');
        // $this->render(TPL_PATH .'/heredoc', ['foo' => $foo, 'bar' => $bar], true);

        ob_start();
        $buffer = $this->render(TPL_PATH .'/heredoc', ['foo' => $foo, 'bar' => $bar], false);
        usleep(3000000);
        ob_end_flush();

        $this->instantly_flush = false;
        exit($buffer);
    }


    /**
     * pass done
     * @return array
     */
    function enabled()
    {
        $this->dispatcher->enable('/api/hello', 'hello');
        $this->dispatcher->enable('/api/world', 'world');

        print_r($this->dispatcher->module_enabled);
    }


    /**
     * pass done
     * @return void
     */
    function catchError()
    {
        $filter = $this->dispatcher->getMiddleware(FilterMiddleware::class);

        try {
            $filter->checkAdmin();
        } catch (\Exception $e) {
            // to do sth.
            print_r($e->getTrace());
        }

        return;
    }


    // stub
    public function index()
    {
        echo 'stub: hello world';
    }


    /**
     * controller => Hello::class,
     * action => index,
     * params => [one, two, three]
     *
     * pass done
     * 控制器之间互相回调
     * @return void
     */
    function forwardEvent()
    {
        \Raichu\Engine\Loader::import('DispatcherTest.php', '/../tests');
        var_dump(\Raichu\Engine\Loader::loaded('DispatcherTest.php'));

        // crash reason: composer update
        // $crash = <<<CRASH
// Deprecation Notice: Class Raichu\Engine\AbstractModel located in ./System/Engine/AbstructModel.php does not comply with psr-4 autoloading standard. It will not autoload anymore in Composer v2.0. in phar:///usr/local/Cellar/composer/1.10.9/bin/composer/src/Composer/Autoload/ClassMapGenerator.php:201
// Deprecation Notice: Class Raichu\Engine\AbstractController located in ./System/Engine/AbstructController.php does not comply with psr-4 autoloading standard. It will not autoload anymore in Composer v2.0. in phar:///usr/local/Cellar/composer/1.10.9/bin/composer/src/Composer/Autoload/ClassMapGenerator.php:201
// Generated optimized autoload files containing 672 classes
// CRASH;

        $this->app->bind(\DispatcherTest::class);
        $this->dispatcher->forward(['controller' => \DispatcherTest::class]);

        // $this->dispatcher->forward(['action' => 'logger']);
        // $this->dispatcher->forward([
        //    'action' => 'shakehands',
        //    'params' => [$this->app->getRequest()],
        // ]);
        // die();

        /*
        $this->dispatcher->forward([
            'controller' => \World\Controller\WorldController::class,
            'action' => 'world',
            'params' => [$this->app->getRequest()],
        ]);

        $this->dispatcher->forward([
            'controller' => \Hello\Controller\HelloController::class,
            null,
            'params' => [$this->app->getRequest()],
        ]);

        $this->dispatcher->forward([
            'controller' => \World\Controller\WorldController::class,
            'action' => 'shakehands',
            null
        ]);
        */
    }


    /**
     * pass done
     * @return null|\Raichu\Engine\AbstractController
     */
    function initPtr()
    {
        $ptr = \Raichu\Engine\AbstractController::getInstance();
        $callback = function(\Raichu\Engine\Container $di) use (&$ptr) {
            return $ptr;
        };

        // $ptr =& \Raichu\Engine\AbstractController::getInstance();
        // echo $ptr->initialize();

        $this->Promise('beforeExecuteRoute');
        $this->Promise('afterExecuteRoute');

        $app = $callback(null);
        return $app;
    }

}

// $response = new \Raichu\Engine\Response();
// $response->abort(10080);

// print_r(\Raichu\Engine\AbstractController::getResponseDescription(10080));
// print_r(\Raichu\Engine\AbstractController::getResponseDescription(200));
// print_r(\Raichu\Engine\AbstractController::getResponseDescription());

$dispatcher = new DispatcherTest($app);
// $dispatcher->segments();
// $dispatcher->parseRoute();

// $dispatcher->eventDispatcher();
$dispatcher->forwardEvent();

// $dispatcher->enabled();
// $dispatcher->flashing();
// $dispatcher->catchError();

// $app = $dispatcher->initPtr();
// var_dump($app);
// $dispatcher->cleanObject();
;