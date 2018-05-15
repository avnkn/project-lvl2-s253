<?php
namespace Differ\RenderPlain;

use function Differ\stringify;

function renderPlain($astTree, $path = "")
{
    $iterInserted = function ($value, $path) {
        if ($value['children']) {
            $arr[]  = "Property '$path{$value['key']}' was added with value: 'complex value'"
                . PHP_EOL;
            $arr[] = renderPlain($value['children'], "$path{$value['key']}.");
        } else {
            $arr[] = "Property '$path{$value['key']}' was added with value: '" . stringify($value['newValue'])
            . "'" . PHP_EOL;
        }
        $str = implode('', $arr);
        return $str;
    };
    $iterDeleted = function ($value, $path) {
        $arr[] = "Property '$path{$value['key']}' was removed" . PHP_EOL;
        if ($value['children']) {
            $arr[] = renderPlain($value['children'], "$path{$value['key']}.");
        }
        $str = implode('', $arr);
        return $str;
    };
    $iterNotChanged = function ($value, $path) {
        if ($value['children']) {
            $str = renderPlain($value['children'], "$path{$value['key']}.");
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
        'inserted'      => $iterInserted,
        'deleted'       => $iterDeleted,
        'not changed'   => $iterNotChanged,
        'changed'       => $iterChanged
    ];

    $funcArrayReduce = function ($item, $value) use ($iters, $path) {
        $item[] = $iters[$value['type']]($value, $path);
        return $item;
    };
    $arrResult = array_reduce($astTree, $funcArrayReduce);
    $strResult = implode('', $arrResult);
    return $strResult;
}
