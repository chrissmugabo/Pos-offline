<?php

    include_once("xlsxwriter.class.php");
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);
    $data     = json_decode($_POST['dataset']);
    $filename = $_POST['filename'] . "_" . date('Y-m-d') . '-' . date('H:i') . ".xlsx";
    $columns  = json_decode($_POST['columns']);
    $headings = [];
    foreach($columns as $column) {
        $headings[$column] = 'string';
    }
    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', TRUE, 200);
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    $writer = new XLSXWriter();
    $writer->setAuthor('TAME APPS');
    $writer->writeSheetHeader('Sheet1', $headings );
    foreach($data as $row) {
        $writer->writeSheetRow('Sheet1', (array)$row );
    }
    $writer->writeToStdOut();
    exit(0);

    //https://github.com/mk-j/PHP_XLSXWriter