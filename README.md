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
 * Rotate pages
 * Fill forms, either from a FDF file or from a data array (UTF-8 aware!)
 * TBD ...

## Examples

```php
use mikehaertl\pdftk\Pdf;

// Extract pages 1-5 and 7,4,9 into a new file
$pdf = new Pdf('my.pdf');
$pdf->cat(1, 5)
    ->cat(array(7, 4, 9))
    ->saveAs('new.pdf');

// Extract pages from several files
$pdf = new Pdf;
$pdf->addFile('file1.pdf', 'A')     // Reference file as 'A'
    ->addFile('file2.pdf', 'B')     // Reference file as 'b'
    ->cat(1, 5, 'A')                // pages 1-5 from A
    ->cat(3, null, 'B')             // page 3 from B
    ->cat(7, 'end', 'B', null, 'E') // pages 7-end from B, rotated East
    ->cat('end',3,'A','even')       // even pages 3-end in reverse order from A
    ->saveAs('new.pdf');

// Fill Form
$pdf = new Pdf('form.pdf');
$pdf->fillForm(array('name'=>'ÄÜÖ äüö мирано čárka'))
    ->saveAs('filled.pdf');

// TBD

```
