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

    private $key;
    private $environment;
    private $domain;

    /**
     * Log constructor.
     * takes $key generated in the united-logs, $environment for better filtering different modes: development, testing, production
     * optional $domain. if you have united-logs on your server, please specify domain or ip. Else default domain will be used.
     *
     * @param $key string
     * @param $environment string
     * @param $domain string|null
     * @throws Exception
     */
    public function __construct($key, $environment, $domain = null)
    {
        if (!$key) {
            throw new Exception('API Key not specified');
        }
        if (!$environment) {
            throw new Exception('Environment not specified');
        }
        $this->key = $key;
        $this->environment = $environment;
        if ($domain === null && !isset($_ENV[self::DOMAIN_KEY_IN_ENV])){
            throw new Exception('Please put the domain in global environment variable with key "'.self::DOMAIN_KEY_IN_ENV.'" or specify it when constructing '.static::class.' object.');
        }
        $this->domain = $domain ? ($domain . '/api/v1/log') : $_ENV[self::DOMAIN_KEY_IN_ENV].'/api/v1/log';
    }


    /**
     * @param $message string
     * @param $category string
     * @param $params string|null
     */
    public function error($message, $category, $params = null)
    {
        $this->sendLog($this->domain . '/error', $message, $category, $params);
    }

    /**
     * @param $message string
     * @param $category string
     * @param $params string|null
     */
    public function success($message, $category, $params = null)
    {
        $this->sendLog($this->domain . '/success', $message, $category, $params);
    }


    /**
     * @param $message string
     * @param $category string
     * @param $params string|null
     */
    public function info($message, $category, $params = null)
    {
        $this->sendLog($this->domain . '/info', $message, $category, $params);
    }


    /**
     * @param $message string
     * @param $category string
     * @param $params string|null
     */
    public function warning($message, $category, $params = null)
    {
        $this->sendLog($this->domain . '/warning', $message, $category, $params);
    }


    /**
     * @param $url
     * @param $message
     * @param $category
     * @param $params
     */
    private function sendLog($url, $message, $category, $params)
    {
        $data = array(
            'api' => $this->key,
            'environment' => $this->environment,
            'message' => $message,
            'category' => $category,
            'params' => $params
        );

        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) {
            /* Handle error */
        }
        var_dump($result);
    }

}