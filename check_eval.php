<?php
// force show errors
ini_set('display_errors', 1); // report errors
ini_set('display_startup_errors', 1); // report php startup errors
error_reporting(E_ALL); // report all errors

// config
$root = getcwd();
$extensions = array ( 'php' );
$regexpr = "/([^-_a-z]eval\s*\(|@"."include)/i"; // @"."include is to avoid matching this php file
$ignore = array (); # comma seperated array of filepath to ignore (false positives)

$infected_files = array ();
$iterator = new RecursiveDirectoryIterator($root);
foreach(new RecursiveIteratorIterator($iterator) as $filepath)
{
    $tmp = explode('.', $filepath);
    if (in_array(strtolower(array_pop($tmp)), $extensions)) {
        if (preg_match($regexpr, file_get_contents($filepath), $name)) {
            if (!in_array ($filepath, $ignore)) {
                array_push ($infected_files, $filepath);
            }
        }
    }
}
if (count ($infected_files) > 0) {
    http_response_code (400); # trigger error for cron
}
echo "Found ".count ($infected_files)." infected files:<br />";
echo implode ("<br />", $infected_files);
?>
