<?php

namespace System\Helpers;

class Logic {
    
    public static $elements = [];
    public static $three = [];
    
    public static function recursionGet($data,$parentName){
        foreach ($data as $element){
            $element = $element->toArray();
            self::$elements[$element['id']] = $element;
        }
        foreach(self::$elements as $element){
            if($element[$parentName] == 0){
                $element['childs'] = self::recursion($element,$parentName);
                if(!$element['childs']){
                    unset($element['childs']);
                }
                self::$three[] = $element;
            }
        }

        return self::$three;
    }
    
    public static function recursion($elem,$parentName){
        $compl = [];
        $stat = false;
        foreach(self::$elements as $element){if($elem['id'] == $element[$parentName]){ $stat = TRUE; }}
        if($stat){
            foreach(self::$elements as $element){
                if($elem['id'] == $element[$parentName]){
                    $element['childs'] = self::recursion($element,$parentName);
                    if(!$element['childs']){
                        unset($element['childs']);
                    }
                    $compl[] = $element;
                }
            }
            return $compl;
        } else { return []; }
    }
    
}
