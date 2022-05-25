#### 简要说明
UTForum是一款简洁的社区论坛系统，基于UT框架开发，遵循Apache2.0开源。
#### 系统要求
Nginx/Apache/IIS.  
PHP5/PHP7/PHP8  
UT Framework 3.x
#### 安装方式 
- 下载安装包并解压
- 运行127.0.0.1进行初始配置
- 初始超级账户密码，均为admin
#### 敏感安全
.config配置包含敏感信息，为防止远程访问和下载，您必须在站点配置文件中设置禁止非本地访问.config。
Nginx
```
location ~* \.(log|config|cms)$ {
deny all;
}
```
Apache
```
<Files ~ "\.config$">
Order allow,deny
Deny from all
</Files>
```
#### [演示](http://frame.usualtool.com/test/forum/)