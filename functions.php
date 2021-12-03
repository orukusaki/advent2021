<?php
function pipe($value) {
    return match (is_callable($value)) {
        true => fn(callable $fn = null) => match ($fn) {
            null => $value(),
            default => pipe(fn() => $fn($value())),
        },
        false => pipe(fn() => $value),
    };
}

$fold = function(callable $f, mixed $carry = null, iterable $collection = null) {
    $inner = function(iterable $collection) use ($f, $carry) {
        foreach ($collection as $item) {
            $carry = $f($carry, $item);
        };
        return $carry;
    };
    return match ($collection) {
        null => $inner,
        default => $inner($collection),
    };
};

$head = function (iterable $collection) {
    foreach ($collection as $item) {
        return $item;
    }
};

$tail = function (iterable $collection): iterable {
    $head = false;
    foreach ($collection as $item) {
        if ($head) {
            yield $item;
        }
        $head = true;
    }
};

$reduce = fn(iterable $collection, callable $f) => $fold($f, $head($collection), $tail($collection));

$sum = fn(iterable $collection) => $reduce(
    $collection,
    fn ($carry, $item) => $item + $carry
);

$count = fn(iterable $collection) => $fold(
    fn($carry, $item) => $carry + 1,
    0,
    $collection,
);

$map = function(callable $f, iterable $collection = null)  {
    $inner = function(iterable $collection) use ($f): iterable {
        foreach ($collection as $item) {
            yield $f($item);
        }
    };
    return match ($collection) {
        null => $inner,
        default => $inner($collection),
    };
};

$filter = function(callable $predicate = null, iterable $collection = null) {
    $predicate = match ($predicate) {
        null => fn ($i) => $i !== null,
        default => $predicate,
    };

    $inner = function(iterable $collection) use ($predicate) {
        foreach ($collection as $item) {
            match ($predicate($item)) {
                true => yield $item,
                false => null,
            };
        }
    };
    return match ($collection) {
        null => $inner,
        default => $inner($collection),
    };
};

$window = function (int $size, iterable $collection = null) {

    $inner = function (iterable $collection) use ($size) {
        $buf = array_fill(0, $size, 0);

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
    return match ($collection) {
        null => $inner,
        default => $inner($collection),
    };
};

