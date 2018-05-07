<?php
namespace Src\Gendiff;

use function Funct\true;

class genDiff {

    function __construct(){
      $doc = <<<DOC
      Generate diff

      Usage:
      gendiff (-h|--help)
      gendiff [--format <fmt>] <firstFile> <secondFile>

      Options:
      -h --help                     Show this screen
      --format <fmt>                Report format [default: pretty]
DOC;
      $args = \Docopt::handle($doc);
      $firstFileName = $args["<firstFile>"];
      $secondFileName = $args["<secondFile>"];
      $format = $args["--format"];
      $this->handler($format, $firstFileName, $secondFileName);
    }

    function handler($format, $firstFileName, $secondFileName) {
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
        if ($format == "yaml"){

        } elseif ($format == "ini") {

        } else {
            $this->handlerJson($firstFileString, $secondFileString);
        }
    }

    function transformBooleanString($arg) {
        if (is_bool($arg)){
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

    function handlerJson($firstFileString, $secondFileString) {
        try{
            $firstFileArray = json_decode($firstFileString, true);
            $secondFileArray = json_decode($secondFileString, true);

            foreach ($firstFileArray as $key => $value) {
                if (array_key_exists($key, $secondFileArray)) {
                    if ($value === $secondFileArray[$key]){
                        echo "  $key: " . $this->transformBooleanString($value) . PHP_EOL;
                    } else {
                        echo "- $key: " . $this->transformBooleanString($value). PHP_EOL;
                        echo "+ $key: " . $this->transformBooleanString($secondFileArray[$key]) . PHP_EOL;
                    }
                } else {
                    echo "- $key: " . $this->transformBooleanString($value) . PHP_EOL;
                }
            }
            $diffArray = array_diff_key($secondFileArray, $firstFileArray);
            foreach ($diffArray as $key => $value){
                echo "+ $key: " . $this->transformBooleanString($value) . PHP_EOL;
            }
        } catch (Exception $e) {
            echo 'Ошибка: ',  $e->getMessage(), PHP_EOL;
        }
    }
}
