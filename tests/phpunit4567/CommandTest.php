<?php
namespace tests\phpunit4567;

class CommandTest extends \tests\CommandTest
{
    public function setUp()
    {
        @unlink($this->getOutFile());
    }
    public function tearDown()
    {
        @unlink($this->getOutFile());
    }
}
