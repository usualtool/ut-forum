	<link rel="stylesheet" href="<?php echo$GLOBALS["config"]["APPURL"];?>/plugins/kindeditor/themes/default/default.css" />
	<link rel="stylesheet" href="<?php echo$GLOBALS["config"]["APPURL"];?>/plugins/kindeditor/plugins/code/prettify.css" />
	<script charset="utf-8" src="<?php echo$GLOBALS["config"]["APPURL"];?>/plugins/kindeditor/kindeditor.js"></script>
	<script charset="utf-8" src="<?php echo$GLOBALS["config"]["APPURL"];?>/plugins/kindeditor/lang/zh-CN.js"></script>
	<script charset="utf-8" src="<?php echo$GLOBALS["config"]["APPURL"];?>/plugins/kindeditor/plugins/code/prettify.js"></script>
	<script>
	var KDEDITOR;
    KindEditor.ready(function (K) {
        KDEDITOR = K.create('textarea[name="ut-editor"]', {
            cssPath : ['<?php echo$GLOBALS["config"]["APPURL"];?>/plugins/kindeditor/plugins/code/prettify.css'],
            allowFileManager: false,
            urlType:'domain',
            afterBlur: function(){this.sync();}
        });
        if($("#ut-content").length>0){
            KDEDITOR.html($("#ut-content").val());
        }
    });
	</script>
	<textarea name="ut-editor" id="ut-editor" style="width:100%;height:300px;visibility:hidden;"></textarea>
    <script type="text/javascript">
        $(function () { prettyPrint(); });
    </script>