<?php
namespace Differ\AST;

function arrayUniqueKey($array1, $array2)
{
    $arrResult1 = array_keys($array1);
    $arrResult2 = array_keys($array2);
    $mergeKey = array_merge($arrResult1, $arrResult2);
    $unionKey = array_unique($mergeKey);
    return $unionKey;
}

function genAST($firstFileArray, $secondFileArray)
{
    $arrayUniqueKey = arrayUniqueKey($firstFileArray, $secondFileArray);
    if (!is_array($firstFileArray)) {
        $firstFileArray =[];
    }
    if (!is_array($secondFileArray)) {
        $secondFileArray = [];
    }
    $funcArrayReduce = function ($item, $key) use ($firstFileArray, $secondFileArray) {
        if (array_key_exists($key, $firstFileArray)) {
            if (array_key_exists($key, $secondFileArray)) {
                if (!is_array($firstFileArray[$key]) and !is_array($secondFileArray[$key])) {
                    if ($firstFileArray[$key] === $secondFileArray[$key]) {
                        $item[] = [
                            "key" => $key,
                            "type" => "not changed",
                            "oldValue" => $firstFileArray[$key],
                            "newValue" => $secondFileArray[$key],
                            "children" => null
                        ];
                    } else {
                        $item[] = [
                            "key" => $key,
                            "type" => "changed",
                            "oldValue" => $firstFileArray[$key],
                            "newValue" => $secondFileArray[$key],
                            "children" =>  null
                        ];
                    }
                } else {
                    $item[] = [
                        "key" => $key,
                        "type" => "not changed",
                        "oldValue" => "",
                        "newValue" => "",
                        "children" => genAST($firstFileArray[$key], $secondFileArray[$key])
                    ];
                }
            } else {
                if (is_array($firstFileArray[$key])) {
                    $item[] = [
                        "key" => $key,
                        "type" => "deleted",
                        "oldValue" => "",
                        "newValue" => "",
                        "children" => genAST($firstFileArray[$key], [])
                    ];
                } else {
                    $item[] = [
                        "key" => $key,
                        "type" => "deleted",
                        "oldValue" => $firstFileArray[$key],
                        "newValue" => "",
                        "children" =>  null
                    ];
                }
            }
        } else {
            if (is_array($secondFileArray[$key])) {
                $item[] = [
                    "key" => $key,
                    "type" => "inserted",
                    "oldValue" => "",
                    "newValue" => "",
                    "children" => genAST([], $secondFileArray[$key])
                ];
            } else {
                $item[] = [
                    "key" => $key,
                    "type" => "inserted",
                    "oldValue" => "",
                    "newValue" => $secondFileArray[$key],
                    "children" => null
                ];
            }
        }
        return $item;
    };
    $arrResult = array_reduce($arrayUniqueKey, $funcArrayReduce);
    return $arrResult;
}
