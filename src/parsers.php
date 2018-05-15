<?php
namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parsingString($stringFromFile, $extensionFile)
{
    if (!in_array($extensionFile, ["yaml", "yml", "json"])) {
        throw new Exception("The extension files is not: 'yaml', 'yml', 'json'.\n" . PHP_EOL);
    }
    $arr =[
        "yaml" => "Differ\Parsers\yamlParse",
        "yml"  => "Differ\Parsers\yamlParse",
        "json" => "Differ\Parsers\jsonParse"
    ];
    $arrayFromFile = $arr[$extensionFile]($stringFromFile);
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
