<?php

require 'functions.php';

$toNumber = $reduce(fn($carry, $item) => ($carry<<1) + $item);

 echo pipe(file('data/3.txt'))
    ($map('trim'))
    ($map('str_split'))
    ($pivot)                               // Gives us an array per column (bit position)
    ($map($sum))                           // Sum up the bits in each column
    ($map(fn($x) => (int)($x > 500)))      // 1 in every position that is more 1s
    ($toNumber)                            // 'gamma'
    (fn($x) => [$x, (~$x & 0xFFF)])        // Create the inverse (epsilon rate)
    ($multiply)
    ();

echo PHP_EOL;

$filterByPosition = fn(int $pos, int $val) => $filter(fn($x) => $x[$pos] == $val);

$countValuesAtPosition = fn(int $pos) => fn(iterable $collection) => pipe($collection)
    ($pivot)
    ($collect)
    (fn($x) => array_count_values($x[$pos]))
    ($peak('asort'))
    ();

$mostCommonAtPosition = fn(int $pos) => fn(iterable $collection) => pipe($collection)
    ($countValuesAtPosition($pos))
    ('array_key_last')
    ();

$leastCommonAtPosition = fn(int $pos) => fn(iterable $collection) => pipe($collection)
    ($countValuesAtPosition($pos))
    ('array_key_first')
    ();

$filterBy = fn(callable $f) => fn(int $position, iterable $collection) => pipe($collection)
    ($collect)
    (fn($x) => [$f($position - 1)($x), $x])
    (fn($x) => $filterByPosition($position - 1, $x[0])($x[1]))
    ();

$getNumberUsingFilter = fn(callable $f) => fn(iterable $collection): int => pipe($collection)
    ($repeat($filterBy($f), 12))
    ($head)
    ($toNumber)
    ();

$oxygen = $getNumberUsingFilter($mostCommonAtPosition);
$co2 = $getNumberUsingFilter($leastCommonAtPosition);

echo pipe(file('data/3.txt'))
    ($map('trim'))
    ($map('str_split'))
    ($collect)
    ($fork([$oxygen, $co2]))
    ($multiply)
    ();

echo PHP_EOL;
