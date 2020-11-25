<?php
namespace tests\phpunit89;

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
