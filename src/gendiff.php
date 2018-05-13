<?php
namespace Differ;

use \Exception;
use Funct\Collection;
use Symfony\Component\Yaml\Yaml;

function genDiff($pathToFile1, $pathToFile2, $format = "json")
{
    try {
        $arrayFromFile1 = getArray($pathToFile1);
        $arrayFromFile2 = getArray($pathToFile2);
    } catch (Exception $e) {
        fwrite(STDERR, $e->getMessage());
        return null;
    }
    return genDiffArrays($arrayFromFile1, $arrayFromFile2);
}

function getArray($pathToFile)
{
    $stringFromFile = getFileAsString($pathToFile);
    $extensionFile = getFileExtension($pathToFile);
    $arrayFromFile = parsingString($stringFromFile, $extensionFile);
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

function parsingString($stringFromFile, $extensionFile)
{
    if (!in_array($extensionFile, ["yaml", "yml", "ini", "json"])) {
        throw new Exception("The extension files is not: 'yaml', 'yml', 'ini', 'json'.\n" . PHP_EOL);
    }
    $arr =[
        "yaml" => "Differ\yamlParse",
        "yml" => "Differ\yamlParse",
        "ini" => "null",
        "json" => "Differ\jsonParse"
    ];
    $nameFunc =  $arr[$extensionFile];
    $arrayFromFile = $nameFunc($stringFromFile);
    return $arrayFromFile;
}


function jsonParse($stringFromFile)
{
    return json_decode($stringFromFile, true);
}

function yamlParse($stringFromFile)
{
    return Yaml::parse($stringFromFile, Yaml::PARSE_OBJECT);
}

function genDiffArrays($firstFileArray, $secondFileArray)
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
function genDiffArraysMap($firstFileArray, $secondFileArray)
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
function genDiffArraysForeach($firstFileArray, $secondFileArray)
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
