<?php

require 'functions.php';

$increased = fn ($x) => $x[0] < $x[1];

echo pipe(file('data/1.txt'))
    ($map('intval'))
    ($window(2))
    ($filter($increased))
    ($count)
    ();

echo PHP_EOL;

echo pipe(file('data/1.txt'))
    ($map('intval'))
    ($window(3))
    ($map($sum))
    ($window(2))
    ($filter($increased))
    ($count)
    ();

echo PHP_EOL;
