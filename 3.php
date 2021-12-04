<?php

require 'functions.php';

$toNumber = $reduce(fn($carry, $item) => ($carry<<1) + $item);

 echo pipe(file('data/3.txt'))
    ($map('trim'))
    ($map('str_split'))
    ($pivot)                               // Gives us an array per column (bit position)
    ($map($sum))                           // Sum up the bits in each column
    ($map(fn($x) => (int)($x > 500)))      // 1 in every position that is more 1s
    ($toNumber)
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
    (fn($x) => array_key_last($x))
    ();

$leastCommonAtPosition = fn(int $pos) => fn(iterable $collection) => pipe($collection)
    ($countValuesAtPosition($pos))
    (fn($x) => array_key_first($x))
    ();

$filterBy = fn($f) => fn(int $position, iterable $collection) => pipe($collection)
    ($collect)
    (fn($x) => [$f($position - 1)($x), $x])
    (fn($x) => $filterByPosition($position - 1, $x[0])($x[1]))
();

$oxygen = pipe(file('data/3.txt'))
    ($map('trim'))
    ($map('str_split'))
    ($repeat($filterBy($mostCommonAtPosition), 12))
    ($head)
    ($toNumber)
    ();

$co2 = pipe(file('data/3.txt'))
    ($map('trim'))
    ($map('str_split'))
    ($repeat($filterBy($leastCommonAtPosition), 12))
    ($head)
    ($toNumber)
    ();

echo $oxygen * $co2;

echo PHP_EOL;
