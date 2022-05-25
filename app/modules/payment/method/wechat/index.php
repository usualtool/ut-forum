<?php
    require_once dirname(__FILE__).'/lib/WxPay.Config.php';
    use library\UsualToolInc\UTInc;
    $posnum=isset($_SESSION['posnum']) ? UTInc::SqlCheck($_SESSION['posnum']) : UTInc::SqlCheck($_GET["posnum"]);
    if(UTInc::IsApp()):
        $_SESSION['posnum']=$posnum;
        UTInc::GoUrl("to/jsapi.php?posnum=".$posnum,"");
    else:
        UTInc::GoUrl("to/native.php?posnum=".$posnum,"");
    endif;