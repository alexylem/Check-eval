<?php
// force show errors
ini_set('display_errors', 1); // report errors
ini_set('display_startup_errors', 1); // report php startup errors
error_reporting(E_ALL); // report all errors

// config
$password = '#############'; // generate password_hash here: https://phppasswordhash.com/
$root = getcwd();
$extensions = array ( 'php' );
$regexpr = '/^.*([^-_a-z]eval\s*\(|@'.'include).*$/im'; // @'.'include is to avoid matching this php file

// init
$ignores = array ();
if (file_exists ('ignores.json')) {
    $ignores = json_decode(file_get_contents ('ignores.json'));
}
session_start();

// start
if (isset ($_POST['view'])) {
    if (isset ($_SESSION['authenticated']) || password_verify ($_POST['password'], $password)) {
        $_SESSION['authenticated']=1;
        echo '<form method="post"><table><thead><tr><th>skip</th><th>ignore</th><th>delete</th><th>file</th></tr></thead><tbody>';
        foreach($_SESSION['infected_files'] as $id => $file) {
            echo '<tr><td><input type="radio" name="actions['.$id.']" value="skip" checked="checked"></td>';
            echo '<td><input type="radio" name="actions['.$id.']" value="ignore"></td>';
            echo '<td><input type="radio" name="actions['.$id.']" value="delete"></td>';
            echo '<td><details><summary>'.$file['filepath'].'<br /><pre>'.$file['match'].'</pre></summary>';
            echo '<pre>'.htmlentities ($file['content']).'</pre></details></td></tr>';
        }
        echo '</table><input type="submit" value="Execute" /></form>';
    } else {
        echo '<font color="red">wrong password</font>';
        echo '<form method="post"><input type="hidden" name="view" value="1" /><input type="password" name="password" placeholder="Password" /><input type="submit" value="View" /></form>';
    }
} else if (isset($_POST['actions']) && isset($_SESSION['infected_files'])) {
    foreach ($_POST["actions"] as $id => $action) {
        $filepath = $_SESSION['infected_files'][$id]['filepath'];
        switch ($action) {
            case 'ignore':
                array_push ($ignores, $filepath);
                echo 'addined '.$filepath.' to ignores.json<br />';
                break;
            case 'delete':
                unlink($filepath);
                echo 'deleted '.$filepath.'<br />';
                break;
            default:
                echo 'kept '.$filepath.'<br />';
                break;
        }
    }
    file_put_contents('ignores.json', json_encode($ignores, JSON_PRETTY_PRINT));
    echo '<a href="'.basename(__FILE__).'">Analyse again</a>';
} else {
    $_SESSION['infected_files']=array ();
    $iterator = new RecursiveDirectoryIterator($root);
    foreach(new RecursiveIteratorIterator($iterator) as $filepath)
    {
        $filepath=(string)$filepath;
        $tmp = explode('.', $filepath);
        if (in_array(strtolower(array_pop($tmp)), $extensions)) {
            $content = file_get_contents($filepath);
            if (preg_match($regexpr, $content, $match)) {
                if (!in_array ($filepath, $ignores)) {
                    array_push ($_SESSION['infected_files'], array (
                        'filepath' => $filepath,
                        'match' => $match[0],
                        'content' => $content
                    ));
                }
            }
        }
    }
    $nb_infected_files = count ($_SESSION['infected_files']);
    if ($nb_infected_files > 0) {
        http_response_code (400); # trigger error for cron
        echo 'Found '.$nb_infected_files.' infected files<br />';
        echo '<form method="post">';
        echo '<input type="hidden" name="view" value="1" />';
        if (!isset ($_SESSION['authenticated'])) {
            echo '<input type="password" name="password" placeholder="Password" />';
        }
        echo '<input type="submit" value="View" /></form>';
    } else {
        echo 'No infected file found<br />';
    }
}
?>
