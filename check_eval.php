<?php
// force show errors
ini_set('display_errors', 1); // report errors
ini_set('display_startup_errors', 1); // report php startup errors
error_reporting(E_ALL); // report all errors

// config
$root = getcwd();
$extensions = array ( 'php' );
$regexpr = '/([^-_a-z]eval\s*\(|@'.'include)/i'; // @'.'include is to avoid matching this php file
$ignore = array (
);

session_start();
//var_dump ($_POST);
//var_dump ($_SESSION);

if (isset($_POST['actions'])) {
    foreach ($_POST["actions"] as $id => $action) {
        $filepath = $_SESSION['infected_files'][$id]['filepath'];
        switch ($action) {
            case 'delete':
                if (is_file ($filepath)) {
                    unlink($filepath);
                    echo 'deleted '.$filepath.'<br />';
                } else {
                    echo 'is not file '.$filepath.'<br />';
                }
                break;
            
            default:
                echo 'kept '.$filepath.'<br />';
                break;
        }
    }
} else {
    $_SESSION['infected_files']=array ();
    $iterator = new RecursiveDirectoryIterator($root);
    foreach(new RecursiveIteratorIterator($iterator) as $filepath)
    {
        $filepath=(string)$filepath;
        $tmp = explode('.', $filepath);
        if (in_array(strtolower(array_pop($tmp)), $extensions)) {
            $content = file_get_contents($filepath);
            if (preg_match($regexpr, $content, $name)) {
                if (!in_array ($filepath, $ignore)) {
                    array_push ($_SESSION['infected_files'], array (
                        'filepath' => $filepath,
                        'content' => $content
                    ));
                }
            }
        }
    }
    $nb_infected_files = count ($_SESSION['infected_files']);
    if ($nb_infected_files > 0) {
        http_response_code (400); # trigger error for cron
        echo 'Found '.$nb_infected_files.' infected files:<br />';
        echo '<form method="post"><table><thead><tr><th>keep</th><th>delete</th><th>file</th></tr></thead><tbody>';
        foreach($_SESSION['infected_files'] as $id => $file) {
            echo '<tr><td><input type="radio" name="actions['.$id.']" value="keep" checked="checked"></td>';
            echo '<td><input type="radio" name="actions['.$id.']" value="delete"></td>';
            echo '<td><details><summary>'.$file['filepath'].'</summary>';
            echo '<pre>'.htmlentities ($file['content']).'</pre></details></td></tr>';
        }
        echo '</table><input type="submit"/></form>';
    } else {
        echo 'No infected file found<br />';
    }
}
echo '<a href=".">Analyse again</a>';
?>
