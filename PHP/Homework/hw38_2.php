<?php
session_start();

echo "hello {$_SESSION['name']}";