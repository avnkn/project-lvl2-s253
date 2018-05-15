<?php
namespace Differ;

use Differ\Parsers;
use \Exception;
use Funct\Collection;

function genDiff($pathToFile1, $pathToFile2, $format = "pretty")
{
    try {
        $array1 = getArray($pathToFile1);
        $array2 = getArray($pathToFile2);
    } catch (Exception $e) {
        fwrite(STDERR, $e->getMessage());
        return null;
    }
    $astTree = \Differ\AST\genAST($array1, $array2);
    $resultStr = render($astTree, $format);
    return $resultStr;
}

function getArray($pathToFile)
{

    $string = getFileStr($pathToFile);
    $extension  = getExtension($pathToFile);
    $array  =  Parsers\parsingString($string, $extension);
    return $array;
}
function getFileStr($filename)
{
    if (!file_exists($filename)) {
        throw new Exception("Error: File '$filename' does not exist" . PHP_EOL);
    }
    $stringFromFile = file_get_contents($filename);
    return $stringFromFile;
}
function getExtension($filename)
{
    $path_info = pathinfo($filename);
    return $path_info['extension'];
}

function render($astTree, $format)
{

    if (!in_array($format, ["pretty", "plain", "json"])) {
        throw new Exception("The format output is not: 'pretty', 'plain', 'json'.\n" . PHP_EOL);
    }
    $parsers =[
        "pretty" => 'Differ\RenderPretty\renderPretty',
        "plain"  => 'Differ\RenderPlain\renderPlain',
        "json"   => 'Differ\RenderJson\renderJson'
    ];
    $resultString = $parsers[$format]($astTree);
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
