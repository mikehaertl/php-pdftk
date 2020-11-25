<?php
namespace tests\phpunit4567;

class PdfTest extends \tests\PdfTest
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
