<?php
$output = shell_exec('php -l c:/xampp/htdocs/mayush/app/Http/Controllers/HomeController.php 2>&1');
file_put_contents('c:/xampp/htdocs/mayush/lint2.txt', $output);
