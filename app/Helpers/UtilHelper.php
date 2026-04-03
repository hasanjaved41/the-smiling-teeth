<?php

namespace App\Helpers;
use Config;

class UtilHelper
{

    /**
     * to form 16 digits alphanumeric or numeric string
     * @author Anil Chatla <anil.chatla@kissht.com>
     * @param bool $onlyNumber To form numeric string
     * @return string
     */
    public static function generateString($onlyNumber = false)
    {
        $timestamp = (microtime(true) * 10000);
        $timestamp = str_replace("0", "8", $timestamp);
        $string = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
        $number = "123456789";

        $random_string = ($onlyNumber == true) ? str_shuffle($number) : str_shuffle($string);
        $unique_code = str_shuffle(substr($timestamp, 0, 4)) . substr($timestamp, 5, 8) . substr($random_string, 0, 4);

        return $unique_code;
    }

    /**
     * to convert number to upto 2 decimal number
     * @author Anil Chatla <anil.chatla@kissht.com>
     * @param double $float Double number
     * @param int $precision To change upto precesion number
     * @return double
     */
    public static function fixFloat($float, $precision = 2)
    {
        $float = (float) $float;
        $converted = number_format($float, $precision, ".", "");
        return (float) $converted;
    }


    /**
     * to get file path from aws s3 storage
     * @author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
     * @param $path
     * @param $expiry_time
     * @return string
     */
    public static function awsFilePath($path, $expiry_time)
    {
        $region = Config('constants.aws_default_region');
        $bucket = Config('constants.aws_bucket');
        $pathArray = explode('/',$path);
        if(in_array('kissht-efa-documents',$pathArray)){
            $bucket = 'kissht-efa-documents';
            $region = 'ap-south-1';
        }
        
        $path = str_replace('kissht-efa-documents','',$path);
        // get s3 objet and set expiry 
        $s3 = \Storage::disk('s3');
        $client = $s3->getDriver()->getAdapter()->getClient();
        $expiry = "+".$expiry_time." minutes";
        // set command usig the file path
        $command = $client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $path,
            'region'  => $region
        ]);

        // get pre signed request path 
        $request = $client->createPresignedRequest($command, $expiry);
        return (string) $request->getUri();
    }

    public static function arrayMergeIfNotNull($arr1, $arr2)
    {
        foreach ($arr2 as $key => $val) {
            $is_set_and_not_null = isset($arr1[$key]);
            if ($val === NULL && $is_set_and_not_null) {
                $arr2[$key] = $arr1[$key];
            }
        }
        return array_merge($arr1, $arr2);
    }
}
