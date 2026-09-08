<?php

declare(strict_types=1);

test('web errors respect the requested response format', function (string $accept, string $contentType): void {
    $this->get('/missing-page', ['Accept' => $accept])
        ->assertNotFound()
        ->assertHeader('Content-Type', $contentType);
})->with([
    'browser requests receive HTML' => ['text/html', 'text/html; charset=utf-8'],
    'JSON clients receive JSON' => ['application/json', 'application/json'],
]);

test('API errors return JSON even when the client requests HTML', function (): void {
    $this->get('/api/missing-resource', ['Accept' => 'text/html'])
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/json');
});
