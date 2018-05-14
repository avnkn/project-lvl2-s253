<?php

namespace Differ\RenderPretty;

use function Differ\stringify;

function renderPretty($arrayFromFile1, $arrayFromFile2)
{
    $astTree = genPrettyDiffAst($arrayFromFile1, $arrayFromFile2);
    $resultString = genPrettyStringIsAst($astTree);
    return $resultString;
}

function genPrettyDiffAst($firstFileArray, $secondFileArray)
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
                            "diff" => " ",
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
                        "diff" => " ",
                        "children" => genPrettyDiffAst($firstFileArray[$key], $secondFileArray[$key])
                    ];
                }
            } else {
                if (is_array($firstFileArray[$key])) {
                    $item[] = [
                        "key" => "$key",
                        "diff" => "-",
                        "children" => genPrettyDiffAst($firstFileArray[$key], $firstFileArray[$key])
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
                    "children" => genPrettyDiffAst($secondFileArray[$key], $secondFileArray[$key])
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

function genPrettyStringIsAst($astTree, $indent = "")
{
    $initial[] = "{" . PHP_EOL;
    $funcArrayReduce = function ($item, $value) use ($indent) {
        if (is_array($value['children'])) {
            $item[] = $indent . "  " . $value['diff'] . " " . $value['key'] . ": ";
            $indentActual = $indent . "    ";
            $item[] = genPrettyStringIsAst($value['children'], $indentActual);
        } else {
            $item[] = $indent . "  " . $value['diff'] . " " . $value['key'] . ": " . stringify($value['children'])
                . PHP_EOL;
        }
        return $item;
    };
    $arrResult = array_reduce($astTree, $funcArrayReduce, $initial);
    $arrResult[] = $indent . "}" . PHP_EOL;

    $strResult = implode('', $arrResult);
    return $strResult;
}
