<?php

namespace Differ\RenderPlain;

use function Differ\stringify;

function renderPlain($arrayFromFile1, $arrayFromFile2)
{
    $astTree = genPlainDiffAst($arrayFromFile1, $arrayFromFile2);
    $resultString = genPlainStringIsAst($astTree);
    return $resultString;
}

function genPlainDiffAst($firstFileArray, $secondFileArray)
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
                            "diff" => "=",
                            "children" => $secondFileArray[$key]
                        ];
                    } else {
                        $item[] = [
                            "key" => "$key",
                            "diff" => "-+",
                            "children" => $firstFileArray[$key],
                            "children2"=> "$secondFileArray[$key]"

                        ];
                    }
                } else {
                    $item[] = [
                        "key" => "$key",
                        "diff" => "=",
                        "children" => genPlainDiffAst($firstFileArray[$key], $secondFileArray[$key])
                    ];
                }
            } else {
                if (is_array($firstFileArray[$key])) {
                    $item[] = [
                        "key" => "$key",
                        "diff" => "-",
                        "children" => genPlainDiffAst($firstFileArray[$key], [])
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
                    "children" => genPlainDiffAst([], $secondFileArray[$key])
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

function genPlainStringIsAst($astTree, $path = "")
{
    $funcArrayReduce = function ($item, $value) use ($path) {
        if ($value['diff'] == "-") {
            $item[] = "Property '$path{$value['key']}' was removed" . PHP_EOL;
            if (is_array($value['children'])) {
                $pathActual = $path . "{$value['key']}.";
                $item[] = genPlainStringIsAst($value['children'], $pathActual);
            }
        } elseif ($value['diff'] == "+") {
            if (is_array($value['children'])) {
                $item[]  = "Property '$path{$value['key']}' was added with value: 'complex value' "
                    . PHP_EOL;
                $pathActual = $path . "{$value['key']}.";
                $item[] = genPlainStringIsAst($value['children'], $pathActual);
            } else {
                $item[] = "Property '$path{$value['key']}' was added with value: '" . stringify($value['children'])
                . "' " . PHP_EOL;
            }
        } elseif ($value['diff'] == "=") {
            if (is_array($value['children'])) {
                $pathActual = $path . "{$value['key']}.";
                $item[] = genPlainStringIsAst($value['children'], $pathActual);
            } else {
                $item[] = "";
            }
        } elseif ($value['diff'] == "-+") {
            $item[] = "Property '$path{$value['key']}' was changed. From '" . stringify($value['children']) . "' to "
                . "'{$value['children2']}' " . PHP_EOL;
        }
        return $item;
    };
    $arrResult = array_reduce($astTree, $funcArrayReduce);
    $strResult = implode('', $arrResult);
    return $strResult;
}
