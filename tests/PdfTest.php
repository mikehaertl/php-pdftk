<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use mikehaertl\pdftk\Pdf;
use mikehaertl\pdftk\FdfFile;

class PdfTest extends TestCase
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
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile'", (string) $pdf->getCommand());
    }
    public function testCanPassPdfInstanceToConstructor()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(1, 5));
        $this->assertFalse($pdf->getCommand()->getExecuted());

        $pdf2 = new Pdf($pdf);
        $this->assertTrue($pdf->getCommand()->getExecuted());
        $outFile1 = (string) $pdf->getTmpFile();
        $this->assertFileExists($outFile1);

        $this->assertTrue($pdf2->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf2->getTmpFile();
        $this->assertEquals("pdftk 'A'='$outFile1' 'output' '$tmpFile'", (string) $pdf2->getCommand());
    }
    public function testCanPassDocumentsToConstructor()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf([
            'A' => $document1,
            'B' => $document2,
        ]);
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document1' 'B'='$document2' 'output' '$tmpFile'", (string) $pdf->getCommand());
    }
    public function testCanPassPdfInstancesToConstructor()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf1 = new Pdf($document1);
        $pdf2 = new Pdf($document2);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf1->cat(1, 5));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf2->cat(2, 3));
        $this->assertFalse($pdf1->getCommand()->getExecuted());
        $this->assertFalse($pdf2->getCommand()->getExecuted());

        $pdf = new Pdf([
            'A' => $pdf1,
            'B' => $pdf2,
        ]);
        $this->assertTrue($pdf1->getCommand()->getExecuted());
        $this->assertTrue($pdf2->getCommand()->getExecuted());
        $outFile1 = (string) $pdf1->getTmpFile();
        $outFile2 = (string) $pdf2->getTmpFile();
        $this->assertFileExists($outFile1);
        $this->assertFileExists($outFile2);

        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$outFile1' 'B'='$outFile2' 'output' '$tmpFile'", (string) $pdf->getCommand());
    }
    public function testCanPassDocumentsWithPasswordToConstructor()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf([
            'A' => [$document1, 'complex\'"password'],
            'B' => $document2,
        ]);
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals(
            "pdftk 'A'='$document1' 'B'='$document2' 'input_pw' 'A'='complex'\''\"password' 'output' '$tmpFile'",
            (string) $pdf->getCommand()
        );
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

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals(
            "pdftk 'A'='$document1' 'D'='$document2' 'input_pw' 'A'='complex'\''\"password' 'output' '$tmpFile'",
            (string) $pdf->getCommand()
        );
    }

    public function testCanPerformEmptyOperation()
    {
        $document1 = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf;
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->addFile($document1, null, 'complex\'"password'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals(
            "pdftk 'A'='$document1' 'input_pw' 'A'='complex'\''\"password' 'output' '$tmpFile'",
            (string) $pdf->getCommand()
        );
    }

    public function testCanCatFile()
    {
        $document = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(1, 5));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat([2,3,4]));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat('end', '2', null, 'even'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(3, 5, null, null, 'east'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(4, 8, null, 'even', 'east'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(1, null, null, null, 'south'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals(
            "pdftk 'A'='$document' cat 1-5 2 3 4 end-2even 3-5east 4-8eveneast 1south 'output' '$tmpFile'",
            (string) $pdf->getCommand()
        );
    }

    public function testCanCatFiles()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf([
            'A' => $document1,
            'B' => $document2,
        ]);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(1, 5, 'A'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat([2,3,4], 'A'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat('end', '2', 'B', 'even'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(3, 5, 'A', null, 'east'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(4, 8, 'B', 'even', 'east'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->cat(1, null, 'A', null, 'south'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals(
            "pdftk 'A'='$document1' 'B'='$document2' cat A1-5 2 3 4 Bend-2even A3-5east B4-8eveneast A1south 'output' '$tmpFile'",
            (string) $pdf->getCommand()
        );
    }

    public function testCanShuffleFiles()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf([
            'A' => $document1,
            'B' => $document2,
        ]);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle(1, 5, 'A'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle([2,3,4], 'B'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle('end', '2', 'B', 'even'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle(3, 5, 'A', null, 'east'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle(4, 8, 'B', 'even', 'east'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->shuffle(1, null, 'A', null, 'south'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals(
            "pdftk 'A'='$document1' 'B'='$document2' shuffle A1-5 2 3 4 Bend-2even A3-5east B4-8eveneast A1south 'output' '$tmpFile'",
            (string) $pdf->getCommand()
        );
    }

    public function testCanBurstWithoutPattern()
    {
        $document = $this->getDocument1();
        $dir = __DIR__;
        $filepattern = $dir . '/pg_000%d.pdf';

        $pdf = new Pdf($document);
        $pdf->getCommand()->procCwd = __DIR__;
        $this->assertTrue($pdf->burst());
        for ($x = 1; $x <= 5; $x++) {
            $filename = sprintf($filepattern, $x);
            $this->assertFileExists($filename);
            @unlink($filename);
        }
        @unlink($dir . '/doc_data.txt');
    }

    public function testCanBurstWithFilePattern()
    {
        $document = $this->getDocument1();
        $dir = __DIR__;
        $filepattern = $dir . '/burst_page_%d.pdf';

        chdir($dir);
        $pdf = new Pdf($document);
        $this->assertTrue($pdf->burst($filepattern));
        for ($x = 1; $x <= 5; $x++) {
            $filename = sprintf($filepattern, $x);
            $this->assertFileExists($filename);
            @unlink($filename);
        }
        @unlink($dir . '/doc_data.txt');
    }

    public function testCanCreateFdfFileFromPdf()
    {
        $form = $this->getFilledForm();
        $file = __DIR__ . '/test.fdf';

        $pdf = new Pdf($form);
        $this->assertTrue($pdf->generateFdfFile($file));
        @unlink($file);
    }

    public function testCanFillFormFromData()
    {
        $form = $this->getForm();
        $file = $this->getOutFile();
        $data = [
            'name' => 'Jürgen čárka čČćĆđĐ мирано',
            'email' => 'test@email.com',
            'checkbox 1' => 'Yes',
            'checkbox 2' => 0,
            'radio 1' => 2,
        ];

        $pdf = new Pdf($form);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->fillForm($data));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->needAppearances());
        $this->assertTrue($pdf->saveAs($file));

        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertRegExp(
            "#pdftk 'A'='$form' 'fill_form' '/tmp/[^ ]+\.xfdf' 'output' '$tmpFile' 'drop_xfa' 'need_appearances'#",
            (string) $pdf->getCommand()
        );
    }

    public function testCanFillFormFromFile()
    {
        $form = $this->getForm();
        $fdf = $this->getFdf();
        $file = $this->getOutFile();

        $pdf = new Pdf($form);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->fillForm($fdf));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->needAppearances());
        $this->assertTrue($pdf->saveAs($file));

        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertRegExp(
            "#pdftk 'A'='$form' 'fill_form' '$fdf' 'output' '$tmpFile' 'drop_xfa' 'need_appearances'#",
            (string) $pdf->getCommand()
        );
    }

    public function testCanUpdateInfo()
    {
        $document1 = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document1);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->updateInfo([
            'Creator' => 'php-pdftk'
        ]));
        $this->assertTrue($pdf->saveAs($file));

        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $pdf = new Pdf($file);
        $data = $pdf->getData();
        $this->assertEquals('php-pdftk', $data['Info']['Creator']);
    }

    public function testCanSetBackground()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf($document1);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->background($document2));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document1' 'background' '$document2' 'output' '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanSetMultiBackground()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf($document1);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->multiBackground($document2));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document1' 'multibackground' '$document2' 'output' '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanStamp()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf($document1);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->stamp($document2));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document1' 'stamp' '$document2' 'output' '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanMultiStamp()
    {
        $document1 = $this->getDocument1();
        $document2 = $this->getDocument2();
        $file = $this->getOutFile();

        $pdf = new Pdf($document1);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->multiStamp($document2));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document1' 'multistamp' '$document2' 'output' '$tmpFile'", (string) $pdf->getCommand());
    }

    public function testCanRemovePermissions()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->allow());
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'allow'", (string) $pdf->getCommand());
    }

    public function testCanSetPermissions()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->allow('Assembly CopyContents'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' allow Assembly CopyContents", (string) $pdf->getCommand());
    }

    public function testCanFlatten()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->flatten());
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'flatten'", (string) $pdf->getCommand());
    }

    public function testCanCompress()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->compress());
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'compress'", (string) $pdf->getCommand());
    }

    public function testCanUncompress()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->compress(false));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'uncompress'", (string) $pdf->getCommand());
    }

    public function testCanKeepFirstId()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->keepId());
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'keep_first_id'", (string) $pdf->getCommand());
    }

    public function testCanKeepFinalId()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->keepId('final'));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'keep_final_id'", (string) $pdf->getCommand());
    }

    public function testCanDropXfa()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->dropXfa());
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'drop_xfa'", (string) $pdf->getCommand());
    }

    public function testCanSetPasswords()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->setPassword('"\'**'));
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->setUserPassword('**"\''));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'owner_pw' '\"'\''**' 'user_pw' '**\"'\'''", (string) $pdf->getCommand());
    }

    public function testSet128BitEncryption()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->passwordEncryption());
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'encrypt_128bit'", (string) $pdf->getCommand());
    }

    public function testSet40BitEncryption()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertInstanceOf('mikehaertl\pdftk\Pdf', $pdf->passwordEncryption(40));
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertEquals("pdftk 'A'='$document' 'output' '$tmpFile' 'encrypt_40bit'", (string) $pdf->getCommand());
    }


    public function testCanGetData()
    {
        $document = $this->getDocument1();
        $pdf = new Pdf($document);
        $data = $pdf->getData();
        $this->assertInstanceOf('\mikehaertl\pdftk\InfoFields', $data);
        $this->assertEquals($this->formDataArray, (array)$data);
    }

    public function testCanGetDataFields()
    {
        $form = $this->getForm();

        $pdf = new Pdf($form);
        $data = $pdf->getDataFields();
        $this->assertInstanceOf('\mikehaertl\pdftk\DataFields', $data);
        $this->assertInternalType('string', $data->__toString());
        $this->assertEquals($this->formDataFields, $data->__toString());
        $this->assertInternalType('array', $data->__toArray());
        $this->assertEquals($this->formDataFieldsArray, $data->__toArray());
    }

    public function testCanGetPdfContentAsString()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();

        $pdf = new Pdf($document);
        $this->assertTrue($pdf->saveAs($file));
        $this->assertFileExists($file);
        $this->assertEquals(file_get_contents($file), $pdf->toString());
    }

    public function testCanSetTemporaryDirectory()
    {
        $document = $this->getDocument1();
        $file = $this->getOutFile();
        $dir = dirname($file);

        $pdf = new Pdf($document);
        $pdf->tempDir = $dir;
        $this->assertTrue($pdf->saveAs($file));

        $tmpFile = (string) $pdf->getTmpFile();
        $this->assertStringStartsWith($dir, $tmpFile);
    }

    protected function getDocument1()
    {
        return __DIR__ . '/files/document1.pdf';
    }

    protected function getDocument2()
    {
        return __DIR__ . '/files/document2.pdf';
    }

    protected function getForm()
    {
        // Empty form
        return __DIR__ . '/files/form.pdf';
    }

    protected function getFilledForm()
    {
        // Form filled with data from testCanFillFormFromData()
        return __DIR__ . '/files/filledform.pdf';
    }

    protected function getFdf()
    {
        // Data from testCanFillFormFromData()
        return __DIR__ . '/files/data.fdf';
    }

    protected function getOutFile()
    {
        // tmp out file
        return __DIR__ . '/test.pdf';
    }

    protected $formData = <<<EOD
InfoBegin
InfoKey: CreationDate
InfoValue: D:20140709121536+02'00'
InfoBegin
InfoKey: Creator
InfoValue: Writer
InfoBegin
InfoKey: Producer
InfoValue: LibreOffice 4.2
PdfID0: 8b93f76a0b28b720d0dee9a6eb2a780a
PdfID1: 8b93f76a0b28b720d0dee9a6eb2a780a
NumberOfPages: 5
PageMediaBegin
PageMediaNumber: 1
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
PageMediaBegin
PageMediaNumber: 2
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
PageMediaBegin
PageMediaNumber: 3
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
PageMediaBegin
PageMediaNumber: 4
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
PageMediaBegin
PageMediaNumber: 5
PageMediaRotation: 0
PageMediaRect: 0 0 595 842
PageMediaDimensions: 595 842
EOD;

    protected $formDataArray = array(
        "Info" => array(
            "CreationDate" => "D:20140709121536+02'00'",
            "Creator" => "Writer",
            "Producer" => "LibreOffice 4.2"
        ),
        "PdfID0" => "8b93f76a0b28b720d0dee9a6eb2a780a",
        "PdfID1" => "8b93f76a0b28b720d0dee9a6eb2a780a",
        "NumberOfPages" => "5",
        "PageMedia" => array(
            array(
                "Number" => "1",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
            array(
                "Number" => "2",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
            array(
                "Number" => "3",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
            array(
                "Number" => "4",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
            array(
                "Number" => "5",
                "Rotation" => "0",
                "Rect" => "0 0 595 842",
                "Dimensions" => "595 842"
            ),
        )

    );

    protected $formDataFields = <<<EOD
---
FieldType: Button
FieldName: checkbox 1
FieldFlags: 0
FieldValue: On
FieldJustification: Left
FieldStateOption: Off
FieldStateOption: On
---
FieldType: Button
FieldName: checkbox 2
FieldFlags: 0
FieldValue: On
FieldJustification: Left
FieldStateOption: Off
FieldStateOption: On
---
FieldType: Button
FieldName: radio 1
FieldFlags: 49152
FieldValue: 2
FieldJustification: Left
FieldStateOption: 1
FieldStateOption: 2
FieldStateOption: Off
---
FieldType: Text
FieldName: email
FieldFlags: 0
FieldJustification: Left
---
FieldType: Text
FieldName: name
FieldFlags: 0
FieldJustification: Left
EOD;

    protected $formDataFieldsArray = [
        [
            'FieldType' => 'Button',
            'FieldName' => 'checkbox 1',
            'FieldFlags' => '0',
            'FieldValue' => 'On',
            'FieldJustification' => 'Left',
            'FieldStateOption' => ['Off', 'On'],
        ],
        [
            'FieldType' => 'Button',
            'FieldName' => 'checkbox 2',
            'FieldFlags' => '0',
            'FieldValue' => 'On',
            'FieldJustification' => 'Left',
            'FieldStateOption' => ['Off', 'On'],
        ],
        [
            'FieldType' => 'Button',
            'FieldName' => 'radio 1',
            'FieldFlags' => '49152',
            'FieldValue' => '2',
            'FieldJustification' => 'Left',
            'FieldStateOption' => ['1', '2', 'Off'],
        ],
        [
            'FieldType' => 'Text',
            'FieldName' => 'email',
            'FieldFlags' => '0',
            'FieldJustification' => 'Left',
        ],
        [
            'FieldType' => 'Text',
            'FieldName' => 'name',
            'FieldFlags' => '0',
            'FieldJustification' => 'Left',
        ],
    ];
}
