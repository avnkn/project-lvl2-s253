<?php
namespace Differ;

use \Exception;
use function Funct\true;
use Symfony\Component\Yaml\Yaml;

function genDiff($format, $firstFileName, $secondFileName)
{
    $firstFileArray = fileParse($firstFileName);
    $secondFileArray = fileParse($secondFileName);
    return genDiffArrays($firstFileArray, $secondFileArray);
}

function fileParse($filename)
{
    try {
        if (!file_exists($filename)){
          throw new Exception("Error: File '$filename' does not exist" . PHP_EOL);
        }
        $extensionFile = getExtensionFile($filename);

        if ($extensionFile == "yaml" or $extensionFile == "yml") {
            $FileArray = yamlParse($filename);
        } elseif ($extensionFile == "ini") {
            $FileArray = null;
        } elseif ($extensionFile == "json") {
            $FileArray = jsonParse($filename);
        } else{
            throw new Exception("Error: Extension file is not yaml, yml, ini, json" . PHP_EOL);
        }
    }
    catch (Exception $e) {
        fwrite(STDERR, $e->getMessage());
        return null;
    }
    return $FileArray;
}

function getExtensionFile($filename)
{
  $path_info = pathinfo($filename);
  return $path_info['extension'];
}

function jsonParse($filename)
{
    $stringJson = file_get_contents($filename);
    return json_decode($stringJson, true);
}

function yamlParse($filename)
{
    $stringYaml = file_get_contents($filename);
    return Yaml::parse($stringYaml, Yaml::PARSE_OBJECT);
}

function genDiffArrays($firstFileArray, $secondFileArray)
{
    $result =[];
    array_walk($firstFileArray,
        function($value, $key) use (&$result, $secondFileArray) {
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
        }
    );
    $diffArray = array_diff_key($secondFileArray, $firstFileArray);
    array_walk($diffArray,
        function($value, $key) use (&$result, $secondFileArray) {
            $result[] = "+ $key: " . stringify($value) . PHP_EOL;
        }
    );
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
