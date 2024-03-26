<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

$file_path = $_GET['start_file'] ?? false;

if($file_path && file_exists($file_path)) {

    $csv_path = dirname(__FILE__) . '/'. $file_path;

    function my_autoloader($class)
    {
        include 'Classes/' . $class . '.php';
    }

    spl_autoload_register('my_autoloader');

    $importApp = new App();

    $importApp->truncate_table = $_GET['truncate_table'] ?? false;

    try {
        $importResults = $importApp->importFromCsv($csv_path);
    } catch (Exception $e) {
        die($e->getMessage());
    }

    echo '<h3>Results</h3><pre>';
    print_r($importResults);
    echo '</pre><br/><hr/><br/>'; // :-D

}else{

    echo 'No file.';

}

