<?php
namespace app\controller;

class AppBase
{
    public function __construct()
    {
        // load core lib
        // load_config()
        // load vendor lib
        // receive request;
        // register the middleware(hook)
        // register the shutdown func
        // parse the request
        // route
        // call_user_func_array()
    }
    
    /**
     * 检查请求头中的设备信息
     *
     * @param {string} data 请求头信息
     */
    protected static function checkDevice($data, $skip_pin = false)
    {
        if (empty($data)) {
            return false;
        }

        $fields = [
            'pin',
            'did', // 设备唯一识别符
            'device_type',
            // 'os_version',
            'app_version', // app版本号
            'app_build', // app build号
            // 'screen_width',
            // 'screen_height',
            'network_type',
            'timestamp',
        ];
        $skip_pin && array_splice($fields, 0, 1);

        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                \Think\Log::write('field:'. $field, \Think\Log::ERR);
                return false;
            }
        }

        if (!in_array(strtolower($data['device_type']), C('DEVICE_TYPES'))) {
            \Think\Log::write('device_type:'. $data['device_type'], \Think\Log::ERR);
            return false;
        }

        if (!in_array(strtolower($data['network_type']), C('NETWORK_TYPES'))) {
            \Think\Log::write('network_type:'. $data['network_type'], \Think\Log::ERR);
            return false;
        }

        // if (false) { // 之后的签名校验需要迁移至Startpoint
        if (!self::checkSign($data)) {
            \Think\Log::write('signature error:'. json_encode($data), \Think\Log::ERR);
            return false;
        }

        return true;
    }

    /**
     * 检查请求头中的设备信息
     *
     * @param {string} data 请求头信息
     */
    protected static function checkSign($data)
    {
        $device_type = $data['device_type'];
        $func = 'checkSign' . ucfirst(strtolower($device_type));

        return self::$func($data);
    }

    private static function checkSignIphone($data)
    {
        if ($data['app_build'] < 10) {
            return self::checkSignV0($data);
        }
        return self::checkSignV1($data);
    }

    private static function checkSignIpad($data)
    {
        return self::checkSignV1($data);
    }

    private static function checkSignV0($data)
    {
        return true;
    }

    private static function checkSignV1($data)
    {
        $key  = C('DATA_AUTH_KEY');
        $sign = md5($key . $data['did'] . $key . $data['timestamp'] . $key);
        return $sign === $data['sign'];
    }

    /**
     * TODO::待完善
     * 
     */
    private static function checkSignV2($data)
    {
        return true;
    }


}