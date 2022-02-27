<?php
namespace Lib;
class Worker
{
    private $db;
    public $userInfor = array(
        'id' => 0,
        'username' => '',
        'fullname' => '',
        'group' => 0,
        'groupname' => '',
        'avatar' => '',
    );
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }
    public function encryptionPassword($password){
        return $this->db->escape(hash('sha512',md5($password)));
    }
    public function login($username,$password){
        $sql = "SELECT `core_user`.*,groupname FROM `core_user` INNER JOIN core_usergroup ON core_user.group = core_usergroup.id 
            WHERE username LIKE '$username'";
        $query = $this->db->query($sql);
        $user = $query->row;
        if(!empty($user)){
            $pass = $this->encryptionPassword($password);
            if($pass == $user['password']){
                // $tokenexpire = $user['tokenexpire'];
                // if(time() - $tokenexpire > $this->tokenexpire){
                //     $this->genToken($user['id']);
                // }
                $this->userInfor = array(
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'fullname' => $user['fullname'],
                    'group' => $user['group'],
                    'groupname ' => $user['groupname'],
                    'avatar' => $user['avatar'],
                    'token' => $user['token'],
                    'tokenexpire' => $user['tokenexpire']
                );
                return  json_encode(array(
                    'statuscode' => 1,
                    'text' => 'Login success',
                    'data' => $this->userInfor
                ));
            }else{
                return  json_encode(array(
                    'statuscode' => 0,
                    'text' => 'Password is not correct',
                    'data' => array()
                ));
            }
        }else{
            return  json_encode(array(
                'statuscode' => -1,
                'text' => 'User is not exist!',
                'data' => array()
            ));
        }
    }
}