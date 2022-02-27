<?php
namespace Lib;
use Core\Model;

class Entity extends Model
{
    public function __construct($type,$classname)
    {
        parent::__construct();
        $this->entity = $this->getEntityByClassType($classname,$type);
        $this->tablename = $this->entity['tablename'];
        foreach ($this->entity['attributes'] as $attribute){
            $this->arr_col[$attribute['attributename']] = $attribute['datatype'];
        }
    }
}