<?php

namespace Lib;


class Request
{
    private $dataGet = array();
    private $dataPost = array();
    public $method;

    public function __construct()
    {
        if(!empty($_GET)){
            foreach ($_GET as &$value){
                if(is_string($value)){
                    $value = htmlentities($value,ENT_IGNORE,'cp866');
                }
            }
        }
        if (!empty($_GET)) {
            $this->dataGet = $_GET;
        }
        if (!empty($_POST)) {
            $this->dataPost = $_POST;
        }else{
            $this->dataPost = json_decode(file_get_contents('php://input'), true);
        }
        $this->method = isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'job';

    }

    private function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * get access token from header
     * */
    public function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }else{
            $requestHeaders = apache_request_headers();
            if(isset($requestHeaders['Access-Token'])){
                return $requestHeaders['Access-Token'];
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->dataGet[$key]))
            return $this->dataGet[$key];
        else {
            return '';
        }
    }

    /**
     * @return mixed
     */
    public function post($key)
    {
        if (isset($this->dataPost[$key]))
            return $this->dataPost[$key];
        else {
            return '';
        }
    }

    /**
     * @return array
     */
    public function getDataPost()
    {
        return $this->dataPost;
    }

    /**
     * @return array
     */
    public function getDataGet()
    {
        return $this->dataGet;
    }

    public function getQueryString()
    {
        return urldecode(http_build_query($this->dataGet));
    }

}