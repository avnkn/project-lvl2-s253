<?php
namespace Differ;

use Differ\Parsers;
use \Exception;
use Funct\Collection;

function genDiff($pathToFile1, $pathToFile2, $format = "pretty")
{
    try {
        $arrayFromFile1 = getArray($pathToFile1);
        $arrayFromFile2 = getArray($pathToFile2);
    } catch (Exception $e) {
        fwrite(STDERR, $e->getMessage());
        return null;
    }
    $resultString = genResultString($arrayFromFile1, $arrayFromFile2, $format);
    return $resultString;
}
function genResultString($arrayFromFile1, $arrayFromFile2, $format)
{
    if ($format == 'plain') {
        $astTree = genPlainDiffAst($arrayFromFile1, $arrayFromFile2);
        $resultString = genPlainStringIsAst($astTree);
    } elseif ($format == 'json') {
        $astTree = genJsonDiffArray($arrayFromFile1, $arrayFromFile2);
        $resultArray = genJsonArrayIsAst($astTree);
        $resultString = json_encode($resultArray);
    } else {
        $astTree = genPrettyDiffAst($arrayFromFile1, $arrayFromFile2);
        $resultString = genPrettyStringIsAst($astTree);
    }
    return $resultString;
}

function getArray($pathToFile)
{
    $stringFromFile = getFileAsString($pathToFile);
    $extensionFile = getFileExtension($pathToFile);
    $arrayFromFile =  Parsers\parsingString($stringFromFile, $extensionFile);
    return $arrayFromFile;
}

function getFileAsString($filename)
{
    if (!file_exists($filename)) {
        throw new Exception("Error: File '$filename' does not exist" . PHP_EOL);
    }
    $stringFromFile = file_get_contents($filename);
    return $stringFromFile;
}

function getFileExtension($filename)
{
    $path_info = pathinfo($filename);
    return $path_info['extension'];
}

function unionKey($array1, $array2)
{
    $arrResult1 = array_map(null, $array1);
    $arrResult2 = array_map(null, $array2);
    $unionKey = array_merge($arrResult1, $arrResult2);
    return $unionKey;
}

function stringify($arg)
{
    if (is_bool($arg)) {
        if ($arg == true) {
            $result = 'true';
        } else {
            $result = 'false';
        }
    } else {
        $result = $arg;
    }
    return $result;
}

function genPrettyDiffAst($firstFileArray, $secondFileArray)
{
    $unionArray = unionKey($firstFileArray, $secondFileArray);
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

function genPlainDiffAst($firstFileArray, $secondFileArray)
{
    $unionArray = unionKey($firstFileArray, $secondFileArray);
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
                $item[] = "Property '$path{$value['key']}' was added with value: '{$value['children']}' "
                    . PHP_EOL;
            }
        } elseif ($value['diff'] == "=") {
            if (is_array($value['children'])) {
                $pathActual = $path . "{$value['key']}.";
                $item[] = genPlainStringIsAst($value['children'], $pathActual);
            } else {
                $item[] = "";
            }
        } elseif ($value['diff'] == "-+") {
            $item[] = "Property '$path{$value['key']}' was changed. From '{$value['children']}' to "
                . "'{$value['children2']}' " . PHP_EOL;
        }
        return $item;
    };
    $arrResult = array_reduce($astTree, $funcArrayReduce);
    $strResult = implode('', $arrResult);
    return $strResult;
}



function genJsonDiffArray($firstFileArray, $secondFileArray)
{
    $unionArray = unionKey($firstFileArray, $secondFileArray);
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













function genDiffArraysFirstLevel($firstFileArray, $secondFileArray)
{
    $unionArray = Collection\union($firstFileArray, $secondFileArray);
    $unionArrrayKey = array_keys($unionArray);
    $initial[] = "{" . PHP_EOL;
    $arrResult = array_reduce($unionArrrayKey, function ($item, $key) use ($firstFileArray, $secondFileArray) {
        if (array_key_exists($key, $firstFileArray)) {
            if (array_key_exists($key, $secondFileArray)) {
                if ($firstFileArray[$key] == $secondFileArray[$key]) {
                    $item[] = "  $key: " . stringify($firstFileArray[$key]) . PHP_EOL;
                } else {
                    $item[] = "- $key: " . stringify($firstFileArray[$key])  . PHP_EOL;
                    $item[] = "+ $key: " . stringify($secondFileArray[$key]) . PHP_EOL;
                }
            } else {
                $item[] = "- $key: " . stringify($firstFileArray[$key]) . PHP_EOL;
            }
        } else {
            $item[] = "+ $key: " . stringify($secondFileArray[$key]) . PHP_EOL;
        }
        return $item;
    }, $initial);
    $arrResult[] = "}" . PHP_EOL;
    $resultString = implode('', $arrResult);
    return $resultString;
}

function genDiffArraysMapFirstLevel($firstFileArray, $secondFileArray)
{
    $unionArray = Collection\union($firstFileArray, $secondFileArray);
    $unionArrrayKey = array_keys($unionArray);

    $arrResult[] = "{" . PHP_EOL;
    $arrResult[] = array_map(function ($key) use ($firstFileArray, $secondFileArray) {
        if (array_key_exists($key, $firstFileArray)) {
            if (array_key_exists($key, $secondFileArray)) {
                if ($firstFileArray[$key] == $secondFileArray[$key]) {
                    $iter = "  $key: " . stringify($firstFileArray[$key]) . PHP_EOL;
                } else {
                    $iter = "- $key: " . stringify($firstFileArray[$key]) . PHP_EOL
                          . "+ $key: " . stringify($secondFileArray[$key]) . PHP_EOL;
                }
            } else {
                $iter = "- $key: " . stringify($firstFileArray[$key]) . PHP_EOL;
            }
        } else {
            $iter = "+ $key: " . stringify($secondFileArray[$key]) . PHP_EOL;
        }
        return $iter;
    }, $unionArrrayKey);
    $arrResult[] = "}" . PHP_EOL;
    $arrResultFlatten = Collection\flatten($arrResult, 2);
    $resultString = implode('', $arrResultFlatten);
    return $resultString;
}
function genDiffArraysForeachFirstLevel($firstFileArray, $secondFileArray)
{
    $unionArray = Collection\union($firstFileArray, $secondFileArray);
    $unionArrrayKey = array_keys($unionArray);
    $arrResult[] = "{" . PHP_EOL;
    foreach ($unionArrrayKey as $key) {
        if (array_key_exists($key, $firstFileArray)) {
            if (array_key_exists($key, $secondFileArray)) {
                if ($firstFileArray[$key] == $secondFileArray[$key]) {
                    $arrResult[] = "  $key: " . stringify($firstFileArray[$key]) . PHP_EOL;
                } else {
                    $arrResult[] = "- $key: " . stringify($firstFileArray[$key]) . PHP_EOL;
                    $arrResult[] = "+ $key: " . stringify($secondFileArray[$key]) . PHP_EOL;
                }
            } else {
                $arrResult[] = "- $key: " . stringify($firstFileArray[$key]) . PHP_EOL;
            }
        } else {
            $arrResult[] = "+ $key: " . stringify($secondFileArray[$key]) . PHP_EOL;
        }
    }
    $arrResult[] = "}" . PHP_EOL;
    $resultString = implode('', $arrResult);
    return $resultString;
}
