<?php
// Intentional phpcs violation file - DO NOT MERGE.
// This file contains deliberate coding standard violations
// to validate that phpcs CI correctly reports them via reviewdog.
function intentional_violation(){$x=1+2;$y=$x*3;return $y;}
class BadClass{
function bad_method($a,$b){
$result=$a+$b;
return $result;
}
}
