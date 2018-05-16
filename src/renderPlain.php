<?php
namespace Differ\RenderPlain;

function renderPlain($astTree, $path = "")
{
    $iterAdded = function ($value, $path) {
        $str = "Property '$path{$value['key']}' was added with value: '" . stringify($value['newValue'])
            . "'" . PHP_EOL;
        return $str;
    };
    $iterDeleted = function ($value, $path) {
        $str = "Property '$path{$value['key']}' was removed" . PHP_EOL;
        return $str;
    };
    $iterNotChanged = function ($value, $path) {
        return "";
    };
    $iterChanged = function ($value, $path) {
        $str = "Property '$path{$value['key']}' was changed. From '" . stringify($value['oldValue']) . "' to '"
            . stringify($value['newValue']) . "'" . PHP_EOL;
        return $str;
    };
    $iterNested = function ($value, $path) {
        $str = renderPlain($value['children'], "$path{$value['key']}.");
        return $str;
    };
    $iters = [
        'added'         => $iterAdded,
        'deleted'       => $iterDeleted,
        'not changed'   => $iterNotChanged,
        'changed'       => $iterChanged,
        'nested'        => $iterNested
    ];
    $funcArrayReduce = function ($item, $value) use ($iters, $path) {
        $item[] = $iters[$value['type']]($value, $path);
        return $item;
    };

    $arrResult = array_reduce($astTree, $funcArrayReduce);
    $strResult = implode('', $arrResult);
    return $strResult;
}

function stringify($arg)
{
    if (is_bool($arg)) {
        if ($arg == true) {
            $result = 'true';
        } else {
            $result = 'false';
        }
    } elseif (is_array($arg)) {
        $result = 'complex value';
    } else {
        $result = $arg;
    }
    return $result;
}
