<?php
namespace Differ;

use \Exception;
use function Funct\Collection\union;
use Symfony\Component\Yaml\Yaml;

function genDiff($format, $pathToFile1, $pathToFile2)
{
    $arrayFromFile1 = getArray($pathToFile1);
    $arrayFromFile2 = getArray($pathToFile2);
    return genDiffArrays($arrayFromFile1, $arrayFromFile2);
}

function getArray($pathToFile)
{
    $stringFromFile = getFileAsString($pathToFile);
    $extensionFile = getExtensionFile($pathToFile);
    $arrayFromFile = parsingString($stringFromFile, $extensionFile);
    return $arrayFromFile;
}

function getFileAsString($filename)
{
    try {
        if (!file_exists($filename)) {
            throw new Exception("Error: File '$filename' does not exist" . PHP_EOL);
        }
    } catch (Exception $e) {
        fwrite(STDERR, $e->getMessage());
        return null;
    }
    $stringFromFile = file_get_contents($filename);
    return $stringFromFile;
}

function getExtensionFile($filename)
{
    $path_info = pathinfo($filename);
    return $path_info['extension'];
}

function parsingString($stringFromFile, $extensionFile)
{
    try {
        if ($extensionFile == "yaml" or $extensionFile == "yml") {
            $arrayFromFile = yamlParse($stringFromFile);
        } elseif ($extensionFile == "ini") {
            $arrayFromFile = null;
        } elseif ($extensionFile == "json") {
            $arrayFromFile = jsonParse($stringFromFile);
        } else {
            throw new Exception("Error: Extension file is not yaml, yml, ini, json" . PHP_EOL);
        }
    } catch (Exception $e) {
        fwrite(STDERR, $e->getMessage());
        return null;
    }
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
    $result =[];
    array_walk($firstFileArray, function ($value, $key) use (&$result, $secondFileArray) {
        if (array_key_exists($key, $secondFileArray)) {
            if ($value === $secondFileArray[$key]) {
                $result[] = "  $key: " . stringify($value) . PHP_EOL;
            } else {
                $result[] = "- $key: " . stringify($value). PHP_EOL;
                $result[] = "+ $key: " . stringify($secondFileArray[$key]) . PHP_EOL;
            }
        } else {
            $result[] = "- $key: " . stringify($value) . PHP_EOL;
        }
    });
    $diffArray = array_diff_key($secondFileArray, $firstFileArray);
    array_walk($diffArray, function ($value, $key) use (&$result, $secondFileArray) {
        $result[] = "+ $key: " . stringify($value) . PHP_EOL;
    });
    $resultString = implode('', $result);
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
