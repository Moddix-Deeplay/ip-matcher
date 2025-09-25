<?php

namespace Moddix\IpMatcher;

use IPLib\Factory;

class IpMatcher
{
    private array $subnets = [];
    private function binarySearch($array, $target): int
    {
        $left = 0;
        $right = count($array) - 1;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);

            if ($array[$mid][0] == $target) {
                return $mid + 1; // Элемент найден. Нужно добавить +1, чтобы index был справа
            }

            if ($array[$mid][0] < $target) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        // Если не найден, возвращаем позицию для вставки
        return $left;
    }

    public function contains($ip, $list): bool
    {
        $ip = ip2long($ip);

        $pos = $this->binarySearch($list, $ip) - 1;

        if ($pos >= 0) {
            list($first, $last) = $list[$pos];

            if (($first <= $ip) && ($ip <= $last)) {
                return true;
            }
        }

        return false;
    }

    public function addSubnet($subnet): bool
    {
        $subnet = Factory::parseRangeString($subnet);
        if (is_null($subnet)) {
            return false;
        }

        $first = ip2long($subnet->getStartAddress()->toString());
        $last = ip2long($subnet->getEndAddress()->toString());

        $this->subnets[] = [$first, $last, $subnet->toString()];

        return true;
    }

    public function getSubnets(): array
    {
        return $this->subnets;
    }

    public function prepare(): void
    {
        // Сначала сортируем массив по первому элементу
        usort($this->subnets, function ($a, $b) {
            return $a[0] <=> $b[0];
        });

        foreach ($this->subnets as $key => &$subnet) {
            // Находим подсети, одиночные IP пропускаем
            if ($subnet[1] - $subnet[0] > 0) {
                // Находим в списке индекс IP адреса который перекрывается подсетью
                // TODO: нужно проверить поведение, если подсети с перекрытием
                $pos = $this->binarySearch($this->subnets, $subnet[1]) - 1;
                $len = $pos - $key;

                // Удаляем одиночные IP адреса из списка
                if ($len > 0) {
                    array_splice($this->subnets, $key + 1, $len);
                }
            }
        }
        unset($subnet);
    }
}
