<?php
use library\UsualToolCode\UTCode;
$captcha = new UTCode();
$captcha->createImage();
$_SESSION['authcode']=$captcha->GetCode();