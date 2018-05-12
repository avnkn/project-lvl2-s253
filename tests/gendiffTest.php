<?php
declare(strict_types=1);

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

class GenDiffTest extends TestCase
{
    public function testGenDiff()
    {
        $res1 = file_get_contents("tests/data/res1.txt");
        $this->assertEquals($res1, \Differ\genDiff('not working', 'tests/data/before.json', 'tests/data/after.json'));
        $this->assertEquals($res1, \Differ\genDiff('not working', 'tests/data/before.yml', 'tests/data/after.yml'));

        //$res2 = file_get_contents("tests/data/res2.txt");
        //$this->assertEquals($res2, \Differ\genDiff('json', 'tests/data/before2.json', 'tests/data/after2.json'));
    }
}