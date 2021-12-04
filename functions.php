<?php
function pipe($value): callable
{
    return match (is_callable($value)) {
        true => fn(callable $fn = null) => match ($fn) {
            null => $value(),
            default => pipe(fn() => $fn($value())),
        },
        false => pipe(fn() => $value),
    };
}

$fold = fn(callable $f, mixed $carry = null): callable => function (iterable $collection) use ($f, $carry) {
    foreach ($collection as $item) {
        $carry = $f($carry, $item);
    };
    return $carry;
};

$head = function (iterable $collection) {
    foreach ($collection as $item) {
        return $item;
    }
};

$tail = function (iterable $collection): iterable {
    foreach ($collection as $i => $item) {
        match ($i) {
            0 => null,
            default => yield $item,
        };
    }
};

$reduce = fn(callable $f) => fn(iterable $collection) => $fold($f, $head($collection))($tail($collection));

$sum = fn(iterable $collection) => $reduce(fn($carry, $item) => $item + $carry)($collection);

$multiply = fn(iterable $collection) => $reduce(fn($carry, $item) => $item * $carry)($collection);

$count = fn(iterable $collection): int => $fold(fn($carry, $item): int => $carry + 1, 0)($collection);

$take = fn(int $size) => function (iterable $collection) use ($size) {
    foreach ($collection as $i => $item) {
        match ($i < $size) {
            false => null,
            true => yield $item,
        };
    }
};

$map = fn(callable $f): callable => function (iterable $collection) use ($f): iterable {
    foreach ($collection as $item) {
        yield $f($item);
    }
};

$flatMap = fn(callable $f): callable => function (iterable $collection) use ($f): iterable {
    foreach ($collection as $item) {
        yield from $f($item);
    }
};

$flatten = function (iterable $collection): iterable {
    foreach ($collection as $item) {
        yield from $item;
    }
};

$filter = function (callable $predicate = null): callable {
    $predicate = match ($predicate) {
        null => fn($i) => $i !== null,
        default => $predicate,
    };

    return function (iterable $collection) use ($predicate): iterable {
        foreach ($collection as $item) {
            match ($predicate($item)) {
                true => yield $item,
                false => null,
            };
        }
    };
};

$slice = fn(int $start, int $size) => function (iterable $collection) use ($start, $size) {
    foreach ($collection as $i => $item) {
        match (($i >= $start) && ($i < ($start + $size))) {
            true => yield $item,
            false => null,
        };
    }
};

$window = fn(int $size): callable => function (iterable $collection) use ($size): iterable {
    $buf = array_fill(0, $size, null);

    foreach ($collection as $i => $item) {

        for ($j = 0; $j < $size - 1; $j++) {
            $buf[$j] = $buf[$j + 1];
        }

        $buf[$j] = $item;

        match ($i < $size - 1) {
            true, => null,
            false => yield $buf,
        };
    }
};

$collect = function (iterable $collection): array {
    $array = [];
    foreach ($collection as $item) {
        $array[] = $item;
    }

    return $array;
};

$repeat = function (callable $f, int $n) use (&$repeat): callable {
    return fn ($input) => match ($n) {
        0 => $input,
        default => $f($n, $repeat($f, $n - 1)($input)),
    };
};

$pivot = function (iterable $collection) use ($collect): iterable {
    $data = $collect($collection);
    for ($i = 0; $i < count($data[0]); $i++) {
        yield array_column($data, $i);
    }
};

$peak = fn (callable $f): callable => function ($x) use ($f) {$f($x); return $x;};
