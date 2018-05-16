<?php
namespace Differ\RenderPretty;

function render($astTree)
{
    $arr[] = "{" . PHP_EOL;
    $arr[] = renderBody($astTree);
    $arr[] = "}" . PHP_EOL;
    $strResult = implode('', $arr);
    return $strResult;
}

function renderBody($astTree, $level = 1)
{
    $iterAdded = function ($value, $level) {
        $str = getIndent($level-1) . "  + " . $value['key'] . ": " . stringify($value['newValue'], $level) . PHP_EOL;
        return $str;
    };
    $iterDeleted = function ($value, $level) {
        $str = getIndent($level-1) . "  - " . $value['key'] . ": " . stringify($value['oldValue'], $level) . PHP_EOL;
        return $str;
    };
    $iterNotChanged = function ($value, $level) {
        $str = getIndent($level) . $value['key'] . ": " . stringify($value['newValue'], $level) . PHP_EOL;
        return $str;
    };
    $iterChanged = function ($value, $level) {
        $str =  getIndent($level-1) . "  - " . $value['key'] . ": " . stringify($value['oldValue'], $level) . PHP_EOL .
                getIndent($level-1) . "  + " . $value['key'] . ": " . stringify($value['newValue'], $level) . PHP_EOL;
        return $str;
    };
    $iterNested = function ($value, $level) {
        $arr[] = getIndent($level) . $value['key'] . ": {" . PHP_EOL;
        $arr[] = renderBody($value['children'], ($level + 1));
        $arr[] = getIndent($level) . "}" . PHP_EOL;
        $str = implode('', $arr);
        return $str;
    };

    $iters = [
        'added'         => $iterAdded,
        'deleted'       => $iterDeleted,
        'not changed'   => $iterNotChanged,
        'changed'       => $iterChanged,
        'nested'        => $iterNested
    ];
    $funcArrayReduce = function ($item, $value) use ($level, $iters) {
        $item[] = $iters[$value['type']]($value, $level);
        return $item;
    };

    $arrResult = array_reduce($astTree, $funcArrayReduce);
    $strResult = implode('', $arrResult);
    return $strResult;
}

function stringify($arg, $level = 1)
{
    if (is_bool($arg)) {
        if ($arg == true) {
            $result = 'true';
        } else {
            $result = 'false';
        }
    } elseif (is_array($arg)) {
        $str1 = json_encode($arg, JSON_PRETTY_PRINT);
        $str2 = str_replace('"', '', $str1);
        $indent = getIndent($level);
        $result = str_replace("\n", "\n$indent", $str2);
    } else {
        $result = $arg;
    }
    return $result;
}

function getIndent($level)
{
    if ($level > 0) {
        $str = "    " . getIndent($level-1);
    } else {
        $str = "";
    }
    return $str;
}
