<?php
namespace Lib;


class Action
{
    private $modelProcess;
    private $loader;
    private $db;
    public function __construct()
    {
        global $db;
        $this->loader = new Loader();
        $this->db = $db;
    }
    public function getItem($id)
    {
        $sql = "Select `core_process`.*
									from `core_process`
									where id ='" . $id . "' ";
        $query = $this->db->query($sql);
        return $query->row;
    }
    public function getList($where = "", $from = 0, $to = 0)
    {
        $sql = "Select *
                from `core_process`
				where `deletedby`= 0 ";
        $sql .= $where;
        if ($to > 0) {
            $sql .= " Limit " . $from . "," . $to;
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }
    public function getProcessbyName($processname){
        $where = " AND `processname` = '$processname'";
        $processs = $this->getList($where);
        if(empty($processs)){
            return array();
        }else{
            return $processs[0];
        }
    }
    public function getProcessbyEntity($entityid,$trigertype){
        $where = " AND `entityid` = '$entityid' AND `trigertype` like '$trigertype'";
        $processs = $this->getList($where);
        return $processs;
    }
    public function execute($processname)
    {
        $process = $this->getProcessbyName($processname);
        if($process['content'] != ''){
            eval("?> ".base64_decode($process['content'])." <?php ");
        }
    }

}