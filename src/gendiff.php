<?php
namespace Differ;

use function Funct\true;

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
        return null;
    } elseif ($format == "ini") {
        return null;
    } else {
        return genDiffJson($firstFileString, $secondFileString);
    }
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
    $result =[];
    try {
        $firstFileArray = json_decode($firstFileString, true);
        $secondFileArray = json_decode($secondFileString, true);

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
