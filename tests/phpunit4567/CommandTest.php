<?php
namespace tests\phpunit4567;

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
