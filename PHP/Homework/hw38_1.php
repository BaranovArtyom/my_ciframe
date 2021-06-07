<?php
session_start();

$_SESSION['name'] = $_POST['name'];

echo '<a href="hw38_2.php">ссылка</a>';