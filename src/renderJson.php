<?php

namespace Differ\RenderJson;

use function Differ\stringify;

function renderJson($arrayFromFile1, $arrayFromFile2)
{
    $astTree = genJsonDiffArray($arrayFromFile1, $arrayFromFile2);
    $resultArray = genJsonArrayIsAst($astTree);
    $resultString = json_encode($resultArray);
    return $resultString;
}

function genJsonDiffArray($firstFileArray, $secondFileArray)
{
    $unionArray = \Differ\unionKey($firstFileArray, $secondFileArray);
    $unionArrrayKey = array_keys($unionArray);
    $funcArrayReduce = function ($item, $key) use ($firstFileArray, $secondFileArray) {
        if (array_key_exists($key, $firstFileArray)) {
            if (array_key_exists($key, $secondFileArray)) {
                if (!is_array($firstFileArray[$key]) and !is_array($secondFileArray[$key])) {
                    if ($firstFileArray[$key] == $secondFileArray[$key]) {
                        $item[] = [
                            "key" => "$key",
                            "diff" => "",
                            "children" => $secondFileArray[$key]
                        ];
                    } else {
                        $item[] = [
                            "key" => "$key",
                            "diff" => "-",
                            "children" => $firstFileArray[$key]
                        ];
                        $item[] = [
                            "key" => "$key",
                            "diff" => "+",
                            "children" => $secondFileArray[$key]
                        ];
                    }
                } else {
                    $item[] = [
                        "key" => "$key",
                        "diff" => "",
                        "children" => genJsonDiffArray($firstFileArray[$key], $secondFileArray[$key])
                    ];
                }
            } else {
                if (is_array($firstFileArray[$key])) {
                    $item[] = [
                        "key" => "$key",
                        "diff" => "-",
                        "children" => genJsonDiffArray($firstFileArray[$key], $firstFileArray[$key])
                    ];
                } else {
                    $item[] = [
                        "key" => "$key",
                        "diff" => "-",
                        "children" => $firstFileArray[$key]
                    ];
                }
            }
        } else {
            if (is_array($secondFileArray[$key])) {
                $item[] = [
                    "key" => "$key",
                    "diff" => "+",
                    "children" => genJsonDiffArray($secondFileArray[$key], $secondFileArray[$key])
                ];
            } else {
                $item[] = [
                    "key" => "$key",
                    "diff" => "+",
                    "children" => $secondFileArray[$key]
                ];
            }
        }
        return $item;
    };

    $arrResult = array_reduce($unionArrrayKey, $funcArrayReduce);
    return $arrResult;
}

function genJsonArrayIsAst($astTree)
{
    $funcArrayReduce = function ($item, $value) {
        if (is_array($value['children'])) {
            $item[($value['diff'] . $value['key'])] = genJsonArrayIsAst($value['children'], $indentActual);
        } else {
            $item[($value['diff'] . $value['key'])] = stringify($value['children']);
        }
        return $item;
    };
    $arrResult = array_reduce($astTree, $funcArrayReduce, $initial);
    return $arrResult;
}
