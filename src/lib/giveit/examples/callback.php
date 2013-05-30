<?php
/**
 * A simple means of testing callbacks.
 * The decrypted data from the callback is written to the file.
 */

require_once 'init.php';

$fh     = fopen('/tmp/callback.txt', 'w');
$result = $giveIt->parseCallback($_POST);

fwrite($fh, print_r($result, true));
