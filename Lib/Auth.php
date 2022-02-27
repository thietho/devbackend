<?php

namespace Lib;
class Auth
{
    private $db;
    private $session;
    private $date;
    private $string;
    private $tokenexpire = 24 * 60 * 60;
    public $userInfor = array(
        'id' => 0,
        'username' => '',
        'fullname' => '',
        'group' => 0,
        'groupname' => '',
        'avatar' => '',
    );
    public $routeallow = array(
        'Core/Auth'
    );

    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->session = new Session();
        $this->userInfor = $this->session->get('login');
        $this->date = new Date();
        $this->string = new ObjString();
    }

    public function login($username, $password)
    {
        $username = $this->db->escape($username);
        $password = $this->db->escape($password);
        $sql = "SELECT core_user.*,groupname FROM `core_user` INNER JOIN core_usergroup ON core_user.group = core_usergroup.id 
            WHERE username LIKE '$username'";
        $query = $this->db->query($sql);
        $user = $query->row;
        if (!empty($user)) {
            $pass = $this->encryptionPassword($password);
            if ($pass == $user['password']) {
                return json_encode(array(
                    'statuscode' => 1,
                    'text' => 'Login success',
                    'data' => $this->updateUserInfor($user)
                ));
            } else {
                return json_encode(array(
                    'statuscode' => 0,
                    'text' => 'Password is not correct',
                    'data' => array()
                ));
            }
        } else {
            return json_encode(array(
                'statuscode' => 0,
                'text' => 'User is not exist!',
                'data' => array()
            ));
        }
    }

    public function updateUserInfor($user)
    {
        $data = array(
            'id' => $user['id'],
            'username' => $user['username'],
            'fullname' => $user['fullname'],
            'group' => $user['group'],
            'groupname' => $user['groupname'],
            'avatar' => $user['avatar'],
        );
        $this->session->set('login', $data);
        return $data;
    }

    public function loginApi($username, $password)
    {
        $username = $this->db->escape($username);
        $password = $this->db->escape($password);
        $sql = "SELECT `core_user`.*,groupname FROM `core_user` INNER JOIN core_usergroup ON core_user.group = core_usergroup.id 
            WHERE username LIKE '$username'";
        $query = $this->db->query($sql);
        $user = $query->row;
        if (!empty($user)) {
            $pass = $this->encryptionPassword($password);
            if ($pass == $user['password']) {
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
                return json_encode(array(
                    'statuscode' => 1,
                    'text' => 'Login success',
                    'data' => $this->userInfor
                ));
            } else {
                return json_encode(array(
                    'statuscode' => 0,
                    'text' => 'Password is not correct',
                    'data' => array()
                ));
            }
        } else {
            return json_encode(array(
                'statuscode' => -1,
                'text' => 'User is not exist!',
                'data' => array()
            ));
        }
    }

    public function loginByToken($token)
    {
        $token = $this->db->escape($token);
        $sql = "SELECT `core_user`.*,groupname FROM `core_user` INNER JOIN core_usergroup ON core_user.group = core_usergroup.id 
            WHERE token LIKE '$token'";
        $query = $this->db->query($sql);
        $user = $query->row;
        if (!empty($user)) {
            $tokenexpire = $user['tokenexpire'];
            //if(time() - $tokenexpire < $this->tokenexpire){
            $this->userInfor = array(
                'id' => $user['id'],
                'username' => $user['username'],
                'fullname' => $user['fullname'],
                'group' => $user['group'],
                'groupname ' => $user['groupname'],
                'avatar' => $user['avatar']
            );
            return json_encode(array(
                'statuscode' => 1,
                'text' => 'Login success',
                'data' => $this->userInfor
            ));
            // }else{
            //     return  json_encode(array(
            //         'statuscode' => 2,
            //         'text' => 'Token is expired!',
            //         'data' => array()
            //     ));
            // }

        } else {
            return json_encode(array(
                'statuscode' => 0,
                'text' => 'User is not exist!',
                'data' => array()
            ));
        }
    }

    public function genToken($userid)
    {
        $userid = $this->db->escape($userid);
        $token = $this->encryptionPassword(time());
        $sql = "UPDATE `core_user` SET `token`= '$token' ,`tokenexpire`= '" . time() . "' WHERE id=" . $userid;
        $this->db->query($sql);
        return $token;
    }

    public function encryptionPassword($password)
    {
        return $this->db->escape(hash('sha512', md5($password)));
    }

    public function logout()
    {
        $this->session->reset();
    }

    public function getAllowMenu()
    {
        $arrMenuId = array();
        if (empty($this->userInfor))
            return array();
        if ($this->userInfor['group'] == 1) {
            $sql = "SELECT * FROM `core_menu` WHERE deletedby = 0";
            $query = $this->db->query($sql);
            $sitemaps = $query->rows;
            foreach ($sitemaps as $sitemap) {
                $arrMenuId[] = $sitemap['id'];
            }
        } else {
            $sql = "SELECT * FROM `core_usergroup` WHERE `id` = " . $this->userInfor['group'];
            $query = $this->db->query($sql);
            $group = $query->row;
            $permission = $this->string->stringToArray($group['permission']);
            foreach ($permission as $menuid) {
                if ((int)$menuid) {
                    $arrMenuId[] = $menuid;
                }
            }
        }

        return $arrMenuId;
    }

    private function getEntityPermission()
    {
        $sql = "SELECT * FROM `core_entity_permission` WHERE `deletedby`= 0 AND `groupid` = " . $this->userInfor['group'];
        $query = $this->db->query($sql);
        $entityPermission = $query->rows;
        return $entityPermission;
    }

    private function getEntity($path, $classname)
    {
        $path = $this->db->escape($path);
        $classname = $this->db->escape($classname);
        $sql = "Select *
                from `core_entity`
				where `deletedby`= 0 AND entitytype ='$path' AND classname = '$classname'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function checkEntityPermission($path, $classname, $method)
    {
        if (isset($this->userInfor['group']) && $this->userInfor['group'] == 1) {
            return 1;
        }
        $entity = $this->getEntity($path, $classname);
        if (!empty($entity)) {
            $entityPermission = $this->getEntityPermission();
            foreach ($entityPermission as $permission) {
                if ($entity['id'] == $permission['entityid']) {
                    switch ($method) {
                        case '':
                        case 'View':
                            return $permission['access'];
                            break;
                        case 'Insert':
                            return $permission['create'];
                            break;
                        case 'Edit':
                            return $permission['edit'];
                            break;
                        case 'Delete':
                            return $permission['delete'];
                            break;
                    }
                }
            }
        } else {
            return 1;
        }
        return 1;
    }
}