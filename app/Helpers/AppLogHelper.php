<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class AppLogHelper
{

    /**
     * Unique request Id
     * @var string
     */
    private static $requestId;

    /**
     * Channel configuration for applog
     * @var string
     */
    private static $channel;

    public static function debug($className, $methodName, $description, $data = null)
    {
        Log::channel(self::$channel)->debug(self::$requestId . '|' . $className . '|' . $methodName . '|' . $description, (array)$data);
    }

    public static function info($className, $methodName, $description, $data = null)
    {
        Log::channel(self::$channel)->info(self::$requestId . '|' . $className . '|' . $methodName . '|' . $description, (array)$data);
    }

    public static function notice($className, $methodName, $description, $data = null)
    {
        Log::channel(self::$channel)->notice(self::$requestId . '|' . $className . '|' . $methodName . '|' . $description, (array)$data);
    }

    public static function warning($className, $methodName, $description, $data = null)
    {
        Log::channel(self::$channel)->warning(self::$requestId . '|' . $className . '|' . $methodName . '|' . $description, (array)$data);
    }

    public static function error($className, $methodName, $description, $data = null)
    {
        Log::channel(self::$channel)->error(self::$requestId . '|' . $className . '|' . $methodName . '|' . $description, (array)$data);
    }

    public static function critical($className, $methodName, $description, $data = null)
    {
        Log::channel(self::$channel)->critical(self::$requestId . '|' . $className . '|' . $methodName . '|' . $description, (array)$data);
    }

    public static function alert($className, $methodName, $description, $data = null)
    {
        Log::channel(self::$channel)->alert(self::$requestId . '|' . $className . '|' . $methodName . '|' . $description, (array)$data);
    }

    public static function emergency($className, $methodName, $description, $data = null)
    {
        Log::channel(self::$channel)->emergency(self::$requestId . '|' . $className . '|' . $methodName . '|' . $description, (array)$data);
    }

    /**
     * to set requestId for logs identifier
     * @param string $requestId Request Id for Logs
     * @return void
     * @author Anil Chatla <anil.chatla@kissht.com>
     */
    public static function setRequestId($requestId)
    {
        self::$channel = config('constants.app_log_channel');
        self::$requestId = $requestId;
    }

    /**
     * to get requestId
     * @return string
     * @author Anil Chatla <anil.chatla@kissht.com>
     */
    public static function getRequestId()
    {
        return self::$requestId;
    }

}
