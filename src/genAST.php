<?php
namespace Differ\AST;

function genAST($array1, $array2)
{
    $arrayUniqueKey = arrayUniqueKey($array1, $array2);
    if (!is_array($array1)) {
        $array1 =[];
    }
    if (!is_array($array2)) {
        $array2 = [];
    }
    $funcArrayReduce = function ($item, $key) use ($array1, $array2) {
        if (array_key_exists($key, $array1) and array_key_exists($key, $array2)) {
            if (!is_array($array1[$key]) and !is_array($array2[$key])) {
                if ($array1[$key] === $array2[$key]) {
                    $item[] = ["key" => $key, "type" => "not changed", "newValue" => $array2[$key]];
                } else {
                    $item[] = ["key" => $key, "type" => "changed", "oldValue" => $array1[$key],
                        "newValue" => $array2[$key]];
                }
            } else {
                $item[] = ["key" => $key, "type" => "nested", "children" => genAST($array1[$key], $array2[$key])];
            }
        } elseif (array_key_exists($key, $array1)) {
            $item[] = ["key" => $key, "type" => "deleted", "oldValue" => $array1[$key]];
        } else {
            $item[] = ["key" => $key, "type" => "added", "newValue" => $array2[$key]];
        }
        return $item;
    };
    $arrResult = array_reduce($arrayUniqueKey, $funcArrayReduce);
    return $arrResult;
}

function arrayUniqueKey($array1, $array2)
{
    $arrResult1 = array_keys($array1);
    $arrResult2 = array_keys($array2);
    $mergeKey = array_merge($arrResult1, $arrResult2);
    $unionKey = array_unique($mergeKey);
    return $unionKey;
}
