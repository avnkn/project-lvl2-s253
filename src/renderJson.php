<?php
namespace Differ\RenderJson;

use function Differ\stringify;
use function Funct\Collection\flattenAll;
use function Funct\Collection\flatten;

function renderJson($astTree)
{
    $flatArray = renderArray($astTree);
    $array = restructArray($flatArray);
    $jsonString = toJson($array);
    return $jsonString;
}
function restructArray($flatArray)
{
    $arrayNumeric = array_chunk($flatArray, 3);
    $funcArrayReduce = function ($item, $value) {
        $item[$value[0]] = [$value[1], $value[2]];
        return $item;
    };
    $arrResult = array_reduce($arrayNumeric, $funcArrayReduce);
    return $arrResult;
}
function toJson($array)
{
    return json_encode($array);
}

function renderArray($astTree, $path = "")
{
    $iterInserted = function ($value, $path) {
        if ($value['children']) {
            $arr[] = $path . $value['key'];
            $arr[] = null;
            $arr[] = "complex add";
            $arr[] = renderArray($value['children'], "$path{$value['key']}.");
        } else {
            $arr[] = $path . $value['key'];
            $arr[] = null;
            $arr[] = $value['newValue'];
        }
        return flatten($arr, 2);
    };
    $iterDeleted = function ($value, $path) {
        if ($value['children']) {
            $arr[] = $path . $value['key'];
            $arr[] = "complex del";
            $arr[] = null;
            $arr[] = renderArray($value['children'], "$path{$value['key']}.");
        } else {
            $arr[] = $path . $value['key'];
            $arr[] = $value['oldValue'];
            $arr[] = null;
        }
        return flatten($arr);
    };
    $iterNotChanged = function ($value, $path) {
        if ($value['children']) {
            $arr[] = $path . $value['key'];
            $arr[] = "complex";
            $arr[] = "complex";
            $arr[] = renderArray($value['children'], "$path{$value['key']}.");
        } else {
            $arr[] = $path . $value['key'];
            $arr[] = $value['oldValue'];
            $arr[] = $value['newValue'];
        }
        return flatten($arr);
    };
    $iterChanged = function ($value, $path) {
        if ($value['children']) {
        } else {
            $arr[] = $path . $value['key'];
            $arr[] = $value['oldValue'];
            $arr[] = $value['newValue'];
        }
        return flatten($arr);
    };

    $iters = [
        'inserted'      => $iterInserted,
        'deleted'       => $iterDeleted,
        'not changed'   => $iterNotChanged,
        'changed'       => $iterChanged
    ];
    $funcArrayReduce = function ($item, $value) use ($iters, $path) {
        $item[] = $iters[$value['type']]($value, $path);
        return flatten($item);
    };
    $arrResult = array_reduce($astTree, $funcArrayReduce);

    return $arrResult;
}
