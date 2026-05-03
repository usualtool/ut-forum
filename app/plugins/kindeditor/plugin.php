<?php
class Kindeditor {
    public function index() {
        echo '<link rel="stylesheet" href="/assets/plugins/kindeditor/themes/default/default.css" />' . PHP_EOL;
        echo '<link rel="stylesheet" href="/assets/plugins/kindeditor/plugins/code/prettify.css" />' . PHP_EOL;
        echo '<script charset="utf-8" src="/assets/plugins/kindeditor/kindeditor.js?v=1.3"></script>' . PHP_EOL;
        echo '<script charset="utf-8" src="/assets/plugins/kindeditor/lang/zh-CN.js"></script>' . PHP_EOL;
        echo '<script charset="utf-8" src="/assets/plugins/kindeditor/plugins/code/prettify.js"></script>' . PHP_EOL;
        echo '<script>' . PHP_EOL;
        echo 'var KDEDITOR;' . PHP_EOL;
        echo 'KindEditor.ready(function (K) {' . PHP_EOL;
        echo '    KDEDITOR = K.create(\'textarea[name="ut-editor"]\', {' . PHP_EOL;
        echo '        cssPath: [\'/assets/plugins/kindeditor/plugins/code/prettify.css\'],' . PHP_EOL;
        echo '        allowFileManager: false,' . PHP_EOL;
        echo '        urlType: \'domain\',' . PHP_EOL;
        echo '        afterBlur: function(){this.sync();}' . PHP_EOL;
        echo '    });' . PHP_EOL;
        echo '    if($("#ut-content").length > 0){' . PHP_EOL;
        echo '        KDEDITOR.html($("#ut-content").val());' . PHP_EOL;
        echo '    }' . PHP_EOL;
        echo '});' . PHP_EOL;
        echo '</script>' . PHP_EOL;
        echo '<textarea name="ut-editor" id="ut-editor" style="width:100%;height:300px;visibility:hidden;"></textarea>' . PHP_EOL;
        echo '<script type="text/javascript">' . PHP_EOL;
        echo '    $(function () { prettyPrint(); });' . PHP_EOL;
        echo '</script>' . PHP_EOL;
    }
    public function simple() {
        echo '<link rel="stylesheet" href="/assets/plugins/kindeditor/themes/default/default.css" />' . PHP_EOL;
        echo '<link rel="stylesheet" href="/assets/plugins/kindeditor/plugins/code/prettify.css" />' . PHP_EOL;
        echo '<script charset="utf-8" src="/assets/plugins/kindeditor/kindeditor.js"></script>' . PHP_EOL;
        echo '<script charset="utf-8" src="/assets/plugins/kindeditor/lang/zh-CN.js"></script>' . PHP_EOL;
        echo '<script charset="utf-8" src="/assets/plugins/kindeditor/plugins/code/prettify.js"></script>' . PHP_EOL;
        echo '<script>' . PHP_EOL;
        echo 'var KDEDITOR;' . PHP_EOL;
        echo 'KindEditor.ready(function (K) {' . PHP_EOL;
        echo '    KDEDITOR = K.create(\'textarea[name="ut-editor"]\', {' . PHP_EOL;
        echo '        cssPath: [\'/assets/plugins/kindeditor/plugins/code/prettify.css\'],' . PHP_EOL;
        echo '        allowFileManager: false,' . PHP_EOL;
        echo '        urlType: \'domain\',' . PHP_EOL;
        echo '        afterBlur: function(){this.sync();},' . PHP_EOL;
        echo '        items: [' . PHP_EOL;
        echo '            \'fontname\', \'fontsize\', \'|\', \'forecolor\', \'hilitecolor\', \'bold\', \'italic\', \'underline\',' . PHP_EOL;
        echo '            \'removeformat\', \'|\', \'justifyleft\', \'justifycenter\', \'justifyright\', \'insertorderedlist\',' . PHP_EOL;
        echo '            \'insertunorderedlist\', \'|\', \'emoticons\', \'image\', \'link\'' . PHP_EOL;
        echo '        ]' . PHP_EOL;
        echo '    });' . PHP_EOL;
        echo '    if($("#ut-content").length > 0){' . PHP_EOL;
        echo '        KDEDITOR.html($("#ut-content").val());' . PHP_EOL;
        echo '    }' . PHP_EOL;
        echo '});' . PHP_EOL;
        echo '</script>' . PHP_EOL;
        echo '<textarea name="ut-editor" id="ut-editor" style="width:100%;height:300px;visibility:hidden;"></textarea>' . PHP_EOL;
        echo '<script type="text/javascript">' . PHP_EOL;
        echo '    $(function () { prettyPrint(); });' . PHP_EOL;
        echo '</script>' . PHP_EOL;
    }
    public function upload() {
        $config = $GLOBALS['config'] ?? [];
        $dir_name = $_GET['dir'] ?? 'image';
        $ext_arr = [
            'image' => ['gif', 'jpg', 'jpeg', 'png', 'bmp'],
            'flash' => ['swf', 'flv'],
            'media' => ['swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'],
            'file'  => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'],
        ];
        $save_path = OPEN_ROOT . '/assets/upload/';
        $save_url  = ($config['APPURL'] ?? '') . '/assets/upload/';
        $max_size  = 1000000; // 1MB

        $save_path = realpath($save_path) . '/';
        $error_messages = [
            1 => '超过php.ini允许的大小。',
            2 => '超过表单允许的大小。',
            3 => '图片只有部分被上传。',
            4 => '请选择文件。',
            6 => '找不到临时目录。',
            7 => '写文件到硬盘出错。',
            8 => '文件上传被扩展中断。',
        ];
        if (!empty($_FILES['imgFile']['error'])) {
            $code = $_FILES['imgFile']['error'];
            $this->sendJson(['error' => 1, 'message' => $error_messages[$code] ?? '未知上传错误。']);
        }
        if (empty($_FILES['imgFile'])) {
            $this->sendJson(['error' => 1, 'message' => '请选择文件。']);
        }
        $file_name = $_FILES['imgFile']['name'];
        $tmp_name  = $_FILES['imgFile']['tmp_name'];
        $file_size = $_FILES['imgFile']['size'];
        if (!$file_name) {
            $this->sendJson(['error' => 1, 'message' => '文件名为空。']);
        }
        if (!is_dir($save_path)) {
            $this->sendJson(['error' => 1, 'message' => '上传目录不存在。']);
        }
        if (!is_writable($save_path)) {
            $this->sendJson(['error' => 1, 'message' => '上传目录没有写权限。']);
        }
        if (!is_uploaded_file($tmp_name)) {
            $this->sendJson(['error' => 1, 'message' => '上传失败，非合法上传文件。']);
        }
        if ($file_size > $max_size) {
            $this->sendJson(['error' => 1, 'message' => '上传文件大小超过限制（最大 1MB）。']);
        }
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($file_ext, $ext_arr[$dir_name] ?? [])) {
            $allowed = implode(',', $ext_arr[$dir_name] ?? []);
            $this->sendJson(['error' => 1, 'message' => "只允许以下格式：{$allowed}"]);
        }
        $ymd = date('Ymd');
        $save_path .= "{$dir_name}/{$ymd}/";
        $save_url  .= "{$dir_name}/{$ymd}/";
        if (!is_dir($save_path)) {
            if (!mkdir($save_path, 0755, true)) {
                $this->sendJson(['error' => 1, 'message' => '无法创建上传目录。']);
            }
        }
        $new_file_name = date('YmdHis') . '_' . rand(10000, 99999) . '.' . $file_ext;
        $file_path = $save_path . $new_file_name;
        if (!move_uploaded_file($tmp_name, $file_path)) {
            $this->sendJson(['error' => 1, 'message' => '文件保存失败。']);
        }
        chmod($file_path, 0644);
        $this->sendJson([
            'error' => 0,
            'url'   => $save_url . $new_file_name
        ]);
    }
    public function manage() {
        $config = $GLOBALS['config'] ?? [];
        $root_path = OPEN_ROOT . '/assets/upload/';
        $root_url  = ($config['APPURL'] ?? '') . '/assets/upload/';
        $ext_arr   = ['gif', 'jpg', 'jpeg', 'png', 'bmp'];
        $path = trim($_GET['path'] ?? '', '/');
        if ($path !== '') {
            if (strpos($path, '..') !== false || strpos($path, '\\') !== false) {
                $this->sendJsonError('Access is not allowed.');
            }
            if (substr($path, -1) !== '/') {
                $this->sendJsonError('Parameter is not valid.');
            }
        }
        $current_path = realpath($root_path) . ($path ? '/' . $path : '');
        $current_url  = $root_url . $path;
        $current_dir_path = $path;
        $moveup_dir_path = '';
        
        if ($path) {
            $parts = explode('/', rtrim($path, '/'));
            array_pop($parts);
            $moveup_dir_path = implode('/', $parts);
            if ($moveup_dir_path !== '') {
                $moveup_dir_path .= '/';
            }
        }
        if (!$current_path || !file_exists($current_path) || !is_dir($current_path)) {
            $this->sendJsonError('Directory does not exist.');
        }
        $order = strtolower($_GET['order'] ?? 'name');
        if (!in_array($order, ['name', 'size', 'type'])) {
            $order = 'name';
        }
        $file_list = [];
        if ($handle = opendir($current_path)) {
            while (($filename = readdir($handle)) !== false) {
                if ($filename[0] === '.') continue; // 跳过 . 和 ..

                $full_path = $current_path . $filename;
                $file_info = [
                    'filename' => $filename,
                    'datetime' => date('Y-m-d H:i:s', filemtime($full_path)),
                ];
                if (is_dir($full_path)) {
                    $file_info['is_dir']     = true;
                    $file_info['has_file']   = (count(scandir($full_path)) > 2); // 是否非空
                    $file_info['filesize']   = 0;
                    $file_info['is_photo']   = false;
                    $file_info['filetype']   = '';
                } else {
                    $file_info['is_dir']     = false;
                    $file_info['has_file']   = false;
                    $file_info['filesize']   = filesize($full_path);
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $file_info['is_photo']   = in_array($ext, $ext_arr);
                    $file_info['filetype']   = $ext;
                }
                $file_list[] = $file_info;
            }
            closedir($handle);
        }
        usort($file_list, function($a, $b) use ($order) {
            if ($a['is_dir'] && !$b['is_dir']) return -1;
            if (!$a['is_dir'] && $b['is_dir']) return 1;
            switch ($order) {
                case 'size':
                    return $a['filesize'] <=> $b['filesize'];
                case 'type':
                    return strcmp($a['filetype'], $b['filetype']);
                default: // name
                    return strcmp($a['filename'], $b['filename']);
            }
        });
        $result = [
            'moveup_dir_path' => $moveup_dir_path,
            'current_dir_path' => $current_dir_path,
            'current_url' => $current_url,
            'total_count' => count($file_list),
            'file_list' => $file_list,
        ];
        $this->sendJson($result);
    }
    private function sendJson($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    private function sendJsonError($message) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 1,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}