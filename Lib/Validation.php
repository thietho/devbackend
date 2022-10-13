<?php

namespace Lib;
class Validation
{
    public function checkLetterOnly($string)
    {
        return (preg_match("/^[a-zA-Z]+$/", $string)) ? true : false;
    }

    public function isId($string)
    {
        return (preg_match("/^[a-zA-Z0-9]+$/", $string)) ? true : false;
    }

    public function checkLetterNunberOnly($string)
    {
        return (preg_match("/^[a-zA-Z0-9 ]+$/", $string)) ? true : false;
    }
    public function checkSpecialCharacters($string)
    {
        $string = htmlspecialchars($string);
        return (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)) ? true : false;
    }
    public function checkVietNam($string)
    {
        $string = htmlspecialchars($string);
        return (preg_match('/^([a-zA-Z0-9ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ\s]+)$/i', $string)) ? true : false;
    }
    public function checkTextOnly($string)
    {
        return (preg_match("/^[a-zA-Z0-9_* ]+$/", $string)) ? true : false;
    }

    public function checkEmail($string)
    {
        return (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $string)) ? true : false;
    }

    public function checkNumberOnly($string)
    {
        return (preg_match("/^[_0-9-]+$/", $string)) ? true : false;
    }

    public function isMySqlDate($stringdate)
    {
        return (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $stringdate)) ? true : false;
    }

    public function checkEmty($string)
    {
        return empty($string);
    }

    public function isExistURLAlias($keyword)
    {
        $sql = "SELECT * FROM url_alias WHERE keyword = '" . $keyword . "'";
        $query = $this->db->query($sql);
        return count($query->rows) > 0;
    }
}