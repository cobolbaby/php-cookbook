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
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

$loglevel = defined('RUN_MODE') && RUN_MODE === 'production' ? Logger::NOTICE : Logger::DEBUG;
$logger   = new Logger('cobolphp');
$logger->pushHandler(new StreamHandler(LOG_PATH . '/access.log', $loglevel));

// 1. non-blocking Request
$request  = new \cobolphp\Request($logger);
$request->asyncRequest('http://localhost:8008');

// 2. Promise
$handlerStack = HandlerStack::create();
$handlerStack->push(Middleware::log($logger, new MessageFormatter(MessageFormatter::DEBUG)));

$client = new Client(['handler' => $handlerStack]);
$promises = [];
for ($i = 0; $i < 10; ++$i) {
    $promise = $client->requestAsync(
        'GET',
        'http://localhost:8008'
    );
    $promise->then(
        function (ResponseInterface $response) {
            // $response->getBody();
        },
        function (RequestException $e) {
            echo '[' . $e->getMessage() . ']' . $e->getRequest()->getMethod();
            exit(1);
        }
    );
    $promises[] = $promise;
}
Promise\all($promises)->wait();
