#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Raichu\Provider\Async\Task;
use Raichu\Provider\Async\Schedule;
use Raichu\Provider\Async\SysCall;
use Raichu\Provider\Async\CoroutineReturnValue;
date_default_timezone_set('Asia/Shanghai');

/*
$options = include __DIR__.'/config/database.php';
foreach ($options as $name => $option) {
    \ORM::configure($option, null, $name);
}
*/

$s = new Schedule();
$s->start((new AsyncMiddleware())->async(new Application()));