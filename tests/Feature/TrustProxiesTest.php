<?php

it('generates https urls when behind a proxy that terminates tls', function () {
    $this->get('/up', [
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-For' => '203.0.113.10',
    ])->assertOk();

    expect(request()->isSecure())->toBeTrue()
        ->and(url('/'))->toStartWith('https://');
});

it('keeps http urls for direct requests without forwarded headers', function () {
    $this->get('/up')->assertOk();

    expect(request()->isSecure())->toBeFalse();
});
