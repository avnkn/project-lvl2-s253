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

    if (!in_array($format, ["pretty", "plain", "json"])) {
        throw new Exception("The format output is not: 'pretty', 'plain', 'json'.\n" . PHP_EOL);
    }
    $parsers =[
        "pretty" => 'Differ\RenderPretty\renderPretty',
        "plain"  => 'Differ\RenderPlain\renderPlain',
        "json"   => 'Differ\RenderJson\renderJson'
    ];
    $astTree = \Differ\AST\genAST($arrayFromFile1, $arrayFromFile2);
    $resultString = $parsers[$format]($astTree);
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

function arrayUniqueKey($array1, $array2)
{
    $arrResult1 = array_keys($array1);
    print_r($array1);
    print_r($array2);
    $arrResult2 = array_keys($array2);
    print_r($arrResult1);
    print_r($arrResult2);
    $mergeKey = array_merge($arrResult1, $arrResult2);
    $unionKey = array_unique($mergeKey);
    print_r($unionKey);
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
