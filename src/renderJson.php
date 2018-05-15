<?php
namespace Differ\RenderJson;

use function Differ\stringify;

function renderJson($astTree)
{
    $array = renderArray($astTree);
    $jsonString = toJson($array);
    return $jsonString;
}

function toJson($array)
{
    return json_encode($array);
}

function renderArray($astTree, $path = "")
{
    $iterInserted = function ($value, $path) {
        if ($value['children']) {
            $arr[] = "$path{$value['key']}\n\ncomplex value\n";
            $arr[] = renderArray($value['children'], "$path{$value['key']}.");
        } else {
            $arr[] = "$path{$value['key']}\n\ncomplex value\n";
        }
        $str = implode('', $arr);
        return $str;
    };
    $iterDeleted = function ($value, $path) {
        if ($value['children']) {
            $arr[] = "$path{$value['key']}\ncomplex value\n\n";
            $arr[] = renderArray($value['children'], "$path{$value['key']}.");
        } else {
            $arr[] = "$path{$value['key']}\n{$value['oldValue']}\n\n";
        }
        $str = implode('', $arr);
        return $str;
    };
    $iterNotChanged = function ($value, $path) {
        if ($value['children']) {
            $str = renderArray($value['children'], "$path{$value['key']}.");
        } else {
            $str = "";
        }
        return $str;
    };
    $iterChanged = function ($value, $path) {
        if ($value['children']) {
            throw new Exception("Error in the generate AST tree" . PHP_EOL);
        } else {
            $str = "Property '$path{$value['key']}' was changed. From '" . stringify($value['oldValue']) . "' to '"
                . stringify($value['newValue']) . "'" . PHP_EOL;
        }
        return $str;
    };

    $iters = [
        'inserted'      => "+",
        'deleted'       => "-",
        'not changed'   => "=",
        'changed'       => "±"
    ];
    $funcArrayReduce = function ($item, $value) use ($iters, $path) {
        if ($value['children']) {
            $item[ $iters[$value['type']] . $path . $value['key'] ] =
                renderArray($value['children'], "$path{$value['key']}.");
        } else {
            $item[ $iters[$value['type']] . $path . $value['key'] ] = [$value['oldValue'], $value['newValue']];
        }
        return $item;
    };

    // $funcArrayReduce = function ($item, $value) use ($iters, $path) {
    //     [$k, $v] = $iters[$value['type']]($value, $path);
    //     $item[$k] = $v;
    //     return $item;
    // };
    $arrResult = array_reduce($astTree, $funcArrayReduce);
    return $arrResult;
}
