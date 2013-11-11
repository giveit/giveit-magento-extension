<?php

require_once 'init.php';

//$giveIt->debug = true;
$giveIt->setEnvironment('sandbox');

$result = $giveIt->verifyKeys();

if ($result) {
  echo "API keys are okay\n";
} else {
  echo "one or more keys are not correct\n";
}
