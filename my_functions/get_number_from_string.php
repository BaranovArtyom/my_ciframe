<?php
$str = '{"10":"OK"}';
$strWithoutChars = preg_replace('/[^0-9]/', '', $str);
echo $strWithoutChars;