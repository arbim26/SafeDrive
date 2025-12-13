<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    // Helper untuk debug response
    protected function debugResponse($response)
    {
        if ($response->status() !== 200 && $response->status() !== 201) {
            dump('Status Code: ' . $response->status());
            dump('Response Headers:', $response->headers->all());
            dump('Response Content:', $response->getContent());
        }
    }
}