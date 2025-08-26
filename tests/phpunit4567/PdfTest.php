<?php
namespace tests\phpunit4567;

class PdfTest extends \tests\PdfTest
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
