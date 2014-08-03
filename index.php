<?php
include_once 'source/ShellExecuter.php';
$se = new ShellExecuter("sleep 5", 1);
$se->execute();
