<?php

//variabele voor de login gegevens voor het database
$db_host = '127.0.0.1';
$db_user = 'root';
$db_password = '';
$db_database = 'rest';

//connectie met het database
$db = mysqli_connect($db_host, $db_user, $db_password, $db_database) or die(mysqli_connect_error());




