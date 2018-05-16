<?php
namespace Differ\Render;

function render($astTree, $format)
{

    if (!in_array($format, ["pretty", "plain", "json"])) {
        throw new Exception("The format output is not: 'pretty', 'plain', 'json'.\n" . PHP_EOL);
    }
    $parsers =[
        "pretty" => 'Differ\RenderPretty\render',
        "plain"  => 'Differ\RenderPlain\render',
        "json"   => 'Differ\RenderJson\render'
    ];
    $resultString = $parsers[$format]($astTree);
    return $resultString;
}
