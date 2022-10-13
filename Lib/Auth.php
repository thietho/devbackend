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
        'phone' => '',
        'email' => '',
        'group' => 0,
        'groupname' => '',
        'partnerid' => 0,
        'homepage' => '',
        'roleid' => 0,
        'rolename' => '',
        'allow' => '',
        'roleidchild' => [],
        'avatar' => '',
    );
    public $routeallow = array(
        'Core/Auth'
    );
    public $methodallow = array(
        'download'
    );

    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->session = new Session();
        $this->userInfor = $this->session->get('login');
        $this->date = new Date();
        $this->string = new ObjString();
        if($this->userInfor == null){
            $this->userInfor = array(
                'id' => 0,
                'username' => '',
                'fullname' => '',
                'phone' => '',
                'email' => '',
                'group' => 0,
                'groupname' => '',
                'partnerid' => 0,
                'homepage' => '',
                'roleid' => 0,
                'rolename' => '',
                'allow' => '',
                'roleidchild' => [],
                'avatar' => '',
            );
        }

    }

    public function login($username, $password)
    {
        $username = $this->db->escape($username);
        $password = $this->db->escape($password);
        $sql = "SELECT core_user.*,groupname,homepage FROM `core_user` INNER JOIN core_usergroup ON core_user.group = core_usergroup.id 
            WHERE username LIKE '$username' AND core_user.deletedby = 0 ORDER BY id DESC";
        $query = $this->db->query($sql);
        $user = $query->row;
        if (!empty($user)) {
            if(intval($user['active'])){
                $pass = $this->encryptionPassword($password);
                if ($pass == $user['password']) {
                    return json_encode(array(
                        'statuscode' => 1,
                        'text' => 'Đăng nhập thành công',
                        'data' => $this->updateUserInfor($user)
                    ));
                } else {
                    return json_encode(array(
                        'statuscode' => 0,
                        'text' => 'Mật khẩu không đúng!',
                        'data' => array()
                    ));
                }
            }else{
                return json_encode(array(
                    'statuscode' => 0,
                    'text' => 'Tài khoản bị khóa',
                    'data' => array()
                ));
            }

        } else {
            return json_encode(array(
                'statuscode' => 0,
                'text' => 'Tài khoản không tồn tại',
                'data' => array()
            ));
        }
    }

    public function updateUserInfor($user)
    {
        $roleModel = new Entity('Core','Role');
        $roles = array();
        $rolename = '';
        $roleModel->travel($user['roleid'],$roles);
        if($user['group'] == 1 && $user['roleid'] == 0){
            $allow = 'all';
        }else{
            $role = $roleModel->getItem($user['roleid']);
            $allow = $role['allow'];
            $rolename = $role['rolename'];
        }

        $data = array(
            'id' => $user['id'],
            'username' => $user['username'],
            'fullname' => $user['fullname'],
            'phone' => $user['phone'],
            'email' => $user['email'],
            'group' => $user['group'],
            'partnerid' => $user['partnerid'],
            'roleid' => $user['roleid'],
            'rolename' => $rolename,
            'homepage' => $user['homepage'],
            'allow' => $allow,
            'roleidchild' => $this->string->matrixToArray($roles,'id'),
            'groupname' => $user['groupname'],
            'avatar' => $user['avatar'],
        );
        $this->userInfor = $data;
        $this->session->set('login', $data);
        return $data;
    }

    public function loginApi($username, $password)
    {
        $username = $this->db->escape($username);
        $password = $this->db->escape($password);
        $sql = "SELECT `core_user`.*,groupname,homepage FROM `core_user` INNER JOIN core_usergroup ON core_user.group = core_usergroup.id 
            WHERE `core_user`.deletedby = 0 AND  username LIKE '$username'";
        $query = $this->db->query($sql);
        $user = $query->row;
        if (!empty($user)) {
            $pass = $this->encryptionPassword($password);
            if ($pass == $user['password']) {
//                 $tokenexpire = $user['tokenexpire'];
//                 if(time() - $tokenexpire > $this->tokenexpire){
//                     $this->genToken($user['id']);
//                 }
                $token = $this->genToken($user['id']);

                $this->userInfor = $this->updateUserInfor($user);
                $this->userInfor['token'] = $token;
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
        $sql = "SELECT `core_user`.*,groupname,homepage FROM `core_user` INNER JOIN core_usergroup ON core_user.group = core_usergroup.id 
            WHERE `core_user`.deletedby = 0 AND token LIKE '$token'";
        $query = $this->db->query($sql);
        $user = $query->row;
        if (!empty($user)) {
            $tokenexpire = $user['tokenexpire'];
            if (time() - $tokenexpire < $this->tokenexpire) {
                $this->userInfor = $this->updateUserInfor($user);
                return array(
                    'statuscode' => 1,
                    'text' => 'Login success',
                    'data' => $this->userInfor
                );
            } else {
                return array(
                    'statuscode' => -1,
                    'text' => 'Token is expired!',
                    'data' => array()
                );
            }

        } else {
            return array(
                'statuscode' => 0,
                'text' => 'User is not exist!',
                'data' => array()
            );
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
            if($this->userInfor['id'] > 0){
                $sql = "SELECT * FROM `core_usergroup` WHERE deletedby = 0 AND `id` = " . $this->userInfor['group'];
                $query = $this->db->query($sql);
                $group = $query->row;
                $permission = $this->string->stringToArray($group['permission']);
                foreach ($permission as $menuid) {
                    if ((int)$menuid) {
                        $arrMenuId[] = $menuid;
                    }
                }
            }

        }
        return $arrMenuId;
    }

    private function getEntityPermission()
    {
        $sql = "SELECT * FROM `core_entity_permission` WHERE `deletedby`= 0 AND `groupid` = " . $this->userInfor['group'] . " Order by id DESC";
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
				where `deletedby`= 0 AND entitytype ='$path' AND classname = '$classname' Order by id DESC";
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