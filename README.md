php-pdftk
=========

[![Build Status](https://secure.travis-ci.org/mikehaertl/php-pdftk.png)](http://travis-ci.org/mikehaertl/php-pdftk)
[![Latest Stable Version](https://poser.pugx.org/mikehaertl/php-pdftk/v/stable.svg)](https://packagist.org/packages/mikehaertl/php-pdftk)
[![Total Downloads](https://poser.pugx.org/mikehaertl/php-pdftk/downloads.svg)](https://packagist.org/packages/mikehaertl/php-pdftk)
[![Latest Unstable Version](https://poser.pugx.org/mikehaertl/php-pdftk/v/unstable.svg)](https://packagist.org/packages/mikehaertl/php-pdftk)
[![HHVM Status](http://hhvm.h4cc.de/badge/yiisoft/yii2-dev.png)](http://hhvm.h4cc.de/package/mikehaertl/php-pdftk)
[![License](https://poser.pugx.org/mikehaertl/php-pdftk/license.svg)](https://packagist.org/packages/mikehaertl/php-pdftk)

A PDF conversion and form utility based on pdftk.

### This is still WIP so not all pdftk features are implemented yet!

## Features

 * Combine pages from several PDF files into a new PDF file
 * Fill forms, either from a FDF file or from a data array (UTF-8 aware!)
 * TBD ...

## Examples

### Operations

You can always only perform one of the following operations on a PDF.

```php
use mikehaertl\pdftk\Pdf;

// Extract pages 1-5 and 7,4,9 into a new file
$pdf = new Pdf('my.pdf');
$pdf->cat(1, 5)
    ->cat(array(7, 4, 9))
    ->saveAs('new.pdf');

// Combine pages from several files
$pdf = new Pdf(array(
    'A' => 'file1.pdf',     // Reference file as 'A'
    'B' => 'file2.pdf',     // Reference file as 'B'
));
$pdf->cat(1, 5, 'A')                // pages 1-5 from A
    ->cat(3, null, 'B')             // page 3 from B
    ->cat(7, 'end', 'B', null, 'E') // pages 7-end from B, rotated East
    ->cat('end',3,'A','even')       // even pages 3-end in reverse order from A
    ->saveAs('new.pdf');

// Split up PDF in single files
$pdf = new Pdf('my.pdf');
$pdf->burst('page_%d.pdf');

// Create FDF from PDF
$pdf = new Pdf('form.pdf');
$pdf->generateFdfFile('data.fdf');

// Fill Form
$pdf = new Pdf('form.pdf');
$pdf->fillForm(array('name'=>'ÄÜÖ äüö мирано čárka'))
    ->saveAs('filled.pdf');

// Fill form from FDF
$pdf = new Pdf('form.pdf');
$pdf->fillForm('data.fdf')
    ->saveAs('filled.pdf');

// Set backround from another PDF
$pdf = new Pdf('my.pdf');
$pdf->background('back.pdf')
    ->saveAs('watermarked.pdf');

// Stamp with another PDF
$pdf = new Pdf('my.pdf');
$pdf->stamp('overlay.pdf')
    ->saveAs('stamped.pdf');

// Get data
$pdf = new Pdf('my.pdf');
$data = $pdf->getData();

// Get form data fields
$pdf = new Pdf('my.pdf');
$data = $pdf->getDataFields();


// TBD ... some operations are still missing
```

### Options

You can add several of the following options to each operation above.

```php
use mikehaertl\pdftk\Pdf;

$pdf = new Pdf('my.pdf');

$pdf->allow('AllFeatures')      // Change permissions
    ->flatten()                 // Merge form data into document
    ->compress($value)          // Compress/Uncompress
    ->keepId('first')           // Keep first/last Id of combined files
    ->dropXfa()                 // Drop XFA from older PDFs
    ->setPassword($pw)          // Set owner password
    ->setUserPassword($pw)      // Set user password
    ->passwordEncryption(128)   // Set password encryption strength
    ->saveAs('new.pdf');
```
