<?php

namespace App\Traits;

trait GeneralTrait
{
    function phoneValidate($number = '')
    {
        if (substr($number, 0, 1) === '0') {
            $number = substr($number, 1);
        }
        if (substr($number, 0, 4) === '+966') {
            $number = substr($number, 4);
        }
        if (substr($number, 0, 4) === '0966') {
            $number = substr($number, 4);
        }
        if (substr($number, 0, 3) === '+20') {
            $number = substr($number, 3);
        }
        if (substr($number, 0, 3) === '020') {
            $number = substr($number, 3);
        }
        $phone = preg_replace('/\s+/', '', $number);
        return $phone;
    }

    function convert2english($string)
    {
        $newNumbers = range(0, 9);
        $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $string = str_replace($arabic, $newNumbers, $string);
        return $string;
    }
}
