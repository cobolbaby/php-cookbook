<?php

define('APP_PATH', dirname(dirname(__FILE__)));
define('LOG_PATH', APP_PATH . '/logs');
define('VENDOR_PATH', APP_PATH . '/vendor');
define('RUN_MODE', 'development');

/**
 * 加载第三方库 via composer autoload.
 */
require VENDOR_PATH . '/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$loglevel = defined('RUN_MODE') && RUN_MODE === 'production' ? Logger::NOTICE : Logger::DEBUG;
$logger   = new Logger('cobolphp');
$logger->pushHandler(new StreamHandler(LOG_PATH . '/access.log', $loglevel));

// 非阻塞请求
$request  = new \cobolphp\Request($logger);
$request->asyncRequest('http://shujuguan.cn');

// Promise

