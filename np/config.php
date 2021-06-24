<?php
$db=mysqli_connect("newtea.mysql.tools","newtea_cintegr","F62ih^U6-v","newtea_cintegr");
mysqli_query($db,"set names utf8");
if(!$db) { echo("Database Error"); exit(); }