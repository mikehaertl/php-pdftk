<?php
namespace tests\phpunit89;

class CommandTest extends \tests\CommandTest
{
    public function setUp(): void
    {
        @unlink($this->getOutFile());
    }
    public function tearDown(): void
    {
        @unlink($this->getOutFile());
    }
}
