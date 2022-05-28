<?php

namespace Core;

require COMPONENT . "vendor/autoload.php";

use Lib\Auth;
use Lib\Cache;
use Lib\Date;
use Lib\Entity;
use Lib\ObjString;
use Lib\Request;
use Lib\Loader;
use Lib\Pagination;
use Lib\Response;
use Lib\Session;
use Lib\Validation;
use Model\EntityTemplateModel;
use Model\OptionSetModel;
use Model\UserViewModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Controller
{
    protected $request;
    protected $response;
    protected $loader;
    protected $template;
    protected $pagination;
    protected $date;
    protected $string;
    protected $model;
    protected $header;
    protected $footer;
    protected $userViewModel;
    protected $setting;
    protected $language;
    private $langcode = 'vn';
    private $view;
    private $data = array();
    private $layout = '';
    public $session;
    public $route;
    public $method = '';
    public $path;
    public $classname;
    protected $tree;
    public $auth;
    private $iscache;
    protected $validation;
    public $operatorchar = array(
        'equal' => 'Equal',
        'notequal' => 'Not equal',
        'in' => 'In',
        'notin' => 'Not In',
        'lessthan' => 'Less than',
        'lessthanequal' => 'Less than or equal',
        'morethan' => 'More than',
        'morethanequal' => 'More than or equal',
        'between' => 'From - to',
        'notbetween' => 'Not in from - to',
        'contains' => 'Containt',
        'notcontains' => 'Not Containt',
        'containsin' => 'Containt',
        'notcontainsin' => 'Not Containt',
        'empty' => 'Empty',
        'notempty' => 'Not Empty',
    );
    public $dataoperator = array(
        'relatedto' => array('equal', 'notequal', 'in', 'notin', 'empty', 'notempty'),
        'relatedtomulti' => array('containsin', 'notcontainsin', 'empty', 'notempty'),
        'optionset' => array('equal', 'notequal', 'in', 'notin', 'empty', 'notempty'),
        'optionsetmulti' => array('containsin', 'notcontainsin', 'empty', 'notempty'),
        'VARCHAR' => array('equal', 'notequal', 'contains', 'notcontains', 'empty', 'notempty'),
        'TEXT' => array('contains', 'notcontains', 'empty', 'notempty'),
        'LONGTEXT' => array('contains', 'notcontains', 'empty', 'notempty'),
        'INT' => array('equal', 'notequal', 'lessthan', 'lessthanequal', 'morethan', 'morethanequal','between','notbetween'),
        'BIGINT' => array('equal', 'notequal', 'lessthan', 'lessthanequal', 'morethan', 'morethanequal','between','notbetween'),
        'FLOAT' => array('equal', 'notequal', 'lessthan', 'lessthanequal', 'morethan', 'morethanequal','between','notbetween'),
        'DOUBLE' => array('equal', 'notequal', 'lessthan', 'lessthanequal', 'morethan', 'morethanequal','between','notbetween'),
        'DATE' => array('equal', 'notequal', 'lessthan', 'lessthanequal', 'morethan', 'morethanequal','between','notbetween', 'empty', 'notempty'),
        'DATETIME' => array('equal', 'notequal', 'lessthan', 'lessthanequal', 'morethan', 'morethanequal','between','notbetween', 'empty', 'notempty'),
        'TIME' => array('equal', 'notequal', 'lessthan', 'lessthanequal', 'morethan', 'morethanequal', 'empty', 'notempty'),
        'BOOLEAN' => array('equal'),
    );

    public function __construct()
    {
        global $auth, $glSetting;
        $this->request = new Request();
        $this->response = new Response();
        $this->loader = new Loader();
        $this->loader->loadModel('Core', 'EntityTemplate');
        $this->template = new EntityTemplateModel();
        $this->pagination = new Pagination();
        $this->date = new Date();
        $this->string = new ObjString();
        $this->session = new Session();
        $route = $this->request->get('route');
        $this->model = new Model();
        $this->auth = $auth;
        $this->loader->loadModel('Core', 'OptionSet');
        $this->userViewModel = new Entity('Core', 'UserView');
        $this->validation = new Validation();
        $this->setting = $glSetting;
        $languageModel = new Entity('Core', 'Language');
        $dataLanguage = $languageModel->getList();
        foreach ($dataLanguage as $language) {
            $this->language[$language['code']] = $language;
        }
        if ($route != '') {
            $arr = explode("/", $route);
            $this->route = $arr[0] . '/' . $arr[1];
            $this->method = isset($arr[2]) ? $arr[2] : '';
            $this->path = $arr[0];
            $this->classname = $arr[1];
        } else {
            $this->path = "Page";
            $this->classname = "Home";
        }
        $this->iscache = true;
    }

    public function translate($code)
    {
        return isset($this->language[$code]) ? $this->language[$code][$this->langcode] : '';
    }

    public function formateValue($type, $value)
    {
        switch ($type) {
            case 'INT':
            case 'BIGINT':
            case 'FLOAT':
            case 'DOUBLE':
                $val = $this->string->toNumber($value);
                break;
            case 'DATE':
            case 'DATETIME':
                $val = $this->date->toServerDate($value);
                break;
            default:
                $val = $value;
        }
        return $val;
    }


    protected function travelTree($root, $data)
    {
        foreach ($data as $pos => $item) {
            $id = $item['id'];
            $parent = $root;
            $this->tree['updatedata'][] = array(
                'id' => $id,
                $this->tree['parentcol'] => $parent,
                $this->tree['sortordercol'] => $pos
            );
            if (isset($item['children'])) {
                $this->travelTree($id, $item['children']);
            }
        }
    }

    public function getOptionSetValue($key, $optionsetid, $attributeid = 0)
    {
        return $this->loader->getOptionSetValue($key, $optionsetid, $attributeid);
    }

    public function getReqOptionSetValue()
    {
        $key = $this->request->get('key');
        $optionsetid = $this->request->get('optionsetid');
        $attributeid = $this->request->get('attributeid');
        return $this->loader->getOptionSetValue($key, $optionsetid, $attributeid);
    }

    public function getRelatedValue($valueid, $entityrelated)
    {
        return $this->loader->getRelatedValue($valueid, $entityrelated);
    }

    public function getReqRelatedValue($valueid = '', $entityrelated = '')
    {
        $valueid = $this->request->get('valueid');
        $entityrelated = $this->request->get('entityrelated');
        return $this->loader->getRelatedValue($valueid, $entityrelated);
    }

    public function dataView($val, $type, $entityrelated = 0, $optionsetid = 0, $attributeid = 0)
    {
        $val = html_entity_decode($val);
        switch ($type) {
            case 'INT':
            case 'BIGINT':
            case 'FLOAT':
            case 'DOUBLE':
                $val = $this->string->numberFormate($val);
                break;
            case 'DATE':
                $val = $this->date->formatMySQLDate($val);
                break;
            case 'DATETIME':
                $val = $this->date->formatMySQLDate($val, "longdate");
                break;
            case 'TIME':
                $val = $this->date->formatTime($val);
                break;
            case 'relatedto':
            case 'relatedtomulti':
                $val = $this->getRelatedValue($val, $entityrelated);
                break;
            case 'optionset':
            case 'optionsetmulti':
                $val = $this->getOptionSetValue($val, $optionsetid, $attributeid);
                break;
            case 'keyvalue':
                $val = str_replace('\"', '"', $val);
                if ($val != '') {
                    $arr = json_decode($val, true);
                    $str = array();
                    foreach ($arr as $key => $val) {
                        $str[] = "$key: $val";
                    }
                    $val = implode(' | ', $str);
                }
                break;
            case 'TEXT':
            case 'LONGTEXT':
                $val = str_replace('\"', '"', $val);
                $val = str_replace('\r\n', "<br>", $val);
                $val = str_replace('\n', "<br>", $val);
                break;
            case 'image':
                if (empty($this->model->entity)) {
                    $entityid = $this->request->get('entityid');
                    $entity = $this->model->getEntity($entityid);
                } else {
                    $entity = $this->model->entity;
                }
                $val = '<img src="' . IMAGESERVER . 'autosize-500x500/upload/' . $entity['tablename'] . '/' . $this->request->get('id') . '/' . $val . '">';
                break;
            case 'imagemulti':
                $val = str_replace('\"', '"', $val);
//                if ($val != '') {
//                    $arr = json_decode($val, true);
//                    $arr_result = array();
//                    foreach ($arr as $image) {
//                        $str = array();
//                        foreach ($image as $key => $item){
//                            $str[] = "$key: $item";
//                        }
//                        $arr_result[] = implode('<br>',$str);
//                    }
//                    $val = '<div>'.implode('<br>', $arr_result).'</div>';
//                }
                break;
            case 'code':
                $val = '<pre>' . base64_decode($val) . '</pre>';
                break;
            case 'BOOLEAN':
                $val = $val == 1 ? '<i class="fa fa-check"></i>' : '';
                break;
            case 'PASSWORD':
                $val = '******************';
                break;
        }
        return $val;
    }

    public function dataRaw($val, $type, $entityrelated = 0, $optionsetid = 0, $attributeid = 0)
    {
        switch ($type) {
//            case 'INT':
//            case 'BIGINT':
//            case 'FLOAT':
//            case 'DOUBLE':
//                $val = $this->string->numberFormate($val);
//                break;
//            case 'DATE':
//                $val = $this->date->formatMySQLDate($val);
//                break;
//            case 'DATETIME':
//                $val = $this->date->formatMySQLDate($val, "longdate");
//                break;
//            case 'TIME':
//                $val = $this->date->formatTime($val);
//                break;
            case 'relatedto':
            case 'relatedtomulti':
                if (!empty($val)) {
                    $val = strip_tags($this->getRelatedValue($val, $entityrelated));
                }

                break;
            case 'optionset':
            case 'optionsetmulti':
                if (!empty($val)) {
                    $val = strip_tags($this->getOptionSetValue($val, $optionsetid, $attributeid));
                }
                break;
            case 'keyvalue':
                if ($val != '') {
                    $arr = json_decode($val, true);
                    if (!empty($arr)) {
                        $str = array();
                        foreach ($arr as $key => $val) {
                            $str[] = "$key: $val";
                        }
                        $val = implode(' | ', $str);
                    } else {
                        $val = '';
                    }

                }
                break;
            case 'image':
                if (!empty($val)) {
                    $val = IMAGESERVER . 'autosize-500x500/upload/' . $this->model->entity['tablename'] . '/' . $this->request->get('id') . '/' . $val;
                }

                break;
            case 'code':
                $val = '<pre>' . base64_decode($val) . '</pre>';
                break;
//            case 'BOOLEAN':
//                $val = $val == 1 ? '<i class="fa fa-check"></i>' : '';
//                break;
            case 'PASSWORD':
                $val = '******************';
                break;
        }
        return $val;
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param mixed $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return mixed
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param mixed $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * @return mixed
     */
    public function getData($key)
    {
        if (!empty($this[$key]))
            return $this->data[$key];
        else
            return "";
    }

    /**
     * @param mixed $data
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function render()
    {
        $output = '';
        if (!empty($this->view)) {
            $filename = VIEW . $this->view;
            extract($this->data);
            ob_start();
            require($filename);
            $output = ob_get_contents();
            ob_end_clean();
        }


        if (!empty($this->layout)) {
            $filename = VIEW . $this->layout;
            extract($this->data);
            $view_content = $output;
            ob_start();
            require($filename);
            $output = ob_get_contents();
            ob_end_clean();
        }
        return $output;
    }

    public function Save()
    {
        $data = $this->request->getDataPost();
        $result = $this->model->save($data);
        $cache = new Cache();
        if ($this->model->entity['structure'] == 'tree') {
            $cache->delete($this->model->entity['classname'] . '.tpl');
        }
        $cache->clearClass($this->model->entity['classname']);
        $cache->newVersion();
        return json_encode($result);
    }

    public function convertData($attribute, $value)
    {
        if ($attribute['entityrelated']) {
            $entityrelated = $this->model->getEntity($attribute['entityrelated']);
            $this->loader->loadModel($entityrelated['entitytype'], $entityrelated['classname']);
            $modelclass = "Model\\" . $entityrelated['classname'] . "Model";
            $modelRelated = new $modelclass();
            $maincol = $entityrelated['maincol'];
            $main_attribute = $this->model->getAttributeById($maincol);
            $mainname = $main_attribute['attributename'];
        }
        if ($attribute['optionsetid']) {
            $optionsetid = $attribute['optionsetid'];
            $modelOptionSet = new OptionSetModel();
            $optionSet = $modelOptionSet->getItem($optionsetid);
            $optionsetvalue = json_decode($this->string->formateJson($optionSet['optionsetvalue']), true);
        }
        switch ($attribute['datatype']) {
            case 'optionset':
                if ($optionsetvalue != null) {
                    foreach ($optionsetvalue as $key => $val) {
                        if ($val == $value) {
                            return $key;
                        }
                    }
                }
                break;
            case 'optionsetmulti':
                $arrValue = explode(',', $value);
                $arr = array();
                if ($optionsetvalue != null) {
                    foreach ($optionsetvalue as $key => $val) {
                        foreach ($arrValue as $v) {
                            if (trim($v) == $val) {
                                $arr[] = $key;
                            }
                        }
                    }
                }
                return $this->string->arrayToString($arr);
                break;
            case 'relatedto':
                $where = " AND `$mainname` = '$value'";
                $result = $modelRelated->getList($where);
                if (empty($result)) {
                    return "";
                } else {
                    return $result[0]['id'];
                }
                break;
            case 'relatedtomulti':
                $arrValue = explode(',', $value);
                foreach ($arrValue as $key => $val) {
                    $arrValue[$key] = trim($val);
                }
                $arr = array();
                $where = " AND `$mainname` in ('" . implode("','", $arrValue) . "')";
                $result = $modelRelated->getList($where);
                foreach ($result as $item) {
                    $arr[] = $item['id'];
                }
                return $this->string->arrayToString($arr);
                break;
            case 'image':

                break;
            case 'imagemulti':

                break;
            case 'attachment':

                break;
            case 'keyvalue':
                $arrValue = explode('|', $value);
                $data = array();
                if (!empty($arrValue)) {
                    foreach ($arrValue as $key => $val) {
                        $arrValue[$key] = trim($val);
                        $arr = explode(":", trim($val));
                        if (isset($arr[1])) {
                            $data[trim($arr[0])] = trim($arr[1]);
                        }
                    }
                }
                return json_encode($data);
                break;
        }
    }

    public function Import()
    {
        require COMPONENT . "vendor/autoload.php";
        $data = $this->request->getDataPost();
        $datamapping = array();
        foreach ($data as $col => $value) {
            if ($col != 'id') {
                $attribute = $this->model->getAttributeByName($col);
                //print_r($attribute);
                //Mapping data
                switch ($attribute['datatype']) {
                    case 'optionset':
                    case 'optionsetmulti':
                    case 'relatedto':
                    case 'relatedtomulti':
                    case 'image':
                    case 'imagemulti':
                    case 'attachment':
                    case 'keyvalue':
                        $datamapping[$col] = $this->convertData($attribute, $value);
                        break;
                    case 'DATE':
                        if (is_numeric($value)) {
                            $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value);
                            $strdate = date('Y-m-d', $timestamp);
                            $datamapping[$col] = $this->date->formatMySQLDate($strdate);
                        } else {
                            $datamapping[$col] = $value;
                        }

                        break;
                    case 'PASSWORD':
                        $datamapping[$col] = $this->auth->encryptionPassword($value);
                        break;
                    default:
                        $datamapping[$col] = $value;
                }
            } else {
                $datamapping[$col] = $value;
            }

        }

        $result = $this->model->save($datamapping);
        return json_encode($result);
    }

    public function Export()
    {
        $data = $this->request->getDataPost();
        unset($data['route']);
        $where = "";
        foreach ($this->model->arr_col as $col => $type) {
            if (!empty($data[$col])) {
                $arr = explode('_', $data[$col]);
                $operator = $arr[0];
                $val = $this->formateValue($type, $arr[1]);
                $condition = $this->model->genCondition($this->model->entity['tablename'].'.'.$col, $operator, $val);
                $where .= " AND $condition";
            }
        }
        if (empty($data['sortcol'])) {
            $where .= " ORDER BY `id` ASC";
        } else {
            $where .= " ORDER BY " . $data['sortcol'] . " " . $data['sorttype'];
        }
        $list = $this->model->getList($where);
        $columname[] = 'ID';
        foreach ($this->model->entity['attributes'] as $attribute) {
            $columname[] = $attribute['attributelabel'];
        }
        foreach ($this->model->coreAttributes as $attribute) {
            $columname[] = $attribute['attributelabel'];
        }
        $dataexport[] = $columname;
        foreach ($list as $item) {
            $row = array();
            $row['ID'] = $item['id'];
            foreach ($this->model->entity['attributes'] as $attribute) {
                $value = $item[$attribute['attributename']];
                $row[$attribute['attributename']] = $this->dataRaw($value, $attribute['datatype'], $attribute['entityrelated'], $attribute['optionsetid'], $attribute['id']);
            }
            foreach ($this->model->coreAttributes as $attribute) {
                $value = $item[$attribute['attributename']];
                $row[$attribute['attributename']] = $this->dataRaw($value, $attribute['datatype'], $attribute['entityrelated'], 0, $attribute['id']);
            }
            $dataexport[] = $row;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($dataexport, NULL);
        $writer = new Xlsx($spreadsheet);
        $dir = FILESERVER . 'cache/';
        $filename = $dir . time() . '.xlsx';
        $writer->save($filename);
        return json_encode(array(
            'statuscode' => 1,
            'text' => 'Export complete',
            'link' => HTTPSERVER . $filename
        ));
    }

    public function Delete()
    {
        $id = $this->request->get('id');
        if ($id != '') {
            $item = $this->model->getItem($id);
            if (!empty($item)) {
                return $this->model->delete($id);
            } else {
                return json_encode(array(
                    'statuscode' => 0,
                    'text' => 'Id is not exist!',
                    'data' => array()
                ));
            }
        } else {
            return json_encode(array(
                'statuscode' => 0,
                'text' => 'Id is empty',
                'data' => array()
            ));
        }
    }

    public function Edit()
    {
        $id = $this->request->get('id');
        $formname = $this->request->get('formname');
        $this->setData('id', $id);
        $form = $this->loader->loadController(
            $this->path,
            $this->classname,
            'loadForm',
            array('id' => $id, 'formname' => $formname)
        );
        $this->setData('form', $form);
        $this->setView($this->path . '/' . $this->classname . '/PageForm.tpl');

        $appjs = $this->loader->loadController("Common", "Header", 'loadAppJS');
        $this->setData('appjs', $appjs);
        $this->header = $this->loader->loadController("Common", "Header");
        $this->footer = $this->loader->loadController("Common", "Footer");
        $this->setData('header', $this->header);
        $this->setData('footer', $this->footer);
        $this->setLayout('Layout/home.tpl');
        return $this->render();
    }

    public function loadForm($data = array())
    {
        $id = $this->request->get('id');
        if (empty($data)) {
            $this->setData('id', $id);
        } else {
            $this->setData('id', $data['id']);
        }
        $formname = $this->request->get('formname');
        if (!empty($data)) {
            $formname = $data['formname'];
        }
        if ($formname == '') {
            $this->setView($this->path . '/' . $this->classname . '/Form.tpl');
        } else {
            $this->setView($this->path . '/' . $this->classname . '/Form_' . $formname . '.tpl');
        }

        return $this->render();
    }

    public function loadView($data)
    {
        $this->setData('list', $data['list']);
        $this->setData('viewtemplate', $data['viewtemplate']);
        $this->setData('pagination', $data['pagination']);
        $this->setView($data['view']);
        return $this->render();
    }

    public function Insert()
    {
        $this->setView($this->path . '/' . $this->classname . '/QuickForm.tpl');
        return $this->render();
    }

    public function View()
    {
        $id = $this->request->get('id');
        $this->setData('id', $id);
        $formname = $this->request->get('formname');
        $form = $this->loader->loadController(
            $this->path,
            $this->classname,
            'loadViewItem',
            array('id' => $id, 'formname' => $formname)
        );
        $this->setData('form', $form);
        $this->setView($this->path . '/' . $this->classname . '/PageDetail.tpl');

        $appjs = $this->loader->loadController("Common", "Header", 'loadAppJS');
        $this->setData('appjs', $appjs);
        $this->header = $this->loader->loadController("Common", "Header");
        $this->footer = $this->loader->loadController("Common", "Footer");
        $this->setData('header', $this->header);
        $this->setData('footer', $this->footer);
        $this->setLayout('Layout/home.tpl');
        return $this->render();
    }

    public function loadViewItem($data = array())
    {
        $id = $this->request->get('id');
        $item = $this->model->getItem($id);
        if (empty($data)) {
            $this->setData('id', $id);
        } else {
            $this->setData('id', $data['id']);
            $this->setData('item', $item);
        }
        $formname = $this->request->get('formname');
        if ($formname != '') {
            $formname = $data['formname'];
            $this->setView($this->path . '/' . $this->classname . "/ViewItem_$formname.tpl");
        } else {
            $this->setView($this->path . '/' . $this->classname . '/ViewItem.tpl');
        }


        return $this->render();
    }

    public function getItem()
    {
        $dataget = $this->request->getDataGet();
        $cachefilename = $this->model->entity['classname'] . '_' . md5(json_encode($dataget)) . '.json';
        $cache = new Cache();
        $result = $cache->get($cachefilename);

        if (empty($result) || $this->iscache) {
            $id = $this->request->get('id');
            if ($id != '') {
                $item = $this->model->getItem($id);
                foreach ($item as &$val) {
                    $val = str_replace('\"', '"', $val);
                }
                $list = $this->updateDataView(array($item));
                $item = $list[0];
                if (!empty($item)) {
                    $result = json_encode(array(
                        'statuscode' => 1,
                        'text' => 'Get data success',
                        'data' => $item
                    ));
                    if ($this->iscache) {
                        $cache->create($cachefilename, $result);
                    }
                    return $result;
                } else {
                    return array(
                        'statuscode' => 0,
                        'text' => 'Id is not exist!',
                        'data' => array()
                    );
                }
            } else {
                return array(
                    'statuscode' => 0,
                    'text' => 'Id is empty',
                    'data' => array()
                );
            }
        } else {
            return $result;
        }
    }

    public function getCountItem()
    {
        $where = "";
        $data = $this->request->getDataGet();
        foreach ($this->model->arr_col as $col => $type) {
            if (!empty($data[$col])) {
                $arr = explode('_', $data[$col]);
                $operator = $arr[0];
                $val = $this->formateValue($type, $arr[1]);
                $condition = $this->model->genCondition($col, $operator, $val);
                $where .= " AND $condition";
            }
        }
        $count = $this->model->countTotal($where);
        return json_encode(array(
            'statuscode' => 1,
            'text' => 'Get data success',
            'count' => $count
        ));
    }

    public function getUserView()
    {
        $viewDefault = $this->template->getViewDefault($this->model->entity['id']);
        $where = " AND userid = " . $this->auth->userInfor['id'] . " AND entityid = " . $this->model->entity['id'];
        $userViews = $this->userViewModel->getList($where);
        $data = array();
        $data[0] = array(
            'id' => 0,
            'viewname' => $viewDefault['templatename'],
            'viewcontent' => $viewDefault['templatecontent']
        );
        foreach ($userViews as $userView) {

            $data[$userView['id']] = array(
                'id' => $userView['id'],
                'viewname' => $userView['viewname'],
                'viewcontent' => base64_decode($userView['viewcontent'])
            );
        }
        return $data;
    }

    public function index()
    {
        if ($this->path == "PageView") {
            $content = $this->loader->loadPage($this->classname);
            $this->setData('content', $content);
            $this->setView('PageView/View.tpl');
            $appjs = $this->loader->loadController("Common", "Header", 'loadAppJS');
            $this->setData('appjs', $appjs);
            $this->header = $this->loader->loadController("Common", "Header");
            $this->footer = $this->loader->loadController("Common", "Footer");
            $this->setData('header', $this->header);
            $this->setData('footer', $this->footer);
            $this->setLayout('Layout/home.tpl');
            return $this->render();
        } else {
            if (empty($this->model->entity)) {
                $this->setView('Core/Entity/AccessDenied.tpl');
                return $this->render();
            } else {
                $listView = 'ListView';
                if ($this->model->entity['structure'] == 'tree') {

                    $listView = 'TreeView';
                    $list = $this->model->getList();
                    $view = $this->loader->loadController(
                        $this->path,
                        $this->classname,
                        'loadView',
                        array(
                            'view' => $this->path . "/" . $this->classname . "/$listView.tpl",
                            'list' => $list,
                            'viewtemplate' => array(),
                            'pagination' => ''
                        )
                    );
                } else {
                    $data = $this->request->getDataGet();
                    $where = "";
                    $arrcol = $this->model->arr_col;
                    foreach ($this->model->coreAttributes as $attribute) {
                        $arrcol[$attribute['attributename']] = $attribute['datatype'];
                    }
                    foreach ($arrcol as $col => $type) {
                        if (!empty($data[$col])) {
                            $arr = explode('_', $data[$col]);
                            $operator = $arr[0];
                            if (isset($arr[1])) {
                                $val = $this->formateValue($type, $arr[1]);
                            } else {
                                $val = '';
                            }
                            $condition = $this->model->genCondition($this->model->entity['tablename'] . '.' . $col, $operator, $val);
                            $where .= " AND $condition";
                        }
                    }
                    $this->pagination->total = $this->model->countTotal($where);

                    if ($this->request->get('page') != '' && $this->validation->checkNumberOnly($this->request->get('page'))) {
                        $this->pagination->page = intval($this->request->get('page'));
                    }

                    $this->pagination->url = $this->request->getQueryString();
                    $from = ($this->pagination->page - 1) * $this->pagination->limit;
                    $to = $this->pagination->limit;
                    $this->setData('pagination', $this->pagination->render());
                    if (!empty($data['sortcol'])) {
                        $where .= " ORDER BY " . $data['sortcol'] . " " . $data['sorttype'];
                    }

                    if ($this->model->entity['entitytype'] != 'Core') {
                        $userViews = $this->getUserView();
                        $viewid = empty($this->request->get('viewid')) ? 0 : $this->request->get('viewid');
                        $viewtemplate = json_decode($userViews[$viewid]['viewcontent'], true);
                        if (empty($data['sortcol'])) {
                            if (isset($viewtemplate['sort']) && !empty($viewtemplate['sort'])) {
                                $arr_sort = array();
                                foreach ($viewtemplate['sort'] as $colsort) {
                                    $tablename = $this->model->entity['tablename'];
//                            if(is_numeric($colsort['attributeid'])){
//                                $attribute = $this->model->getAttributeById($colsort['attributeid']);
//                                $entityid = $attribute['entityid'];
//                                $entity = $this->model->getEntity($entityid);
//                                $tablename = $entity['tablename'];
//                            }
                                    $arr_sort[] = $colsort['attributename'] . ' ' . $colsort['sorttype'];
                                }
                                $where .= "ORDER BY " . implode(',', $arr_sort);
                            }
                        }
                        $list = $this->model->getList($where, $from, $to, $viewtemplate);
                        $this->setData('viewid', $viewid);
                        $this->setData('userViews', $userViews);
                    } else {
                        $template = $this->template->getViewDefault($this->model->entity['id']);
                        $viewtemplate = json_decode($template['templatecontent'], true);
                        if (empty($data['sortcol'])) {
                            if (isset($viewtemplate['sort']) && !empty($viewtemplate['sort'])) {
                                $arr_sort = array();
                                foreach ($viewtemplate['sort'] as $colsort) {
                                    $attributeid = $colsort['attributeid'];
                                    if (is_int($attributeid)) {
                                        $attribute = $this->model->getAttributeById($attributeid);
                                        $entityid = $attribute['entityid'];
                                        $relateEntity = $this->model->getEntity($entityid);
                                        $arr_sort[] = $colsort['attributename'] . ' ' . $colsort['sorttype'];
                                    } else {
                                        $arr_sort[] = $colsort['attributename'] . ' ' . $colsort['sorttype'];
                                    }

                                }
                                $where .= "ORDER BY " . implode(',', $arr_sort);
                            }
                        }
                        $list = $this->model->getList($where, $from, $to, $viewtemplate);
                    }


                    $view = $this->loader->loadController(
                        $this->path,
                        $this->classname,
                        'loadView',
                        array(
                            'view' => $this->path . "/" . $this->classname . "/$listView.tpl",
                            'list' => $list,
                            'viewtemplate' => $viewtemplate,
                            'pagination' => $this->pagination->render()
                        )
                    );
                }

                $this->setData('view', $view);

                $this->setView($this->path . '/' . $this->classname . '/PageList.tpl');
                $appjs = $this->loader->loadController("Common", "Header", 'loadAppJS');
                $this->setData('appjs', $appjs);
                $this->header = $this->loader->loadController("Common", "Header");
                $this->footer = $this->loader->loadController("Common", "Footer");
                $this->setData('header', $this->header);
                $this->setData('footer', $this->footer);
                $this->setLayout('Layout/home.tpl');
                return $this->render();
            }
        }


    }

    public function getList()
    {
        $dataget = $this->request->getDataGet();
        $cachefilename = $this->model->entity['classname'] . '_' . md5(json_encode($dataget)) . '.json';
        $cache = new Cache();
        $result = $cache->get($cachefilename);
        //$result = '';
        if (empty($result) || !$this->iscache) {
            $templateid = $this->request->get('templateid');
            $from = $this->request->get('from') == '' ? 0 : $this->request->get('from');
            $to = $this->request->get('to') == '' ? 0 : $this->request->get('to');
            $template = array();
            if ($templateid) {
                $template = $this->template->getItem($templateid);
            }
            $data = $this->request->getDataGet();
            $where = "";
            $arrcol = $this->model->arr_col;
            foreach ($this->model->coreAttributes as $attribute) {
                $arrcol[$attribute['attributename']] = $attribute['datatype'];
            }
            if (isset($data['id'])) {
                $arrcol['id'] = 'BIGINT';
            }
            foreach ($arrcol as $col => $type) {
                if (!empty($data[$col])) {
                    $arr = explode('_', $data[$col]);
                    $operator = $arr[0];
                    if (isset($arr[1])) {
                        $val = $this->formateValue($type, $arr[1]);
                    } else {
                        $val = '';
                    }

                    $condition = $this->model->genCondition($this->model->entity['tablename'] . '.' . $col, $operator, $val);
                    if (!empty($condition)) {
                        $where .= " AND $condition";
                    }

                }
            }
            if ($this->request->get('paging') == 'true') {
                $limit = $this->request->get('limit');
                if ($limit) {
                    $this->pagination->limit = $limit;
                }
                $this->pagination->total = $this->model->countTotal($where);
                if ($this->request->get('page') != '' && $this->validation->checkNumberOnly($this->request->get('page'))) {
                    $this->pagination->page = intval($this->request->get('page'));
                }
                $this->pagination->url = $this->request->getQueryString();
                $from = ($this->pagination->page - 1) * $this->pagination->limit;
                $to = $this->pagination->limit;

                if (empty($data['sortcol'])) {
                    $where .= " ORDER BY " . $this->model->entity['tablename'] . ".`id` ASC";
                } else {
                    $where .= " ORDER BY " . $data['sortcol'] . " " . $data['sorttype'];
                }
                if (isset($template['templatecontent'])) {
                    $templatecontent = json_decode($template['templatecontent'], true);
                    $list = $this->model->getList($where, $from, $to, $templatecontent);
                } else {
                    $list = $this->model->getList($where, $from, $to);
                }

                $result = json_encode(array(
                    'statuscode' => 1,
                    'text' => 'Get data success',
                    'data' => $this->updateDataView($list),
                    'pagination' => $this->pagination->pageData(),
                    'paginationajax' => $this->pagination->ajaxRender()
                ));
                if ($this->iscache) {
                    $cache->create($cachefilename, $result);
                }
                return $result;
            } else {

                if (empty($data['sortcol'])) {
                    $where .= " ORDER BY `id` ASC";
                } else {
                    $where .= " ORDER BY " . $data['sortcol'] . " " . $data['sorttype'];
                }
                $list = $this->model->getList($where, $from, $to);
                foreach ($this->model->corecol as $col => $item) {
                    $this->model->arr_col[$col] = $item['datatype'];
                }
                $result = json_encode(array(
                    'statuscode' => 1,
                    'text' => 'Get data success',
                    'data' => $this->updateDataView($list)
                ));
                if ($this->iscache) {
                    $cache->create($cachefilename, $result);
                }
                return $result;
            }
        } else {
            return $result;
        }
    }

    private function updateDataView($list)
    {
        foreach ($list as &$row) {
            foreach ($row as $col => $val) {
                if (isset($this->model->arr_col[$col])) {
                    $datatype = $this->model->arr_col[$col];
                    //echo "$col - $datatype - $val".PHP_EOL;
                    switch ($datatype) {
                        case 'relatedto':
                            if (!isset($this->model->corecol[$col])) {
                                $attribute = $this->model->getAttributeByName($col);
                                $relatevalue = $this->getRelatedValue($val, $attribute['entityrelated']);
                            } else {
                                $relatevalue = $this->getRelatedValue($val, 1);
                            }
                            $row[$col . '_text'] = $relatevalue != null ? strip_tags($relatevalue) : '';
                            break;
                        case 'relatedtomulti':
                            $arr = $this->string->stringToArray($val);
                            $entityrelatedid = 1;
                            if (!isset($this->model->corecol[$col])) {
                                $entityrelated = $this->model->getAttributeByName($col);
                                $entityrelatedid = $entityrelated['entityrelated'];
                            }
                            $relatevalue = array();
                            foreach ($arr as $item) {
                                $relatevalue[] = $this->getRelatedValue($item, $entityrelatedid);
                            }

                            $row[$col . '_text'] = strip_tags(implode(',', $relatevalue));
                            break;
                        case 'optionset':
                            $attribute = $this->model->getAttributeByName($col);
                            $optionSetValue = $this->getOptionSetValue($val, $attribute['optionsetid'], $attribute['id']);

                            $row[$col . '_text'] = $optionSetValue;
                            break;
                    }
                }

            }
        }
        return $list;
    }

    public function updateTree()
    {

        $data = $this->request->post('data');
        if ($data != '') {
            $this->tree['maincol'] = $this->request->post('maincol');
            $this->tree['parentcol'] = $this->request->post('parentcol');
            $this->tree['sortordercol'] = $this->request->post('sortordercol');
            if ($data != '') {
                $data = json_decode($data, true);
                $this->travelTree(0, $data);
            }

            foreach ($this->tree['updatedata'] as $item) {
                $this->model->updateCol($item['id'], $this->tree['parentcol'], $item[$this->tree['parentcol']]);
                $this->model->updateCol($item['id'], $this->tree['sortordercol'], $item[$this->tree['sortordercol']] + 1);
            }
        }
        $cache = new Cache();
        $cache->clearClass($this->model->entity['classname']);
        $cache->newVersion();
        return json_encode(array(
            'statuscode' => 1,
            'text' => 'Update data success',
            'data' => $this->tree
        ));
    }

    public function getChilds()
    {
        $parent = $this->request->get('parent');
        $childs = $this->model->getChilds($parent);
        return json_encode(array(
            'statuscode' => 1,
            'text' => 'Update data success',
            'data' => $childs
        ));
    }

    public function getTree()
    {
        $rootid = (int)$this->request->get('rootid');
        $data = array();
        $this->model->travel($rootid, $data);
        return json_encode(array(
            'statuscode' => 1,
            'text' => 'Update data success',
            'data' => $data
        ));
    }

    public function destroyBulk()
    {
        if ($this->auth->userInfor['group'] == 1) {
            $data = $this->request->getDataGet();
            $where = "";
            $arrcol = $this->model->arr_col;
            foreach ($this->model->coreAttributes as $attribute) {
                $arrcol[$attribute['attributename']] = $attribute['datatype'];
            }
            if (isset($data['id'])) {
                $arrcol['id'] = 'BIGINT';
            }
            foreach ($arrcol as $col => $type) {
                if (!empty($data[$col])) {
                    $arr = explode('_', $data[$col]);
                    $operator = $arr[0];
                    if (isset($arr[1])) {
                        $val = $this->formateValue($type, $arr[1]);
                    } else {
                        $val = '';
                    }

                    $condition = $this->model->genCondition($this->model->entity['tablename'] . '.' . $col, $operator, $val);
                    if (!empty($condition)) {
                        $where .= " AND $condition";
                    }
                }
            }
            $this->model->destroyBulk($where);
            $result = array(
                'statuscode' => 1,
                'text' => 'Destroy data success',
                'data' => []
            );
        } else {
            $result = array(
                'statuscode' => 0,
                'text' => 'Destroy data faild!',
                'data' => []
            );
        }
        return $result;
    }
}
