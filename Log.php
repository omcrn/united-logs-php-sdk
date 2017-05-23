<?php

namespace omcrn\unitedlogs;

use Exception;

/**
 * Created by PhpStorm.
 * User: guga
 * Date: 5/8/17
 * Time: 11:16 AM
 */
class Log
{
    const DOMAIN_KEY_IN_ENV = 'UNITED_LOGS_DOMAIN';

    private $levels = array('warning', 'info', 'error', 'success');
    private $key;
    private $environment;
    private $domain;

    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_INFO = 'info';
    const LEVEL_SUCCESS = 'success';

    /**
     * Log constructor. Initializes logger object.
     *
     *
     * @param string $key Project key. 64 bit length string associated to project
     * @param string $environment For better filtering different modes: development, testing, production. You can put any text you want
     * @param array|null $levels
     * @param string|null $domain The endpoint of United Logs server. If you have it on your server, please specify domain or ip.
     *                              You can put the domain in $_ENV['UNITED_LOGS_DOMAIN'] and not specify here
     * @throws Exception
     */
    public function __construct($key, $environment, $levels = null, $domain = null)
    {
        if (!$key) {
            throw new Exception('API Key not specified');
        }
        if (!$environment) {
            throw new Exception('Environment not specified');
        }
        $this->key = $key;
        $this->environment = $environment;

        if ($levels !== null){
            $this->levels = $levels;
        }

        if ($domain === null && !isset($_ENV[self::DOMAIN_KEY_IN_ENV])){
            throw new Exception('Please put the domain in global $_ENV variable with key "'.self::DOMAIN_KEY_IN_ENV.'" or specify it when constructing '.get_class($this).' object.');
        }
        $this->domain = $domain ? ($domain . '/api/v1/log') : $_ENV[self::DOMAIN_KEY_IN_ENV].'/api/v1/log';
    }


    /**
     * Write error log. It accepts message, category and optional params.
     * Return whether or not the log has been successfully written.
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param string $message
     * @param string $category
     * @param array|null $params
     * @return bool
     */
    public function error($message, $category, $params = null)
    {
        return $this->sendLog(self::LEVEL_ERROR, $message, $category, $params);
    }

    /**
     * Write success log. It accepts message, category and optional params.
     * Return whether or not the log has been successfully written.
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param string $message
     * @param string $category
     * @param array|null $params
     * @return bool
     */
    public function success($message, $category, $params = null)
    {
        return $this->sendLog(self::LEVEL_SUCCESS, $message, $category, $params);
    }


    /**
     * Write info log. It accepts message, category and optional params.
     * Return whether or not the log has been successfully written.
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param string $message
     * @param string $category
     * @param array|null $params
     * @return bool
     */
    public function info($message, $category, $params = null)
    {
        return $this->sendLog(self::LEVEL_INFO, $message, $category, $params);
    }


    /**
     * Write warning log. It accepts message, category and optional params.
     * Return whether or not the log has been successfully written.
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param string $message
     * @param string $category
     * @param array|null $params
     * @return bool
     */
    public function warning($message, $category, $params = null)
    {
        return $this->sendLog(self::LEVEL_WARNING, $message, $category, $params);
    }

    /**
     * Send log message
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param string $level
     * @param string $message
     * @param string $category
     * @param array $params
     * @return bool
     */
    private function sendLog($level, $message, $category, $params)
    {
        if (!in_array($level, array(self::LEVEL_WARNING, self::LEVEL_INFO, self::LEVEL_SUCCESS, self::LEVEL_ERROR))){
            return false;
        }

        $data = array(
            'api' => $this->key,
            'environment' => $this->environment,
            'message' => $message,
            'category' => $category,
            'params' => $params
        );

        // create a new cURL resource
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $this->domain.'/'.$level);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HEADER, "Content-type: application/x-www-form-urlencoded");

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        // close cURL resource, and free up system resources
        curl_close($ch);

        $result = json_decode($server_output, true);
        return $result['success'];
    }

}