<?php
namespace Lib;
class Worker
{
    private $db;
    private $date;
    private $string;
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
    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->date = new Date();
        $this->string = new ObjString();
    }
    public function encryptionPassword($password){
        return $this->db->escape(hash('sha512',md5($password)));
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
                    ),JSON_UNESCAPED_UNICODE);
                } else {
                    return json_encode(array(
                        'statuscode' => 0,
                        'text' => 'Mật khẩu không đúng!',
                        'data' => array()
                    ),JSON_UNESCAPED_UNICODE);
                }
            }else{
                return json_encode(array(
                    'statuscode' => 0,
                    'text' => 'Tài khoản bị khóa',
                    'data' => array()
                ),JSON_UNESCAPED_UNICODE);
            }

        } else {
            return json_encode(array(
                'statuscode' => 0,
                'text' => 'Tài khoản không tồn tại',
                'data' => array()
            ),JSON_UNESCAPED_UNICODE);
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
            'roleid' => $user['roleid'],
            'rolename' => $rolename,
            'homepage' => $user['homepage'],
            'allow' => $allow,
            'roleidchild' => $this->string->matrixToArray($roles,'id'),
            'groupname' => $user['groupname'],
            'avatar' => $user['avatar'],
        );
        $this->userInfor = $data;
        return $data;
    }
}