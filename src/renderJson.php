<?php
namespace Differ\RenderJson;

use function Differ\stringify;
use function Funct\Collection\flattenAll;
use function Funct\Collection\flatten;

function render($astTree)
{
    $jsonString = toJson($astTree);
    return $jsonString;
}

function toJson($array)
{
    return json_encode($array);
}
