Document of Raichu
---

### 项目简介
- Di及单元测试的良好支持
- 基于yield实现了协程堆栈的异步控制台.
- 路由采用Restful风格及自动识别模式
- 灵活的hook机制，中间层采用单例接口模式生产不同的middleware
- 抽象的After/Before让我们更好的初始化和释放资源
- 数据层设计大胆使用了开源的idiorm作为支撑
- 提供了灵活的Clockwork监控，方便我们快速DebugApi
- 单入口加载及Composer的引进，让组件更加的丰富多彩
- 命名空间完全按照PSR-4规范, 代码规范清真
- 模块化/微服务化, 各模块完全解耦, 互不影响, 提升可维护性

### 项目特点

- 模块化设计，核心足够轻量

### 编译环境

- **请只用 PHP v5.6.x 以上版本编译执行**

### 依赖包

- composer dependency


### 服务搭建
```
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

