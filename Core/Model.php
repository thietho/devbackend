<?php

namespace Core;

use Lib\Action;
use Lib\Auth;
use Lib\Cache;
use Lib\Entity;
use Lib\Date;
use Lib\File;
use Lib\ObjString;
use Lib\Validation;

class Model
{
    protected $db;
    protected $date;
    protected $string;
    public $entity;
    protected $tablename;
    protected $cache;
    protected $auth;
    protected $validation;
    private $action;
    public $arr_col;
    public $datatype = array(
        'VARCHAR' => 'String',
        'PASSWORD' => 'Password',
        'TEXT' => 'Textarea',
        'LONGTEXT' => 'Editor',
        'code' => 'Code Editor',
        'INT' => 'Integer',
        'BIGINT' => 'Long Integer',
        'FLOAT' => 'Decimal',
        'DOUBLE' => 'Money',
        'DATE' => 'Date',
        'DATETIME' => 'DateTime',
        'TIME' => 'Time',
        'BOOLEAN' => 'Boolean',
        'optionset' => 'Option Set',
        'optionsetmulti' => 'Option Set Multi',
        'relatedto' => 'Related to',
        'relatedtomulti' => 'Related to Multi',
        'image' => 'Image',
        'imagemulti' => 'Image Multi',
        'file' => 'File',
        'video' => 'Video',
        'attachment' => 'Attachment',
        'keyvalue' => 'Key Value'
    );

    public $corecol = array(
        'assignees' => array('Label' => 'Assignees', 'datatype' => 'relatedto', 'entityrelated' => '1'),
        'assignat' => array('Label' => 'Assign At', 'datatype' => 'DATETIME'),
        'createdby' => array('Label' => 'Created By', 'datatype' => 'relatedto', 'entityrelated' => '1'),
        'createdat' => array('Label' => 'Created At', 'datatype' => 'DATETIME'),
        'updatedby' => array('Label' => 'Updated By', 'datatype' => 'relatedto', 'entityrelated' => '1'),
        'updatedat' => array('Label' => 'Updated At', 'datatype' => 'DATETIME'),
        'deletedby' => array('Label' => 'Deleted By', 'datatype' => 'relatedto', 'entityrelated' => '1'),
        'deletedat' => array('Label' => 'Deleted At', 'datatype' => 'DATETIME'),

    );
    public $coreAttributes = array(
        array('id' => 'assignees', 'attributename' => 'assignees', 'attributelabel' => 'Assignees', 'datatype' => 'relatedto', 'entityrelated' => '1', 'datalength' => 0),
        array('id' => 'assignat', 'attributename' => 'assignat', 'attributelabel' => 'Assign At', 'datatype' => 'DATETIME', 'entityrelated' => '0', 'datalength' => 0),
        array('id' => 'createdby', 'attributename' => 'createdby', 'attributelabel' => 'Created By', 'datatype' => 'relatedto', 'entityrelated' => '1', 'datalength' => 0),
        array('id' => 'createdat', 'attributename' => 'createdat', 'attributelabel' => 'Created At', 'datatype' => 'DATETIME', 'entityrelated' => '0', 'datalength' => 0),
        array('id' => 'updatedby', 'attributename' => 'updatedby', 'attributelabel' => 'Updated By', 'datatype' => 'relatedto', 'entityrelated' => '1', 'datalength' => 0),
        array('id' => 'updatedat', 'attributename' => 'updatedat', 'attributelabel' => 'Updated At', 'datatype' => 'DATETIME', 'entityrelated' => '0', 'datalength' => 0),
    );

    public function __construct()
    {
        global $auth, $db;
        $this->db = $db;
        $this->date = new Date();
        $this->string = new ObjString();
        $this->cache = new Cache();
        $this->auth = $auth;
        $this->action = new Action();
        $this->validation = new Validation();
    }

    public function getCoreCol()
    {
        $cols = array();
        foreach ($this->corecol as $key => $item) {
            $cols[$key] = $item['datatype'];
        }
        return $cols;
    }

    public function getItem($id, $tablename = '')
    {
        $id = $this->db->escape($id);
        $tablename = $this->db->escape($tablename);
        if ($tablename == '') {
            $tablename = $this->tablename;
        }
        $sql = "Select `" . $tablename . "`.*
									from `" . $tablename . "`
									where id ='" . $id . "' ";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getEntity($id)
    {
        $id = $this->db->escape($id);
        $sql = "Select `core_entity`.*
									from `core_entity`
									where id ='" . $id . "' ";
        $query = $this->db->query($sql);
        $entity = $query->row;
        if ($entity['structure'] == '') {
            $entity['structure'] = 'list';
        }
        $entity['attributes'] = $this->getAttributes($id);
        return $entity;
    }

    public function getEntitys($where = "", $from = 0, $to = 0)
    {
        $sql = "Select *
                from `core_entity`
				where `deletedby`= 0 " . $where;
        if ($to > 0) {
            $sql .= " Limit " . $from . "," . $to;
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAllAttributes($entityid)
    {
        $entityid = $this->db->escape($entityid);
        $sql = "Select *
                from `core_entity_attribute`
				where entityid = $entityid ORDER BY `position`";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAttributes($entityid)
    {
        $entityid = $this->db->escape($entityid);
        $sql = "Select *
                from `core_entity_attribute`
				where entityid = $entityid AND deletedby = 0 ORDER BY `position`";
        $query = $this->db->query($sql);
        $data = $query->rows;
        foreach ($data as &$attribute) {
            if ($attribute['optionsetvalue'] != '') {
                $attribute['optionsetvalue'] = json_decode($attribute['optionsetvalue'], true);
            }
        }
        return $data;
    }

    public function getEntityByClassType($classname, $type)
    {
        $classname = $this->db->escape($classname);
        $type = $this->db->escape($type);
        $where = " AND entitytype = '$type' AND classname = '$classname'";
        $entitys = $this->getEntitys($where);
        if (empty($entitys))
            return array();
        $entity = $entitys[0];
        $entity['attributes'] = $this->getAttributes($entity['id']);
        $entity['coreattributes'] = $this->coreAttributes;
        return $entity;
    }

    public function getEntityByTableName($tablename)
    {
        $tablename = $this->db->escape($tablename);
        $where = " AND tablename ='$tablename'";
        $entitys = $this->getEntitys($where);
        if (empty($entitys))
            return array();
        $entity = $entitys[0];
        $entity['attributes'] = $this->getAttributes($entity['id']);
        $entity['coreattributes'] = $this->coreAttributes;
        return $entity;
    }

    public function getEntityByRoute($path, $classname)
    {
        $path = $this->db->escape($path);
        $classname = $this->db->escape($classname);
        $where = " AND entitytype ='$path' AND classname = '$classname'";
        $entitys = $this->getEntitys($where);
        if (empty($entitys))
            return array();
        $entity = $entitys[0];
        $entity['attributes'] = $this->getAttributes($entity['id']);
        $entity['coreattributes'] = $this->coreAttributes;
        return $entity;
    }

    public function getEntityByMenu($menuid)
    {
        $menuid = $this->db->escape($menuid);
        $where = " AND menuid = $menuid";
        $entitys = $this->getEntitys($where);
        if (empty($entitys))
            return array();
        $entity = $entitys[0];
        $entity['attributes'] = $this->getAttributes($entity['id']);
        $entity['coreattributes'] = $this->coreAttributes;
        return $entity;
    }

    public function getAttributesByEntity($entityid)
    {
        $entityid = $this->db->escape($entityid);
        $sql = "SELECT * FROM `core_entity_attribute` WHERE `entityid` = " . $entityid;
        $query = $this->db->query($sql);
        return $query->rows;
    }


    public function getAttributeByName($attributename, $entityid = 0)
    {
        $attributename = $this->db->escape($attributename);
        $entityid = $this->db->escape($entityid);
        if ($entityid == 0) {
            $entityid = !empty($this->entity) ? $this->entity['id'] : 0;
        }
        if ($entityid) {
            $sql = "Select `core_entity_attribute`.*
									from `core_entity_attribute`
									where attributename ='$attributename' AND entityid = " . $entityid;
            $query = $this->db->query($sql);
            return $query->row;
        } else {
            return array();
        }
    }

    public function getAttributeById($attributeid)
    {
        $attributeid = $this->db->escape($attributeid);
        $query = $this->db->query("Select `core_entity_attribute`.*
									from `core_entity_attribute`
									where id ='$attributeid' ");
        return $query->row;
    }

    public function getValue($id, $name)
    {
        $id = $this->db->escape($id);
        $name = $this->db->escape($name);
        $query = $this->db->query("Select `" . $this->tablename . "`.*
									from `" . $this->tablename . "`
									where id ='" . $id . "' ");
        return $query->row[$name];
    }

    /**
     * @param string $where
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function countTotal($where)
    {
        $sql = "Select count(*) as total
                from `" . $this->tablename . "`
				where `deletedby`= '' " . $where;

        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function genCondition($col, $operator, $val)
    {
        //        $tablename = '';
        //        if(isset($this->model->entity['tablename'])){
        //            $tablename = $this->model->entity['tablename'].'.';
        //        }
        $val = $this->db->escape($val);
        $condition = '';
        switch ($operator) {
            case 'equal':
                $condition = "$col = '$val'";
                break;
            case 'notequal':
                $condition = "$col <> '$val'";
                break;
            case 'in':
                $arr = explode('-', $val);
                $condition = "$col in ('" . implode("','", $arr) . "')";
                break;
            case 'notin':
                $arr = explode('-', $val);
                $condition = "$col not in ('" . implode("','", $arr) . "')";
                break;
            case 'lessthan':
                $condition = "$col < '$val'";
                break;
            case 'lessthanequal':
                $condition = "$col <= '$val'";
                break;
            case 'morethan':
                $condition = "$col > '$val'";
                break;
            case 'morethanequal':
                $condition = "$col >= '$val'";
                break;
            case 'between':
                $arr = $this->string->stringToArray($val);
                $condition = "$col BETWEEN '".$arr[0]."' AND '".$arr[1]."'";
                break;
            case 'notbetween':
                $arr = $this->string->stringToArray($val);
                $condition = "$col NOT BETWEEN '".$arr[0]."' AND '".$arr[1]."'";
                break;
            case 'contains':
                $condition = "$col LIKE '%$val%'";
                break;
            case 'notcontains':
                $condition = "$col NOT LIKE '%$val%'";
                break;
            case 'containsin':
                $condition = "$col LIKE '%[$val]%'";
                break;
            case 'notcontainsin':
                $condition = "$col NOT LIKE '%[$val]%'";
                break;
            case 'empty':
                $condition = "$col = ''";
                break;
            case 'notempty':
                $condition = "$col <> ''";
                break;
        }
        return $condition;
    }

    public function getList($where = "", $from = 0, $to = 0, $template = array())
    {
        $listuserid = array();
        $allow = 'all';
        $whereassignees = "";
        if (!empty($this->auth->userInfor) && !empty($this->entity) && $this->auth->userInfor['group'] != 1) {
            $listroleid = [];
            $allow = $this->auth->userInfor['allow'];
            if ($this->auth->userInfor['allow'] == 'minechild') {
                foreach ($this->auth->userInfor['roleidchild'] as $roleid) {
                    $listroleid[] = $roleid;
                }
            }
            if ($this->auth->userInfor['allow'] != 'all') {
                $listuserid []= $this->auth->userInfor['id'];
                if(!empty($listroleid)){
                    $sql = "SELECT * FROM `core_user` WHERE `roleid` in (" . implode(',', $listroleid) . ");";
                    $query = $this->db->query($sql);
                    $users = $query->rows;

                    foreach ($users as $user) {
                        $listuserid [] = $user['id'];
                    }
                }


                if($allow!='all'){
                    $whereassignees = " AND ". $this->entity['tablename'].".createdby in (".implode(',',$listuserid).") OR ". $this->entity['tablename'].".`assignees` IN (".implode(',',$listuserid).")";
                }
            }

        }


        $whereuser = '';
        if (!empty($this->entity)) {
            if ($this->entity['classname'] == 'User' && $this->auth->userInfor['group'] != 1) {
                $whereuser = " AND core_user.group <> 1";
            }
            if ($this->entity['classname'] == 'UserGroup' && $this->auth->userInfor['group'] != 1) {
                $whereuser = " AND id <> 1";
            }
        }

        if (empty($template)) {
            $sql = "Select *
                from `" . $this->tablename . "`
				where `deletedby`= 0 " . $whereuser;
        } else {
            $cols = $template;
            $arr_colname = array();
            $arr_join = array();
            $i = 0;
            foreach ($cols['cols'] as $col) {
                $attribute = $this->getAttributeById($col['attributeid']);
                if (empty($attribute)) {
                    $attribute = $col;
                    $attribute['entityid'] = $this->entity['id'];
                    $attribute['structure'] = 'list';
                }
                $entity = $this->getEntity($attribute['entityid']);

                if ($attribute['datatype'] == 'relatedto') {
                    $entityrelated = $this->getEntity($attribute['entityrelated']);
                    if (!isset($attribute['attributeid'])) {
                        $join = 'LEFT JOIN ' . $entityrelated['tablename'] . ' ON ' . $this->entity['tablename'] . '.' . $attribute['attributename'] . ' = ' . $entityrelated['tablename'] . '.id';
                    } else {
                        $join = 'LEFT JOIN ' . $entityrelated['tablename'] . ' ON ' . $attribute['attributename'] . ' = ' . $entityrelated['tablename'] . '.id';
                    }

                    $arr_join[] = $join;
                    $i++;
                }

                if (isset($attribute['attributeid'])) {
                    $arr_colname[] = $attribute['attributename'];
                } else {
                    $arr_colname[] = $entity['tablename'] . '.' . $attribute['attributename'];
                }

            }
            $strCondition = '';
            if (!empty($template['condition'])) {
                $strCondition .= "AND ";
                $dataCondition = $template['condition'];
                foreach ($dataCondition as $expresstion) {
                    $arr = explode('|', $expresstion);
                    if (count($arr) == 3) {
                        $strCondition .= $this->genCondition($arr[0], $arr[1], $arr[2]);
                    } else {
                        $strCondition .= ' ' . $expresstion . ' ';
                    }
                }
            }

            $sql = "Select " . $this->entity['tablename'] . ".id," . implode(',', $arr_colname) . "
                from `" . $this->tablename . "` " . implode(' ', $arr_join) . "
				where " . $this->entity['tablename'] . ".`deletedby`= 0 $whereassignees $whereuser " . $strCondition;
        }
        $sql .= $where;
        if ($to > 0) {
            $sql .= " Limit " . $from . "," . $to;
        }
        //echo $sql . PHP_EOL;
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getNextId($col)
    {
        return $this->db->getNextId($this->tablename, $col);
    }

    // pattern = xxxx%xxxx
    public function getNextPatternId($fieldname, $pattern)
    {
        $sql = "SELECT $fieldname FROM " . $this->tablename . " WHERE `$fieldname` LIKE '$pattern'";
        $query = $this->db->query($sql);
        $result = $query->rows;

        $arr = explode('%', $pattern);
        $next = 1;
        if (!empty($result)) {
            $maxid = 1;
            foreach ($result as $item) {
                $value = $item[$fieldname];
                if (isset($arr[0])) {
                    $value = str_replace($arr[0], '', $value);
                }
                if (isset($arr[1])) {
                    $value = str_replace($arr[1], '', $value);
                }
                $value = intval($value);
                if (intval($value) > $maxid) {
                    $maxid = intval($value);
                }
            }
            $next = $maxid + 1;
        }
        $strnextid = $this->string->numberToString($next, 4);
        return str_replace('%', $strnextid, $pattern);
    }

    public function updateCol($id, $col, $val, $tablename = '')
    {
        $id = $this->db->escape($id);
        $col = $this->db->escape($col);
        $val = $this->db->escape($val);

        $field = array(
            $col,
            'updatedby',
            'updatedat'
        );
        $value = array(
            $val,
            $this->auth->userInfor['id'],
            $this->date->getToday()
        );

        $where = "id = '" . $id . "'";
        if ($tablename == '') {
            $tablename = $this->tablename;
        }
        $this->db->updateData($tablename, $field, $value, $where);
        $this->cache->clearView();
        //Log
        if ($col != 'deletedby' && $col != 'deletedat') {
            $itemold = $this->getItem($id, $tablename);
            $data = array(
                $col => $val,
                'updatedby' => $this->auth->userInfor['id'],
                'updatedat' => $this->date->getToday()
            );
            $dataold = array();
            $datanew = array();
            foreach ($data as $key => $val) {
                if ($val != $itemold[$key]) {
                    $dataold[$key] = $itemold[$key];
                    $datanew[$key] = $val;
                }
            }
            $this->writeLog($id, 'Edit', $dataold, $datanew);
        }
    }

    private function dataParser($val, $type)
    {
        switch ($type) {
            case 'INT':
            case 'BIGINT':
            case 'FLOAT':
            case 'DOUBLE':
                $val = $this->db->escape($this->string->toNumber($val));
                break;
            case 'DATE':
            case 'DATETIME':
                if ($val != '') {
                    $val = $this->db->escape($this->date->toServerDate($val));
                }
                break;
//            case 'relatedtomulti':
//            case 'optionsetmulti':
//                $val = $this->db->escape($this->string->arrayToString($val));
//                break;
            case 'code':
                $val = $this->db->escape(base64_encode($val));
                break;
            case 'attachment':

                break;
            default:
                $val = $this->db->escape($val);
        }
        return $this->db->escape($val);
    }

    private function formateData($data)
    {
        foreach ($data as $col => $val) {
            if ($col != 'id') {
                $col = str_replace('_value', '', $col);
                if ($col == 'createdat') {
                    if ($val == '') {
                        $data[$col] = $this->date->getToday();
                    } else {
                        $data[$col] = $this->dataParser($val, 'DATETIME');
                    }
                }
                if ($col == 'updatedat') {
                    if ($val == '') {
                        $data[$col] = $this->date->getToday();
                    } else {
                        $data[$col] = $this->dataParser($val, 'DATETIME');
                    }
                }
                if ($col == 'assignat') {
                    if ($val == '') {
                        $data[$col] = $this->date->getToday();
                    } else {
                        $data[$col] = $this->dataParser($val, 'DATETIME');
                    }
                } else {
                    $attribute = $this->getAttributeByName($col);
                    if (!empty($attribute)) {
                        $data[$col] = $this->dataParser($val, $attribute['datatype']);
                    }
                }
                if (!empty($_FILES[$col]['name'])) {
                    $path = FILESERVER . 'upload';
                    if (!is_dir($path)) {
                        mkdir($path);
                        chmod($path, 0777);
                    }
                    $path .= '/' . $this->tablename;
                    if (!is_dir($path)) {
                        mkdir($path);
                        chmod($path, 0777);
                    }
                    $path .= '/' . $data['id'];
                    if (!is_dir($path)) {
                        mkdir($path);
                        chmod($path, 0777);
                    }
                    $path .= '/' . $_FILES[$col]['name'];
                    move_uploaded_file($_FILES[$col]['tmp_name'], $path);
                    $data[$col] = $_FILES[$col]['name'];
                }
            }
        }
        if (isset($data['id']) && $data['id'] > 0) {
            $obj = $this->getItem($data['id']);
            $datachange = array();
            foreach ($data as $col => $val) {
                if (isset($obj[$col]) && $obj[$col] != $val) {
                    $datachange[$col] = $val;
                }
            }
            if (isset($datachange['assignees'])) {
                $datachange['assignat'] = $this->date->getToday();
            }
            if (!empty($datachange)) {
                $datachange['updatedby'] = $this->auth->userInfor['id'];
                $datachange['updatedat'] = $this->date->getToday();
            }

            $datachange['id'] = $data['id'];

            return $datachange;
        }
        $data['createdby'] = $this->auth->userInfor['id'];
        $data['createdat'] = $this->date->getToday();
//        $data['assignees'] = $this->auth->userInfor['id'];
//        $data['assignat'] = $this->date->getToday();
        return $data;
    }

    private function validate($data)
    {
        $errors = array();
        foreach ($data as $key => $val) {
            $attribute = $this->getAttributeByName($key);
            if (!empty($attribute)) {
                if ($attribute['isrequire']) {
                    if ($val == '') {
                        $errors[$key] = $attribute['attributelabel'] . ' is require!';
                    }
                }
                if ($attribute['notduplicate']) {
                    if (!isset($data['id']) || $data['id'] == 0 || $data['id'] == '') {
                        $where = " AND $key = '$val'";
                        $list = $this->getList($where);
                        if (!empty($list)) {
                            $errors[$key] = $attribute['attributelabel'] . ' is duplicate!';
                        }
                    } else {
                        $where = " AND $key = '$val' AND id <> " . $data['id'];
                        $list = $this->getList($where);
                        if (!empty($list)) {
                            $errors[$key] = $attribute['attributelabel'] . ' is duplicate!';
                        }
                    }
                }
                if ($attribute['datalength'] > 0) {
                    if (strlen($val) > $attribute['datalength']) {
                        $errors[$key] = $attribute['attributelabel'] . ' is length over max string length!';
                    }
                }
            }
        }
        return $errors;
    }

    private function beforeCreate(&$context, &$errors = array())
    {
        if (!empty($this->entity)) {
            $processs = $this->action->getProcessbyEntity($this->entity['id'], 'beforecreate');
            foreach ($processs as $process) {
                if ($process['content'] != '' && $process['active']) {
                    eval("?> " . base64_decode($process['content']) . " <?php ");
                    $process_update = array(
                        'id' => $process['id'],
                        'lastrun' => $this->date->formatMySQLDate($this->date->getToday(), 'DMY H:i:s'),
                    );
                    $processModel = new Entity('Core', 'Process');
                    $result = $processModel->save($process_update);
                    $processTraceModel = new Entity('Core', 'ProcessTrace');
                    $processTrace_insert = array(
                        'processid' => $process['id'],
                        'method' => 'BeforeCreate',
                        'input' => json_encode($context),
                        'output' => json_encode($errors),
                    );
                    $processTrace = $processTraceModel->save($processTrace_insert);
                }
            }
        }
    }

    private function createComplete(&$context, &$errors = array())
    {
        if (!empty($this->entity)) {
            $processs = $this->action->getProcessbyEntity($this->entity['id'], 'created');
            foreach ($processs as $process) {
                if ($process['content'] != '' && $process['active']) {
                    eval("?> " . base64_decode($process['content']) . " <?php ");
                }
                $process_update = array(
                    'id' => $process['id'],
                    'lastrun' => $this->date->formatMySQLDate($this->date->getToday(), 'DMY H:i:s'),
                );
                $processModel = new Entity('Core', 'Process');
                $result = $processModel->save($process_update);
                $processTraceModel = new Entity('Core', 'ProcessTrace');
                $processTrace_insert = array(
                    'processid' => $process['id'],
                    'method' => 'AfterCreate',
                    'input' => json_encode($context),
                    'output' => json_encode($errors),
                );
                $processTrace = $processTraceModel->save($processTrace_insert);
            }
        }
    }

    private function beforeUpdate(&$context, &$errors = array())
    {
        if (!empty($this->entity)) {
            $processs = $this->action->getProcessbyEntity($this->entity['id'], 'beforeupdate');
            foreach ($processs as $process) {
                if ($process['content'] != '' && $process['active']) {
                    eval("?> " . base64_decode($process['content']) . " <?php ");
                    $process_update = array(
                        'id' => $process['id'],
                        'lastrun' => $this->date->formatMySQLDate($this->date->getToday(), 'DMY H:i:s'),
                    );
                    $processModel = new Entity('Core', 'Process');
                    $result = $processModel->save($process_update);
                    $processTraceModel = new Entity('Core', 'ProcessTrace');
                    $processTrace_insert = array(
                        'processid' => $process['id'],
                        'method' => 'BeforeUpdate',
                        'input' => json_encode($context),
                        'output' => json_encode($errors),
                    );
                    $processTrace = $processTraceModel->save($processTrace_insert);
                }
            }
        }
    }

    private function updateComplete(&$context, &$errors = array())
    {
        if (!empty($this->entity)) {
            $processs = $this->action->getProcessbyEntity($this->entity['id'], 'updated');
            foreach ($processs as $process) {
                if ($process['content'] != '' && $process['active']) {
                    eval("?> " . base64_decode($process['content']) . " <?php ");
                    $process_update = array(
                        'id' => $process['id'],
                        'lastrun' => $this->date->formatMySQLDate($this->date->getToday(), 'DMY H:i:s'),
                    );
                    $processModel = new Entity('Core', 'Process');
                    $result = $processModel->save($process_update);
                    $processTraceModel = new Entity('Core', 'ProcessTrace');
                    $processTrace_insert = array(
                        'processid' => $process['id'],
                        'method' => 'AfterUpdate',
                        'input' => json_encode($context),
                        'output' => json_encode($errors),
                    );
                    $processTrace = $processTraceModel->save($processTrace_insert);
                }
            }
        }
    }

    private function beforeSave(&$context, &$errors = array())
    {
        if (!empty($this->entity)) {
            $processs = $this->action->getProcessbyEntity($this->entity['id'], 'beforesave');
            foreach ($processs as $process) {
                if ($process['content'] != '' && $process['active']) {
                    eval("?> " . base64_decode($process['content']) . " <?php ");
                    $process_update = array(
                        'id' => $process['id'],
                        'lastrun' => $this->date->formatMySQLDate($this->date->getToday(), 'DMY H:i:s'),
                    );
                    $processModel = new Entity('Core', 'Process');
                    $result = $processModel->save($process_update);
                    $processTraceModel = new Entity('Core', 'ProcessTrace');
                    $processTrace_insert = array(
                        'processid' => $process['id'],
                        'method' => 'BeforeSave',
                        'input' => json_encode($context),
                        'output' => json_encode($errors),
                    );
                    $processTrace = $processTraceModel->save($processTrace_insert);
                }
            }
        }
    }

    private function saveComplete(&$context, &$errors = array())
    {
        if (!empty($this->entity)) {
            $processs = $this->action->getProcessbyEntity($this->entity['id'], 'saved');
            foreach ($processs as $process) {
                if ($process['content'] != '' && $process['active']) {
                    eval("?> " . base64_decode($process['content']) . " <?php ");
                    $process_update = array(
                        'id' => $process['id'],
                        'lastrun' => $this->date->formatMySQLDate($this->date->getToday(), 'DMY H:i:s'),
                    );
                    $processModel = new Entity('Core', 'Process');
                    $result = $processModel->save($process_update);
                    $processTraceModel = new Entity('Core', 'ProcessTrace');
                    $processTrace_insert = array(
                        'processid' => $process['id'],
                        'method' => 'AfterSace',
                        'input' => json_encode($context),
                        'output' => json_encode($errors),
                    );
                    $processTrace = $processTraceModel->save($processTrace_insert);
                }
            }
        }
    }

    private function beforeDelete(&$context, &$errors = array())
    {
        if (!empty($this->entity)) {
            $processs = $this->action->getProcessbyEntity($this->entity['id'], 'beforedelete');
            foreach ($processs as $process) {
                if ($process['content'] != '' && $process['active']) {
                    eval("?> " . base64_decode($process['content']) . " <?php ");
                    $process_update = array(
                        'id' => $process['id'],
                        'lastrun' => $this->date->formatMySQLDate($this->date->getToday(), 'DMY H:i:s'),
                    );
                    $processModel = new Entity('Core', 'Process');
                    $result = $processModel->save($process_update);
                    $processTraceModel = new Entity('Core', 'BeforeDelete');
                    $processTrace_insert = array(
                        'processid' => $process['id'],
                        'method' => 'AfterUpdate',
                        'input' => json_encode($context),
                        'output' => json_encode($errors),
                    );
                    $processTrace = $processTraceModel->save($processTrace_insert);
                }
            }
        }
    }

    private function deleteComplete(&$context, &$errors = array())
    {
        if (!empty($this->entity)) {
            $processs = $this->action->getProcessbyEntity($this->entity['id'], 'deleted');
            foreach ($processs as $process) {
                if ($process['content'] != '' && $process['active']) {
                    eval("?> " . base64_decode($process['content']) . " <?php ");
                    $process_update = array(
                        'id' => $process['id'],
                        'lastrun' => $this->date->formatMySQLDate($this->date->getToday(), 'DMY H:i:s'),
                    );
                    $processModel = new Entity('Core', 'Process');
                    $result = $processModel->save($process_update);
                    $processTraceModel = new Entity('Core', 'ProcessTrace');
                    $processTrace_insert = array(
                        'processid' => $process['id'],
                        'method' => 'AfterDelete',
                        'input' => json_encode($context),
                        'output' => json_encode($errors),
                    );
                    $processTrace = $processTraceModel->save($processTrace_insert);
                }
            }
        }
    }

    public function save($data)
    {
        if (empty($data['id'])) {

            foreach ($this->arr_col as $col => $type) {
                $attribute = $this->getAttributeByName($col);
                if (isset($attribute['isrequire']) && $attribute['isrequire']) {
                    if (!isset($data[$attribute['attributename']])) {
                        $data[$col] = '';
                    }
                }
                if (isset($attribute['autoincrement']) && $attribute['autoincrement']) {
                    if ($type == 'INT' or $type == 'BIGINT') {
                        $data[$col] = $this->getNextId($col);
                    }
                    if ($type == 'VARCHAR') {
                        $pattern = $this->string->decodePattern($attribute['pattern']);
                        $data[$col] = $this->getNextPatternId($col, $pattern);
                    }
                }

            }
        } else {
            foreach ($this->arr_col as $col => $type) {
                $attribute = $this->getAttributeByName($col);
                if (isset($attribute['autoincrement']) && $attribute['autoincrement']) {
                    if ($type == 'INT' or $type == 'BIGINT') {
                        if (isset($data[$col]) && $data[$col] == 0) {
                            $data[$col] = $this->getNextId($col);
                        }

                    }
                    if ($type == 'VARCHAR') {
                        if (isset($data[$col]) && $data[$col] == '') {
                            $pattern = $this->string->decodePattern($attribute['pattern']);
                            $data[$col] = $this->getNextPatternId($col, $pattern);
                        }
                    }
                }

            }
        }

        $errors = $this->validate($data);
        if (empty($data['id'])) {
            $this->beforeCreate($data, $errors);
        } else {
            $this->beforeUpdate($data, $errors);
        }
        $this->beforeSave($data, $errors);
        if (!empty($errors)) {
            return array(
                'statuscode' => 0,
                'text' => 'Save failed',
                'data' => $errors
            );
        }
        $data = $this->formateData($data);
        $value = array();
        $field = array();
        foreach ($data as $col => $val) {
            $value[] = $val;
            $field[] = $col;
        }
        if (empty($data['id'])) {
            $data['id'] = $this->db->insertData($this->tablename, $field, $value);
            if (!empty($this->entity) && $this->entity['tablename'] != 'core_log') {
                $this->writeLog($data['id'], 'Insert', array(), $data);
            }
            $this->createComplete($data);
        } else {
            $itemold = $this->getItem($data['id']);
            $where = "id = '" . $data['id'] . "'";
            $this->db->updateData($this->tablename, $field, $value, $where);
            if (!empty($this->entity) && $this->entity['tablename'] != 'core_log') {
                $dataold = array();
                $datanew = array();
                foreach ($data as $key => $val) {
                    if ($val != $itemold[$key]) {
                        $dataold[$key] = $itemold[$key];
                        $datanew[$key] = $val;
                    }
                }
                if (!empty($datanew)) {
                    $this->writeLog($data['id'], 'Edit', $dataold, $datanew);
                }
            }
            $this->updateComplete($data);
        }
        $this->saveComplete($data);
        $this->cache->clearView();
        if ($data['id'] != 0) {
            return array(
                'statuscode' => 1,
                'text' => 'Save success',
                'data' => $data
            );
        } else {
            return array(
                'statuscode' => 0,
                'text' => 'Save failed',
                'data' => $data
            );
        }
    }

    public function getRelatedValue($valueid, $entityrelated)
    {
        if ($entityrelated) {
            $entity = $this->getEntity($entityrelated);
            $maincol = $entity['maincol'];
            $sql = "SELECT * FROM `core_entity_attribute` WHERE id = " . $maincol . " AND entityid = " . $entity['id'];
            $query = $this->db->query($sql);
            $attribute = $query->row;
            $maincolname = $attribute['attributename'];
            if ((int)$valueid) {
                $sql = "SELECT `$maincolname` FROM `" . $entity['tablename'] . "` WHERE id = " . $valueid;
                try {
                    $query = $this->db->query($sql);
                    if ($query->num_rows) {
                        return '<a href="?route=' . $entity['entitytype'] . '/' . $entity['classname'] . '/View&id=' . $valueid . '" target="_blank">' . $query->row[$maincolname] . '</a>';
                    } else {
                        return '';
                    }
                } catch (\Exception $exception) {

                }

            } else {
                $str = str_replace('[', '', $valueid);
                $str = str_replace(']', '', $str);

                $arr = explode(',', $str);
                $arrMenuId = array();
                if (!empty($arr)) {
                    foreach ($arr as $menuid) {
                        if ((int)$menuid) {
                            $arrMenuId[] = $menuid;
                        }
                    }
                }

                if ($arr != null && !empty($arr) && !empty($arrMenuId)) {
                    $sql = "SELECT `id`,`$maincolname` FROM `" . $entity['tablename'] . "` WHERE id in (" . implode(',', $arrMenuId) . ")";
                    $query = $this->db->query($sql);
                    if ($query->num_rows) {
                        $result = $query->rows;
                        $arr = array();
                        foreach ($result as $item) {
                            //$arr[] = $item[$maincolname];
                            $arr[] = '<a href="?route=' . $entity['entitytype'] . '/' . $entity['classname'] . '/View&id=' . $item['id'] . '" target="_blank">' . $item[$maincolname] . '</a>';
                        }
                        return implode(', ', $arr);
                    } else {
                        return '';
                    }
                }
            }
        } else {
            return '';
        }

    }

    private function writeLog($recordid, $action, $dataold, $datanew)
    {
        if (!empty($this->entity)) {
            $data = array(
                'logdate' => $this->date->getToday(),
                'entityid' => !empty($this->entity) ? $this->entity['id'] : 0,
                'entityname' => !empty($this->entity) ? $this->entity['entityname'] : '',
                'recordid' => $recordid,
                'action' => $action,
                'actionby' => $this->auth->userInfor['id'],
                'dataold' => base64_encode(json_encode($dataold)),
                'datanew' => base64_encode(json_encode($datanew)),
            );
            $data['createdby'] = $this->auth->userInfor['id'];
            $data['createdat'] = $this->date->getToday();
            $data['assignees'] = $this->auth->userInfor['id'];
            $data['assignat'] = $this->date->getToday();
            foreach ($data as $col => $val) {
                $value[] = $val;
                $field[] = $col;
            }
            $data['id'] = $this->db->insertData('core_log', $field, $value);
            return $data;
        }
    }

    public function delete($id)
    {
        $errors = array();
        if (!empty($this->entity)) {
            $entityRelateds = $this->getEntityRelated($this->entity['id']);
            foreach ($entityRelateds as &$entityRelated) {
                $data = $this->getRelateData($id, $entityRelated);
                $entityRelated['data'] = $data;
                if (!empty($data)) {
                    $errors[] = "Have data related to " . $entityRelated['entityname'];
                }
            }
        }
        if (!empty($errors)) {
            return json_encode(array(
                'statuscode' => 0,
                'text' => implode('<br>', $errors),
            ));
        }
        $this->beforeDelete($id);
        if (!empty($this->entity)) {
            if ($this->entity['structure'] == 'tree') {
                $childs = $this->getChilds($id);
                if (!empty($childs)) {
                    return json_encode(array(
                        'statuscode' => 0,
                        'text' => 'Deleted data is error! You must clear all childs',
                    ));
                }
            }
        }

        $this->updateCol($id, 'deletedby', $this->auth->userInfor['id']);
        $this->updateCol($id, 'deletedat', $this->date->getToday());

        $datanew = array(
            'deletedby' => $this->auth->userInfor['id'],
            'deletedat' => $this->date->getToday()
        );
        $this->writeLog($id, 'Delete', array(), $datanew);
        $this->deleteComplete($id);
        return json_encode(array(
            'statuscode' => 1,
            'text' => 'Deleted data success',
        ));
    }

    private function getEntityRelated($entityid)
    {
        $sql = "SELECT core_entity.entitytype,core_entity.tablename,core_entity.classname,core_entity.entityname, core_entity_attribute.* 
            FROM `core_entity_attribute` 
                INNER JOIN core_entity ON core_entity.id = core_entity_attribute.entityid
            WHERE `entityrelated` = $entityid AND core_entity_attribute.deletedby = 0";
        $this->db->query($sql);
        $query = $this->db->query($sql);
        $data = $query->rows;
        $result = array();
        foreach ($data as $entity) {
            $check = $this->auth->checkEntityPermission($entity['entitytype'], $entity['classname'], 'View');
            if ($check) {
                $result[] = $entity;
            }
        }
        return $result;
    }

    public function getRelateData($id, $entityRelate)
    {
        $sql = "SELECT * FROM " . $entityRelate['tablename'] . " WHERE deletedby = 0 AND " . $entityRelate['attributename'] . " = $id";
        $this->db->query($sql);
        $query = $this->db->query($sql);
        $data = $query->rows;
        return $data;
    }

    public function getChilds($id)
    {
        $parentAttribute = $this->getAttributeById($this->entity['parentcol']);
        $parentcol = $parentAttribute['attributename'];
        $sortAttribute = $this->getAttributeById($this->entity['sortcol']);
        $sortcol = $sortAttribute['attributename'];
        $where = " AND $parentcol = $id ORDER BY `$sortcol` ASC";
        return $this->getList($where);
    }

    public function travel($rootid, &$data, $level = 0)
    {
        $childs = $this->getChilds($rootid);
        if (!empty($childs)) {
            foreach ($childs as &$child) {
                $child['level'] = $level;
                $data[] = $child;
                $this->travel($child['id'], $data, $level + 1);
            }
        }
    }

    public function getLogRecord($entityid, $recordid, $from = 0, $to = 0)
    {
        $sql = "Select *
                from `core_log`
				where `deletedby`= 0 AND `entityid` = $entityid AND recordid = $recordid ORDER BY `logdate` DESC";
        if ($to > 0) {
            $sql .= " Limit " . $from . "," . $to;
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function destroy($id)
    {
        $where = "id = '" . $id . "'";
        $this->db->deleteData($this->tablename, $where);
        $this->cache->clearView();
    }

    public function destroyBulk($where)
    {
        $this->db->deleteData($this->tablename, "1 " . $where);
        $this->cache->clearView();
    }

    public function destroyEntity($id)
    {
        //Xoa controller
        $entity = $this->getEntity($id);
        $path = CONTROLLER . ucfirst($entity['entitytype']) . '/';
        $path .= $entity['classname'] . 'Controller.php';
        if (file_exists($path)) {
            unlink($path);
        }
        //Xóa model
        $path = MODEL . ucfirst($entity['entitytype']) . '/';
        $path .= $entity['classname'] . 'Model.php';
        if (file_exists($path)) {
            unlink($path);
        }
        //Xoa view
        $path = VIEW . ucfirst($entity['entitytype']) . '/' . $entity['classname'];
        $file = new File();
        $file->deleteDir($path);
//        //Xoa file liên quan
        $path = FILESERVER . 'upload/' . $entity['tablename'];
        $file->deleteDir($path);
        $where = "entityid = '" . $id . "'";
        //core_entity_template
        $this->db->deleteData('core_entity_template', $where);
        //core_entity_permission
        $this->db->deleteData('core_entity_permission', $where);
        //core_process
        $this->db->deleteData('core_process', $where);
        //core_entity_attribute
        $this->db->deleteData('core_entity_attribute', $where);
        //core_userview
        $this->db->deleteData('core_userview', $where);
        //delete table in db
        $this->db->query("DROP TABLE IF EXISTS " . $entity['tablename']);

        $where = "id = '" . $id . "'";
        $this->db->deleteData('core_entity', $where);
        $this->cache->clearView();
    }

    public function clearLog()
    {
        $where = " createdat <= date_add(NOW(), interval -7 day)";
        $this->db->deleteData('core_log', $where);
        $this->db->deleteData('core_process_trace', $where);
        $this->db->deleteData('core_logapi', $where);
    }
}
