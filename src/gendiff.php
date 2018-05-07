<?php
namespace Gendiff\Src\Gendiff;

function handler()
{
    $doc = <<<DOC
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help                     Show this screen
  --format <fmt>                Report format [default: pretty]
DOC;
    $args = \Docopt::handle($doc);
}
