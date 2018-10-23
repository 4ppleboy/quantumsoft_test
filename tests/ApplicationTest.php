<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testEnvVars()
    {
        $this->assertInternalType('string', getenv('APP_NAME'));
        $this->assertInternalType('string', getenv('APP_DEBUG'));
        $this->assertInternalType('string', getenv('APP_URL'));
    }

    public function testAppTZ()
    {
        $this->assertEquals('UTC', date_default_timezone_get());
    }
}