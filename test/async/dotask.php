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

Log::record('do task...message:'.file_get_contents('php://input'), Log::INFO);
?>