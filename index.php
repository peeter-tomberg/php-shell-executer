<?php
include_once 'ShellExecuter.php';
$se = new ShellExecuter("sleep 5", 1);
$se->execute();