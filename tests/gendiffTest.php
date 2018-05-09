<?php
declare(strict_types=1);

namespace GendiffTest;

use PHPUnit\Framework\TestCase;

class gendiffTest extends TestCase
{
    public function testgenDiff(){
        $res1 = <<<DOC
  host: hexlet.io
- timeout: 50
+ timeout: 20
- proxy: 123.234.53.22
+ verbose: true

DOC;
        $this->assertEquals($res1, \Differ\genDiff('json', 'tests/data/before.json', 'tests/data/after.json'));
    }
}
