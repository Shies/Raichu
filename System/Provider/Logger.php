<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) ____Shies <gukai@bilibili.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Raichu\Provider;
use \Exception;

/**
 * Monolog log channel
 *
 * It contains a stack of Handlers and a stack of Processors,
 * and uses them to store records that are added to it.
 *
 * @author ____Shies <gukai@bilibili.com>
 */
class Logger
{
    /**
     * Detailed debug information
     */
    const DEBUG = 7;

    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    const INFO = 6;

    /**
     * Uncommon events
     */
    const NOTICE = 5;

    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 4;

    /**
     * Runtime errors
     */
    const ERROR = 3;

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 2;

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 1;

    /**
     * Urgent alert.
     */
    const EMERGENCY = 0;

    /**
     * Logging levels from syslog protocol defined in RFC 5424
     *
     * @var array $levels Logging levels
     */
    protected static $levels = array(
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    );


    /**
     * @var \$this
     */
    protected static $instance;


    /**
     * @var string $localPath
     */
    protected static $localConfig;


    /**
     * @var array
     */
    protected $processors = [];


    /**
     * handle done. flag=true|false
     * @var bool
     */
    protected $handle;



    /**
     * @return mixed
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }


    /**
     * Adds a processor on to the stack.
     *
     * @param  callable $callback
     * @return $this
     */
    public function pushProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Processors must be valid callables (callback or object with an __invoke method), '.var_export($callback, true).' given');
        }

        array_unshift($this->processors, $callback);
        return $this;
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popProcessor()
    {
        if (!$this->processors) {
            throw new Exception('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processors);
    }

    /**
     * @return callable[]
     */
    public function getProcessors()
    {
        return $this->processors;
    }


    /**
     * 多线程同时写入文件
     *
     * @param $data
     * @param $path
     * @return bool false|int
     */
    private function fileLock($data, $path)
    {
        $handle = fopen($path, 'ab');
        while (!flock($handle, LOCK_EX)) {
            usleep(100);
        }

        $writed = fwrite($handle, $data . PHP_EOL);
        flock($handle, LOCK_UN);
        fclose($handle);

        return $writed;
    }


    /**
     * 转换信息格式
     *
     * @param $message
     * @return string
     */
    private function convert($message)
    {
        if (E_ERROR == error_get_last()['type']) {
            list($show, $log) = $this->debug_backtrace();
            $message = "Error：".$message.PHP_EOL.$log;
        }

        if (!is_string($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        return $message;
    }


    /**
     * show error and show msg
     * @return array
     */
    private function debug_backtrace()
    {
        // show
        $return = [
            null, // msg
            null, // error
        ];

        $debug_backtrace = debug_backtrace();
        krsort($debug_backtrace);
        foreach ($debug_backtrace AS $error) {
            $this->backtrace($return, $error);
        }

        return $return;
    }


    /**
     * @param $return
     * @param $error
     */
    private function backtrace(&$return, $error)
    {
        // error chain
        $trace = '';
        $msgs = '';
        $errs = '';

        // before replace error[file] is path of ROOT(/Config/defined.php);
        $file = str_replace(ROOT, '', $error['file']);

        $trace = isset($error['class']) ? $error['class'] : '';
        $trace .= isset($error['type']) ? $error['type'] : '';
        $trace .= isset($error['function']) ? $error['function'] : '';
        if (empty($trace)) {
            return true;
        }

        reset($return);
        foreach ($return AS &$undefined) {
            if (isset($return[0])) {
                $msgs = $file.'('.$trace.') => '.$error['line'].PHP_EOL;
                $undefined = $msgs;
            } else {
                $errs = !empty($return[1]) ? ' ' : '';
                $errs .= $file.'：'.$error['line'].PHP_EOL;
                $undefined = $errs;
            }
        }

        return !false;
    }


    /**
     * 配置全局路径
     *
     * @param null $path
     * @param null|string $localPath
     */
    private function configure($path = null)
    {
        if (!$path = static::$localConfig) {
            $path = '/data/logs/Raichu.log';
        }

        return $path;
    }


    /**
     * Adds a log record.
     *
     * @param  int     $level   The logging level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = array())
    {
        $path = $this->configure(null);

        $this->pushProcessor(function($level, $msg, $path) {
            $msg = $this->convert($msg);
            // syslog($level, $msg);
            return $this->fileLock("(".date('Y-m-d H:i:s').")=> ".$msg, $path);
        });

        $this->handle = true;
        while ($this->getProcessors()) {
            $pop = $this->popProcessor();
            if (!($pop instanceof \Closure)) {
                return false;
            }

            if (is_callable($pop)) {
                call_user_func($pop, $level, $message, $path);
                continue;
            }
        }

        return ($this->handle);
    }


    /**
     * 处理系统错误以及异常
     * @param \Raichu\Engine\App $app
     *
     * global $app
     * $app->handleError
     */
    public function handleError($errno, $errmsg, $errfile, $errline)
    {
        // temp pattern
        // can rewrite
        return $this->addError($errmsg);
    }


    /**
     * Adds a log record at the DEBUG level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addDebug($message, array $context = array())
    {
        return $this->addRecord(static::DEBUG, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addInfo($message, array $context = array())
    {
        return $this->addRecord(static::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addNotice($message, array $context = array())
    {
        return $this->addRecord(static::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addWarning($message, array $context = array())
    {
        return $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addError($message, array $context = array())
    {
        return $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addCritical($message, array $context = array())
    {
        return $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addAlert($message, array $context = array())
    {
        return $this->addRecord(static::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addEmergency($message, array $context = array())
    {
        return $this->addRecord(static::EMERGENCY, $message, $context);
    }

    /**
     * Gets all supported logging levels.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getLevels()
    {
        return array_flip(static::$levels);
    }

    /**
     * Gets the name of the logging level.
     *
     * @param  int    $level
     * @return string
     */
    public static function getLevelName($level)
    {
        if (!isset(static::$levels[$level])) {
            throw new Exception('Level "'.$level.'" is not defined, use one of: '.implode(', ', array_keys(static::$levels)));
        }

        return static::$levels[$level];
    }

    /**
     * Converts PSR-3 levels to Monolog ones if necessary
     *
     * @param string|int Level number (monolog) or name (PSR-3)
     * @return int
     */
    public static function toMonologLevel($level)
    {
        if (is_string($level) && defined(__CLASS__.'::'.strtoupper($level))) {
            return constant(__CLASS__.'::'.strtoupper($level));
        }

        return $level;
    }


    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  mixed   $level   The log level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function log($level, $message, array $context = array())
    {
        $level = static::toMonologLevel($level);

        return $this->addRecord($level, $message, $context);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function debug($message, array $context = array())
    {
        return $this->addRecord(static::DEBUG, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function info($message, array $context = array())
    {
        return $this->addRecord(static::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function notice($message, array $context = array())
    {
        return $this->addRecord(static::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function warn($message, array $context = array())
    {
        return $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function warning($message, array $context = array())
    {
        return $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function err($message, array $context = array())
    {
        return $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function error($message, array $context = array())
    {
        return $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function crit($message, array $context = array())
    {
        return $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function critical($message, array $context = array())
    {
        return $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function alert($message, array $context = array())
    {
        return $this->addRecord(static::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function emerg($message, array $context = array())
    {
        return $this->addRecord(static::EMERGENCY, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function emergency($message, array $context = array())
    {
        return $this->addRecord(static::EMERGENCY, $message, $context);
    }
}
