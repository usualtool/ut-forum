<?php
use usualtool\Lib\Code;
$captcha = new Code();
$captcha->createImage();
$_SESSION['authcode']=$captcha->GetCode();