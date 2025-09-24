<?php

use Moddix\IpMatcher\IpMatcher;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversMethod(IpMatcher::class, 'contains')]
class IpMatcherTest extends TestCase
{
    public static function dataProvider(): array
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../data.json'), true);

        $rand_keys = array_rand($data, 3);

        $rand_values = [];

        foreach ($rand_keys as $key) {
            $ip = \IPLib\Factory::parseRangeString($data[$key][2]);

            if ($ip->getSize() > 1) {
                $rand = rand(0, $ip->getSize());
                $rand_values[] = [$ip->getAddressAtOffset($rand)->toString(), true];
            } else {
                $rand_values[] = [$ip->toString(), true];
            }
        }

//        $rand_values = [
//            ['2.89.56.215', false], 84.16.256.256
//            ['2.89.56.216', true],
//            ['2.89.56.217', false],
//
//            ['223.255.225.13', true],
//            ['223.255.225.14', true],
//            ['223.255.225.15', false],
//
//            // 64.233.0.0/16
//            ['64.232.255.254', false],
//            ['64.233.0.1', true],
//            ['64.233.255.254', true],
//
//            ['38.203.217.224', true],
//
//        ];

        return $rand_values;
    }

    #[DataProvider('dataProvider')]
    public function testContains($input, $expected)
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../data.json'), true);
        $this->assertEquals($expected, (new IpMatcher())->contains($input, $data));
    }
}
