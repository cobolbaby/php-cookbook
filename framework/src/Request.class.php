<?php
namespace cobolphp;

/**
 *
 */
class Request
{
    protected $logger;

    public function __construct($logger = '')
    {
        $this->logger = $logger;
    }

    /**
     * 以下该方法仅支持GET/POST方式的HTTP请求
     */
    public function asyncRequest($url, $method = null, $body = null, array $headers = [])
    {
        if (!function_exists('fsockopen')) {
            $this->logger->error('fsockopen function not exists');
            return false;
        }

        $matches = parse_url($url);
        !isset($matches['host']) && $matches['host'] = 'localhost';
        !isset($matches['port']) && $matches['port'] = 80;
        !isset($matches['path']) && $matches['path'] = '';
        !isset($matches['query']) && $matches['query'] = '';
        $server = $matches['host'];
        $port   = $matches['port'];
        $host   = $port === 80 ? $server : $server .':'. $port; // [fix]请求头信息Host中没有端口的问题
        $path   = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
        if ($body) {
            $length = strlen($body);

// Your browser (or proxy) sent a request that this server could not understand.
//         $req  = <<<HEADER
// POST /task.php HTTP/1.1
// Host: localhost
// User-Agent: curl/7.29.0
// Content-Type: application/json; charset=UTF-8
// Content-Length: {$length}
// Accept: */*
// Accept-Encoding: gzip, deflate
// Cache-Control: no-cache
// Connection: Close

// {$body}
// HEADER;

            // 因上面的拼接方式有兼容问题，所以改为以下方式
            $end  = "\r\n"; // 此处只能为\r\n，不能采用PHP_EOL，并且必须为双引号
            $req  = [
                'POST ' . $path . ' HTTP/1.1',
                'Host: ' . $host,
                'Content-Type: application/json; charset=UTF-8',
                'Content-Length: ' . $length,
                'Accept: */*',
                'Accept-Language: zh-CN',
                'Accept-Encoding: gzip, deflate',
                'Cache-Control: no-cache',
                'Connection: Close'
            ];
            $req  = implode($end, $req) . $end . $end;
            $req .= $body;
        } else {
            $end  = "\r\n"; // 此处只能为\r\n，不能采用PHP_EOL，并且必须为双引号
            $req  = [
                'GET ' . $path . ' HTTP/1.1',
                'Host: ' . $host,
                'Accept: */*',
                'Accept-Language: zh-CN',
                'Accept-Encoding: gzip, deflate',
                'Cache-Control: no-cache',
                'Connection: Close'
            ];
            $req  = implode($end, $req) . $end . $end;
        }
        $this->logger->info('Request is:' . PHP_EOL . $req);

        $fp = fsockopen($server, $port, $errno, $errstr, 5);
        if (!$fp) {
            $this->logger->error("fsockopen error: [{$errno}]{$errstr}");
            return false;
        }
        fwrite($fp, $req);
        // while (!feof($fp)) {
        //     $ret = fgets($fp, 1024);
        //     break;
        // }
        fclose($fp);
        return true;
    }
}
