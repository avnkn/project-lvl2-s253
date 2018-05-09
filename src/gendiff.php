<?php
namespace Differ;

use function Funct\true;
use Symfony\Component\Yaml\Yaml;

function genDiff($format, $firstFileName, $secondFileName)
{
    if (file_exists($firstFileName)) {
        $firstFileString = file_get_contents($firstFileName);
    } else {
        echo "Файла $firstFileName не существует" . PHP_EOL;
        exit;
    }
    if (file_exists($secondFileName)) {
        $secondFileString = file_get_contents($secondFileName);
    } else {
        echo "Файла $secondFileName не существует" . PHP_EOL;
        exit;
    }
    if ($format == "yaml") {
        return genDiffYaml($firstFileString, $secondFileString);
    } elseif ($format == "ini") {
        return null;
    } else {
        return genDiffJson($firstFileString, $secondFileString);
    }
}

function genDiffArray($firstFileArray, $secondFileArray)
{
    try {
        $result =[];
        foreach ($firstFileArray as $key => $value) {
            if (array_key_exists($key, $secondFileArray)) {
                if ($value === $secondFileArray[$key]) {
                    $result[] = "  $key: " . transformBooleanString($value) . PHP_EOL;
                } else {
                    $result[] = "- $key: " . transformBooleanString($value). PHP_EOL;
                    $result[] = "+ $key: " . transformBooleanString($secondFileArray[$key]) . PHP_EOL;
                }
            } else {
                $result[] = "- $key: " . transformBooleanString($value) . PHP_EOL;
            }
        }
        $diffArray = array_diff_key($secondFileArray, $firstFileArray);
        foreach ($diffArray as $key => $value) {
            $result[] = "+ $key: " . transformBooleanString($value) . PHP_EOL;
        }
    } catch (Exception $e) {
        echo 'Ошибка: ',  $e->getMessage(), PHP_EOL;
    }
    $resultString = implode('', $result);
    return $resultString;
}
function transformBooleanString($arg)
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

function genDiffJson($firstFileString, $secondFileString)
{
        $firstFileArray = json_decode($firstFileString, true);
        $secondFileArray = json_decode($secondFileString, true);
        return genDiffArray($firstFileArray, $secondFileArray);
}

function yamlParse($stringYaml)
{
    return Yaml::parse($stringYaml, Yaml::PARSE_OBJECT);
}

function genDiffYaml($firstFileString, $secondFileString)
{
    $firstFileArray = yamlParse($firstFileString);
    $secondFileArray = yamlParse($secondFileString);
    return genDiffArray($firstFileArray, $secondFileArray);
}
