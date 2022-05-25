<?php
$l=$_GET["l"];
$do=$_GET["do"];
if($do=="update"){
    $id=UsualToolCMS::sqlcheckx($_POST["id"]);
    $qq_appid=UsualToolCMS::sqlcheck($_POST["qq_appid"]);
    $qq_appkey=UsualToolCMS::sqlcheck($_POST["qq_appkey"]);
    $qq_reurl=UsualToolCMS::sqlcheck($_POST["qq_reurl"]);
    $wb_appid=UsualToolCMS::sqlcheck($_POST["wb_appid"]);
    $wb_appkey=UsualToolCMS::sqlcheck($_POST["wb_appkey"]);
    $wb_reurl=UsualToolCMS::sqlcheck($_POST["wb_reurl"]);
    $ww_appid=UsualToolCMS::sqlcheck($_POST["ww_appid"]);
    $ww_appkey=UsualToolCMS::sqlcheck($_POST["ww_appkey"]);
    $ww_reurl=UsualToolCMS::sqlcheck($_POST["ww_reurl"]);
    $qr_appid=UsualToolCMS::sqlcheck($_POST["qr_appid"]);
    $qr_appkey=UsualToolCMS::sqlcheck($_POST["qr_appkey"]);
    $qr_reurl=UsualToolCMS::sqlcheck($_POST["qr_reurl"]);
    if(!empty($id)){
        if($l=="qq"):
            UsualToolCMSDB::updateData("cms_connect",array("qq_appid"=>$qq_appid,"qq_appkey"=>$qq_appkey,"qq_reurl"=>$qq_reurl),"id='$id'");
        endif;
        if($l=="wb"):
            UsualToolCMSDB::updateData("cms_connect",array("wb_appid"=>$wb_appid,"wb_appkey"=>$wb_appkey,"wb_reurl"=>$wb_reurl),"id='$id'");
        endif;
        if($l=="ww"):
            UsualToolCMSDB::updateData("cms_connect",array("ww_appid"=>$ww_appid,"ww_appkey"=>$ww_appkey,"ww_reurl"=>$ww_reurl),"id='$id'");
        endif;
        if($l=="sm"):
            UsualToolCMSDB::updateData("cms_connect",array("qr_appid"=>$qr_appid,"qr_appkey"=>$qr_appkey,"qr_reurl"=>$qr_reurl),"id='$id'");
        endif;
    }else{
        if($l=="qq"):
            UsualToolCMSDB::insertData("cms_connect",array("qq_appid"=>$qq_appid,"qq_appkey"=>$qq_appkey,"qq_reurl"=>$qq_reurl));
        endif;
        if($l=="wb"):
            UsualToolCMSDB::insertData("cms_connect",array("wb_appid"=>$wb_appid,"wb_appkey"=>$wb_appkey,"wb_reurl"=>$wb_reurl));
        endif;
        if($l=="ww"):
            UsualToolCMSDB::insertData("cms_connect",array("ww_appid"=>$ww_appid,"ww_appkey"=>$ww_appkey,"ww_reurl"=>$ww_reurl));
        endif;
        if($l=="sm"):
            UsualToolCMSDB::insertData("cms_connect",array("qr_appid"=>$qr_appid,"qr_appkey"=>$qr_appkey,"qr_reurl"=>$qr_reurl));
        endif;
    }
        echo "<script>window.location.href='?m=authlogin&u=a_auth.php&l=$l'</script>";
}
$row=UsualToolCMSDB::queryData("cms_connect","","","","1","0")["querydata"][0];
    $id=$row["id"];
    $qq_appid=$row["qq_appid"];
    $qq_appkey=$row["qq_appkey"];
    $qq_reurl=$row["qq_reurl"];
    $wb_appid=$row["wb_appid"];
    $wb_appkey=$row["wb_appkey"];
    $wb_reurl=$row["wb_reurl"];
    $ww_appid=$row["ww_appid"];
    $ww_appkey=$row["ww_appkey"];
    $ww_reurl=$row["ww_reurl"];
    $qr_appid=$row["qr_appid"];
    $qr_appkey=$row["qr_appkey"];
    $qr_reurl=$row["qr_reurl"];
?>
<script type="text/javascript">
    $(function(){ $(".idTabs").idTabs(); }); 
</script>
<div class="idTabs">
    <ul class="tab">
        <li><a href="#qq" <?php if($l=="qq")echo"class=selected";?>>◇ QQ授权登陆 ◇</a></li>
        <li><a href="#wb" <?php if($l=="wb")echo"class=selected";?>>◇ 微博授权登陆 ◇</a></li>
        <li><a href="#ww" <?php if($l=="ww")echo"class=selected";?>>◇ 微信授权登陆 ◇</a></li>
        <li><a href="#sm" <?php if($l=="sm")echo"class=selected";?>>◇ 微信扫码登陆 ◇</a></li>
    </ul>
</div>
<div class="items">
    <div id="qq">
        <form action="?m=authlogin&u=a_auth.php&do=update&l=qq" method="post" id=form1 name=form1 onsubmit="return check();">
        <input type="hidden" name="id" value="<?php echo$id;?>" />
        <table width="100%" border="0" cellpadding="8" cellspacing="0" class="tablebasic">
            <tr>
                <td align="right">APPID</td>
                <td><input type="text" name="qq_appid" value="<?php echo$qq_appid;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td align="right">APPKEY</td>
                <td><input type="text" name="qq_appkey" value="<?php echo$qq_appkey;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td align="right">回调地址</td>
                <td><input type="text" name="qq_reurl" value="<?php if(!empty($qq_reurl)):echo$qq_reurl;else:echo"".$weburl."/modules/authlogin/connect/sdk_chat.php";endif;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td width=20%></td>
                <td><input name="submit" class="btn" type="submit" value="更新设置" /></td>
            </tr>
        </table>
        </form>
    </div>
    <div id="wb">
        <form action="?m=authlogin&u=a_auth.php&do=update&l=wb" method="post" id=form2 name=form2 onsubmit="return check();">
        <input type="hidden" name="id" value="<?php echo$id;?>" />
        <table width="100%" border="0" cellpadding="8" cellspacing="0" class="tablebasic">
            <tr>
                <td align="right">APPID</td>
                <td><input type="text" name="wb_appid" value="<?php echo$wb_appid;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td align="right">APPKEY</td>
                <td><input type="text" name="wb_appkey" value="<?php echo$wb_appkey;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td align="right">回调地址</td>
                <td><input type="text" name="wb_reurl" value="<?php if(!empty($wb_reurl)):echo$wb_reurl;else:echo"".$weburl."/modules/authlogin/connect/sdk_weibo.php";endif;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td width=20%></td>
                <td><input name="submit" class="btn" type="submit" value="更新设置" /></td>
            </tr>
        </table>
        </form>
    </div>
    <div id="ww">
        <form action="?m=authlogin&u=a_auth.php&do=update&l=ww" method="post" id=form3 name=form3 onsubmit="return check();">
        <input type="hidden" name="id" value="<?php echo$id;?>" />
        <table width="100%" border="0" cellpadding="8" cellspacing="0" class="tablebasic">
            <tr>
                <td align="right">APPID</td>
                <td><input type="text" name="ww_appid" value="<?php echo$ww_appid;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td align="right">APPKEY</td>
                <td><input type="text" name="ww_appkey" value="<?php echo$ww_appkey;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td align="right">回调地址</td>
                <td><input type="text" name="ww_reurl" value="<?php if(!empty($ww_reurl)):echo$ww_reurl;else:echo"".$weburl."/modules/authlogin/connect/sdk_weixin.php";endif;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td width=20%></td>
                <td><input name="submit" class="btn" type="submit" value="更新设置" /></td>
            </tr>
        </table>
        </form>
    </div>
    <div id="sm">
        <form action="?m=authlogin&u=a_auth.php&do=update&l=sm" method="post" id=form4 name=form4 onsubmit="return check();">
        <input type="hidden" name="id" value="<?php echo$id;?>" />
        <table width="100%" border="0" cellpadding="8" cellspacing="0" class="tablebasic">
            <tr>
                <td align="right">APPID</td>
                <td><input type="text" name="qr_appid" value="<?php echo$qr_appid;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td align="right">APPKEY</td>
                <td><input type="text" name="qr_appkey" value="<?php echo$qr_appkey;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td align="right">回调地址</td>
                <td><input type="text" name="qr_reurl" value="<?php if(!empty($qr_reurl)):echo$qr_reurl;else:echo"".$weburl."/modules/authlogin/connect/sdk_qrcode.php";endif;?>" size="80" class="inpMain" /></td>
            </tr>
            <tr>
                <td width=20%></td>
                <td><input name="submit" class="btn" type="submit" value="更新设置" /></td>
            </tr>
        </table>
        </form>
    </div>
</div>