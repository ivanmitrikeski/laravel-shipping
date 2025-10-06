<?php
namespace Mitrik\Shipping\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }
}
