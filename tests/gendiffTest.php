<?php
declare(strict_types=1);

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

class GenDiffTest extends TestCase
{
    public function testGenDiff()
    {
        $resPretty1 = file_get_contents("tests/data/resPretty1.txt");
        $this->assertEquals($resPretty1, \Differ\genDiff('tests/data/before.json', 'tests/data/after.json'));
        $this->assertEquals($resPretty1, \Differ\genDiff('tests/data/before.yml', 'tests/data/after.yml'));

        $resPretty2 = file_get_contents("tests/data/resPretty2.txt");
        $this->assertEquals($resPretty2, \Differ\genDiff('tests/data/before2.json', 'tests/data/after2.json'));

        $resPlain1 = file_get_contents("tests/data/resPlain1.txt");
        $this->assertEquals($resPlain1, \Differ\genDiff('tests/data/before2.json', 'tests/data/after2.json', 'plain'));

        $resJson1 = trim(file_get_contents("tests/data/resJson1.txt"));
        $this->assertEquals($resJson1, \Differ\genDiff('tests/data/before2.json', 'tests/data/after2.json', 'json'));
    }
}
