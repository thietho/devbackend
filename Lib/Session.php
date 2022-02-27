<?php
namespace Lib;
class Session
{
    private $data = array();
    public function __construct()
    {
        $this->data = $_SESSION;
    }
    public function get($key){
        if(isset($this->data[$key])){
            return $this->data[$key];
        }else{
            return null;
        }
    }
    public function set($key,$value){
        $_SESSION[$key] = $value;
        $this->data = $_SESSION;
    }
    public function remove($key){
        unset($_SESSION[$key]);
        $this->data = $_SESSION;
    }
    public function reset(){
        session_destroy();
        $this->data = array();
    }
}