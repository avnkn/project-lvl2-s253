<?php
namespace Differ\RenderPretty;

use function Differ\stringify;

function renderPretty($astTree, $indent = "", $status = 1)
{
    $iterInserted = function ($value, $indent, $status) {
        $prefix = $status ? "  + " : "    ";
        if ($value['children']) {
            $arr[] = $indent . $prefix . $value['key'] . ": ";
            $arr[] = renderPretty($value['children'], "    $indent", 0);
            $str = implode('', $arr);
        } else {
            $str = $indent . $prefix . $value['key'] . ": " . stringify($value['newValue']) . PHP_EOL;
        }
        return $str;
    };
    $iterDeleted = function ($value, $indent, $status) {
        $prefix = $status ? "  - " : "    ";
        if ($value['children']) {
            $arr[] = $indent . $prefix . $value['key'] . ": ";
            $arr[] = renderPretty($value['children'], "    $indent", 0);
            $str = implode('', $arr);
        } else {
            $str = $indent . $prefix . $value['key'] . ": " . stringify($value['oldValue']) . PHP_EOL;
        }
        return $str;
    };
    $iterNotChanged = function ($value, $indent, $status) {
        $prefix = "    ";
        if ($value['children']) {
            $arr[] = $indent . $prefix . $value['key'] . ": ";
            $arr[] = renderPretty($value['children'], "    $indent");
            $str = implode('', $arr);
        } else {
            $str = $indent . $prefix . $value['key'] . ": " . stringify($value['newValue']) . PHP_EOL;
        }
        return $str;
    };
    $iterChanged = function ($value, $indent, $status) {
        if ($value['children']) {
            throw new Exception("Error in the generate AST tree" . PHP_EOL);
        } else {
            $str = $indent . "  - " . $value['key'] . ": " . stringify($value['oldValue']) . PHP_EOL .
                   $indent . "  + " . $value['key'] . ": " . stringify($value['newValue']) . PHP_EOL;
        }
        return $str;
    };

    $iters = [
        'inserted'      => $iterInserted,
        'deleted'       => $iterDeleted,
        'not changed'   => $iterNotChanged,
        'changed'       => $iterChanged
    ];

    $funcArrayReduce = function ($item, $value) use ($indent, $iters, $status) {
        $item[] = $iters[$value['type']]($value, $indent, $status);
        return $item;
    };
    $initial[] = "{" . PHP_EOL;
    $arrResult = array_reduce($astTree, $funcArrayReduce, $initial);
    $arrResult[] = $indent . "}" . PHP_EOL;

    $strResult = implode('', $arrResult);
    return $strResult;
}
