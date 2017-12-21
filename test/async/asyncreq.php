<?php

    class Log
    {
        const ERR  = 'error';
        const INFO = 'info';
        public static function record($message, $level)
        {
            $timestamp = date('Y-m-d H:i:s');
            return file_put_contents('./logs.log', "[$timestamp]".$message.PHP_EOL, FILE_APPEND);
        }
    }


    function testAsyncRequest()
    {
        $body   = '{"mobile":"15222820651","company":"\u6d4b\u8bd5\u4f01\u4e1a","department":"IT","email":"zhangxinglong55@mailinator.com","password":"111111","from":0,"uid":1000377,"oid":264}';
        $length = strlen($body);

// 【注意】此处的换行符并非是\r\n，在高版本(2.4.23以上)的Apache下会被认为是非法请求
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
        $end  = "\r\n";
        $req  = [
            'POST /dotask.php HTTP/1.1',
            'Host: localhost',
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
        Log::record('async req is:'.PHP_EOL.$req, Log::INFO);

        $fp = fsockopen('localhost', 80, $errno, $errstr, 30);
        if (!$fp) {
            Log::record("fsockopen throw error:[$errno]$errstr", Log::ERR);
            return false;
        }
        if (fwrite($fp, $req) === FALSE) {
            Log::record("fwrite throw error:[$errno]$errstr", Log::ERR);
            return false;
        }
        // Log::record('fsockopen result:', Log::INFO);
        // while (!feof($fp)) {
        //     $ret = fgets($fp, 1024);
        //     Log::record($ret, Log::INFO);
        //     // break;
        // }
        fclose($fp);
        return true;
    }

    testAsyncRequest();