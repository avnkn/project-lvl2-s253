<?php
namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parsingString($stringFromFile, $extensionFile)
{
    if (!in_array($extensionFile, ["yaml", "yml", "ini", "json"])) {
        throw new Exception("The extension files is not: 'yaml', 'yml', 'ini', 'json'.\n" . PHP_EOL);
    }
    $arr =[
        "yaml" => "Differ\Parsers\yamlParse",
        "yml"  => "Differ\Parsers\yamlParse",
        "ini"  => "null",
        "json" => "Differ\Parsers\jsonParse"
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
