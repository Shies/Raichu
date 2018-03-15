Document of Raichu
---

### 项目简介
1. 灵活的composer引擎加载
2. 模块化/微服务化, 各模块完全解耦, 互不影响, 提升可维护性
3. 命名空间完全按照psr-4规范
4. 整合了yield-console异步处理, 代码规范清真


### 服务搭建
```json
server {
    listen       80;
    server_name  l.raichu.com;

    index index.html index.htm index.php;
    set $root_path '/YourPath/Raichu/Public';
    root $root_path;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php($|/) {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $root_path$fastcgi_script_name;
        include fastcgi.conf;
        fastcgi_param  PATH_INFO $fastcgi_path_info;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
    }

    location ~ /\.ht {
            deny all;
    }
}
```

