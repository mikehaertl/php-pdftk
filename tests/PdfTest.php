<?php
use mikehaertl\pdftk\Pdf;

class PdfTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        @unlink($this->getOutFile());
    }
    public function tearDown()
    {
        @unlink($this->getOutFile());
    }


    public function testCanPassDocumentToConstructor()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $pdf->saveAs($file);
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = $pdf->getPdfFilename();
        $this->assertEquals("pdftk Z='$document' output '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanPassDocumentsToConstructor()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf(array(
            'A' => $document1,
            'B' => $document2,
        ));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = $pdf->getPdfFilename();
        $this->assertEquals("pdftk A='$document1' B='$document2' output '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanAddFiles()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf;
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->addFile($document1, null, 'complex\'"password'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->addFile($document2, 'D'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = $pdf->getPdfFilename();
        $this->assertEquals("pdftk Z='$document1' D='$document2' input_pw Z='complex'\''\"password' output '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanCatFile()
    {
        $document = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(1,5));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(array(2,3,4)));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat('end','2',null,'even'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(3,5,null,null,'E'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(4,8,null,'even','E'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(1,null,null,null,'S'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = $pdf->getPdfFilename();
        $this->assertEquals("pdftk Z='$document' cat 1-5 2 3 4 end-2even 3-5E 4-8evenE 1S output '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanCatFiles()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf(array(
            'A' => $document1,
            'B' => $document2,
        ));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(1,5,'A'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(array(2,3,4),'A'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat('end','2','B','even'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(3,5,'A',null,'E'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(4,8,'B','even','E'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(1,null,'A',null,'S'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = $pdf->getPdfFilename();
        $this->assertEquals("pdftk A='$document1' B='$document2' cat A1-5 2 3 4 Bend-2even A3-5E B4-8evenE A1S output '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanShuffleFiles()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf(array(
            'A' => $document1,
            'B' => $document2,
        ));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle(1,5,'A'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle(array(2,3,4),'B'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle('end','2','B','even'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle(3,5,'A',null,'E'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle(4,8,'B','even','E'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle(1,null,'A',null,'S'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = $pdf->getPdfFilename();
        $this->assertEquals("pdftk A='$document1' B='$document2' shuffle A1-5 2 3 4 Bend-2even A3-5E B4-8evenE A1S output '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanFillForm()
    {
        $form = $this->getForm();
        $file = $this->getOutFile();
        $data = array(
            'name' => 'ÄÜÖ äüö мирано čárka',
            'email' => 'test@email.com',
            'checkbox 1' => 'Yes',
            'checkbox 2' => 0,
            'radio 1' => 2,
        );

        $pdf = new Pdf($form);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->fillForm($data));
        $this->assertTrue($pdf->saveAs($file));
$this->assertTrue($pdf->saveAs(__DIR__.'/filled.pdf'));

        $this->assertFileExists($file);

        $tmpFile = $pdf->getPdfFilename();
        $this->assertRegExp("#pdftk Z='$form' fill_form '/tmp/[^ ]+\.fdf' output '$tmpFile'#", (string) $pdf->getCommand());
    }

    protected function getDocument1()
    {
        return __DIR__.'/files/document1.pdf';
    }

    protected function getDocument2()
    {
        return __DIR__.'/files/document2.pdf';
    }

    protected function getForm()
    {
        return __DIR__.'/files/form.pdf';
    }

    protected function getOutFile()
    {
        return __DIR__.'/test.pdf';
    }
}
