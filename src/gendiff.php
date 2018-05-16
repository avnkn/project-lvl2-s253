<?php
namespace Differ;

use Differ\Parsers;
use \Exception;
use Funct\Collection;
use function Differ\Render\render;

function genDiff($pathToFile1, $pathToFile2, $format = "pretty")
{
    try {
        $array1 = getArray($pathToFile1);
        $array2 = getArray($pathToFile2);
        $astTree = \Differ\AST\genAST($array1, $array2);
        $resultStr = render($astTree, $format);
    } catch (Exception $e) {
        fwrite(STDERR, $e->getMessage());
        return null;
    }
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
