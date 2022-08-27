<?php

use Xuchunyang\Ydcv\Ydcv;

test('example', function () {
    expect(true)->toBeTrue();
});

test('ydcv query returns an array for hello', function () {
    expect(Ydcv::query('hello'))->toBeArray();
});