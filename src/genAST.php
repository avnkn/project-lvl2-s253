<?php
namespace Differ\AST;

function genAST($firstFileArray, $secondFileArray)
{
    $unionArray = \Differ\unionKey($firstFileArray, $secondFileArray);
    $unionArrrayKey = array_keys($unionArray);
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
    $arrResult = array_reduce($unionArrrayKey, $funcArrayReduce);
    return $arrResult;
}
