<?php

require '../advent2021/functions.php';

$parse = fn ($row) => explode(' ', $row);

$track = fn (array $v, array $row) => match ($row[0]) {
    'forward' => [$v[0] + $row[1], $v[1]],
    'up'      => [$v[0], $v[1] - $row[1]],
    'down'    => [$v[0], $v[1] + $row[1]],
    default   => $v,
};

echo pipe(file('data/2.txt'))
    ($map($parse))
    ($fold($track, [0, 0]))
    ($multiply)
    ();

echo PHP_EOL;

$track2 = fn (array $v, array $row) => match ($row[0]) {
    'forward' => [$v[0] + $row[1], $v[1] + $v[2] * $row[1], $v[2]],
    'up'      => [$v[0], $v[1], $v[2] - $row[1]],
    'down'    => [$v[0], $v[1], $v[2] + $row[1]],
    default   => $v,
};

echo pipe(file('data/2.txt'))
    ($map($parse))
    ($fold($track2, [0, 0, 0]))
    ($take(2))
    ($multiply)
    ();

echo PHP_EOL;
