[TOC]

# 目录

## 编写目标

搞PHP也5年了，算是有一些大型项目的开发经验，做个分享希望可以帮助到一些有点迷茫的同学，同时接受各方的批评和指点。

## 奇淫巧技

### 1) 非阻塞请求

是不是你已厌烦了PHP的同步阻塞IO，那就来看一下利用PHP如何实现原生的非阻塞吧，保证让你有一种恍然大悟的赶脚。

**场景:**

消息推送但不考虑结果状态，如Webhook

### 2) Promises/A+

`Promise`作为异步编程的解决方案，现在已经在Js代码中烂大街了。其实PHP中也有类似的实现，尤其是 `all` 方法值得PHP程序员学习借鉴，我们可以以此来达到并发请求的目标，同时也确保了写法的优雅程度。当然我们需要知道的是，PHP中 `Promise` 的实现也是同步，只是写法看似是异步而已。

**场景:**

并发请求接口，如批量发送邮件，爬虫

### 3) Redis管道

如果对 `Redis` 进行批量操作的话，管道是一个很好的选择。与 `单一操作Key` 相比，其性能有了大幅的提升，主要是因为 `TCP` 连接中减少"交互往返"的时间。此时你是否会想到另外两个批量操作的命令 `mset` 以及 `mget` ，它们和 `pipeline`分别用在什么场景下呢？好奇的同学不妨一探究竟。

**场景:**

批量操作 `Redis` 中的键值，如缓存用户列表的信息

### 4) MQ协议

AMQP（Advanced Message Queuing Protocol），一个标准的高级消息队列协议。
    
具体一点，该协议描述的内容包含哪些部分？实现 原理类似于什么？协议实现如何保证可靠性？
    
心跳的话，基于何种协议实现？

**场景:**

异步任务执行，降低功能模块之间的耦合

### 5) MySQL批量插入

对于一些数据量较大的系统，数据库面临的问题除了查询效率低下，还有就是数据入库时间长。特别像报表系统，每天花费在数据导入上的时间可能会长达几个小时或十几个小时之久。因此，优化数据库插入性能是很有意义的。

数据插入优化时很容易想到合并insert语句进而达到批量添加的目的。之前做过一个性能测试，验证一下插入1000条数据的时间，最后发现一次性插入的话算上网络延迟撑死也就3s，而如果逐条插入，即便采用连接池，时间也得在300s开外，如果每插入一次还要建一次连接的话，那时间可想而知肯定会更长，所以能批量插入的时候绝对要首选。

有的时候还需要注意一点就是autocommit，如果起始设置为false，则后续做的插入或者修改操作都会在最后一次性提交，所以批量插入的时候可以采用 合并数据+合并事务+有序插入 的方式。

**场景:**

CSV文件数据上传保存/用户行为数据保存

### 6) 守护进程

在处理消息队列中的任务时，采用守护进程的方式处理，但守护进程需要考虑的因素有很多，比如内存溢出，程序退出，优雅重启，避免窗口退出造成进程死掉

### 7) 多线程

多线程程序只能运行在cli模式下，而且其实现与其他语言有很大的不同，多个线程之间不能通过共享内存变量的方式进行通信，因为多线程之间被互相隔离了

### 8) 内存使用优化

如何控制内存的使用率？

- 变量引用
- 迭代器
- 限制进程可使用内存

### 9) 插件机制实现

使用钩子可以更好的做到切面编程，说到底就是面向接口的编程。常见的Wordpress中就有很多钩子，这也造就了一大批优秀的WP插件。

### 10) 日志管理

参考一下 [Monolog](https://github.com/Seldaek/monolog)，但此处更重要的是观察者模式如何实现？以及日志能否异步处理或者多观察者并行处理？

### 11) 排序算法

说到排序算法真是一把辛酸泪啊，牛逼的公司必问，而且逢考必挂。

常见的排序算法有几种？快排如何实现？堆排序解决了什么问题？能否再举几个应用的示例？

### 12) Composer

`Composer`已成为PHP库引入的规范，如果你写的库文件仍然不支持被自由引用，那就有点out了。不过要了解Composer时如何引入自己编写的库时需要知道 `Psr-4` 规范

### 13) 锁机制

并发编程必备知识，常见锁有几种类型？各类锁的使用场景？

- 分布式锁
- 读写锁
- 乐观锁

### 14) MySQL字段类型

需要注意用PHP查询数据库得到的数据类型与数据库中存储的未必一致。[PHP从MySQL取出int数据，变成了string](http://www.druidcoder.cn/2016/05/10/mysql-driver/)，所以在做API接口的时候，留心一下 `$mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1); `。

### 15) Cache-Aside

当存在缓存的时候，如何确保读取到的数据是正确的？

### 16) Zookeeper

微服务实现难免需要了解Zookeeper，但具体到实现阶段，是否要用PHP实现服务注册和服务发现那就另当别论了，一般会考虑常驻进程的语言，而非PHP。可以参考一下 Service Mesh 的实现。

### 17) 静态变量

静态变量的作用域？说是静态变量怎么还可以赋值？

### 18) 数据库连接池

原生PHP没办法实现，考虑一下周边的解决方案，比如采用数据库中间件去实现连接池的功能。

### 19) 数据库长连接

仅在PHP-FPM管控下可实现长连接，Apache的话就别想了。不过一定要想好什么时候释放连接资源，别到时候出现`too many clients`的问题。

### 20) 文件流式上传

玩惯了Node.js的流式处理，感觉PHP处理文件上传过程中先让文件落地的方式实在是让人无奈，那有没有别的办法可以实现流式上传呢？主要是想减少本地磁盘IO

### 21) apiDoc

相比于 `Swagger`，`apiDoc` 对注释的写法要求

### 22) MySQL读写分离

利用框架级别实现还是用中间件实现？各有什么优缺点？

除了业务读写分离，最重要的反倒是如何保障主从之间的同步

### 23) 软件安防
### 24) 迭代器

请简述Array, ArrayObject和ArrayIterator之间的区别?

如果有看过PDO::query的返回值类型的话，我们会发现，这个方法返回的 PDOStatement，正是对 Iterator 的实现。

### 25) 并发下的事务安全
### 26) 分片上传超大文件

如何分片以及如何合并分片

### 27) Sphinx vs Elasticsearch

Sphinx的不足

- 配置繁琐
- 中文分词coreseek不再维护
- 相关度排序实现不太友好
- 无法做到实时更新索引

### 28) Redis Cluster

Redis Cluster下客户端原生不支持多key操作

### 29) MySQL Proxy

作用:

- 分库分表
- 读写分离
- 连接池

### 30) 数据库分库分表

大表分割会使得用户在对数据库进行增删改查时候，服务器CPU的负载下降，性能会提升。这种技术可分为两种：垂直分表与水平分表。

- 垂直拆分

垂直拆分是一种把数据库中的表按列变成几张表的方法，这样可以降低表的复杂度和字段的数目，从而达到优化的目的。 

示例一：在Users表中有一个字段是家庭地址，这个字段是可选字段，而且你在数据库操作的时候除了个人信息外，你并不需要经常读取或是改写这个字段。那么，为什么不把他放到另外一张表中呢？ 这样会让你的表更小，性能也更好，对于用户表来说，只有用户ID，用户名，口令，用户角色等会被经常使用。

示例二： 你有一个叫 “last_login” 的字段，它会在每次用户登录时被更新。但是，每次更新都会导致该表的查询缓存被清空。所以，你可以把这个字段放到另一个表中，这样就不会影响你对用户ID，用户名，用户角色的不停地读取了，因为查询缓存会帮你提高性能。 

另外，你需要注意的是，这些被分出去的字段所形成的表，你不会经常性地去Join他们，不然的话，这样的性能会比不分割时还要差，而且，会是极数级的下降。 

- 水平拆分

如何做分布才能让每个数据库节点的负载均衡？

- 分区

那数据库分区与分库分表的区别？逻辑代码可以不做修改吗？

### 31) MySQL严格模式

有些集成的PHP运行环境(WAMP/XAMPP)自带的MySQL貌似都没有开启MySQL的严格模式。那何为MySQL的严格模式，简单来说就是MySQL自身对数据进行严格的校验（格式、长度、类型等），比如一个整型字段我们写入一个字符串类型的数据，在非严格模式下MySQL不会报错，同样如果定义了char或varchar类型的字段，当写入或更新的数据超过了定义的长度也不会报错。MySQL开启了严格模式从一定程序上来讲是对我们代码的一种测试，如果我们的开发环境没有开启严格模式在开发过程中也没有遇到错误，那么在上线或代码移植的时候将有可能出现不兼容的情况，因此在开发过程做最好开启MySQL的严格模式。

**如何开启**

1. 可以通过执行SQL语句来开启

    但是只对当前连接有效，下面是SQL语句：
`set sql_mode="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION";`

2. 通过修改MySQL的配置文件

    在`[mysqld]`下查找`sql-mode`，将此行修改成为: 
`sql-mode="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"`，推荐第二种方法，可以一劳永逸。

### 32) PDO

参见: [mysqli vs PDO](http://php.net/manual/en/mysqlinfo.api.choosing.php)

优势：

- SQL预处理提高了安全性以及SQL生成的效率
- 迭代器的实现支持读取大数据量的场景
- 数据库迁移成本较低 PG <=> MySQL
- TP框架优先推荐

### 33) DocumentFragment(文档碎片)的应用

- Wordpress中应用

```html
<html5>
    <head>
        <style type="text/css">
        .hide: {
            display: none;
        }
        </style>
        <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    </head>

    <body>
        <div>
            <span class='tab'>所有资源</span>
            <span class='tab' data-type='cat1'>解决方案</span>
            <span class='tab' data-type='cat2'>视野观点</span>
            <span class='tab' data-type='cat3'>可视化技巧</span>
        </div>
        <ul class="list-items" condition1="" condition2=""></ul>
    </body>
    <?php
        $ret = [
            ['url' => '/foo', 'img' => 'foo.png', 'title' => 'Foo item', 'cat' => 'cat1'],
            ['url' => '/foo2', 'img' => 'foo2.png', 'title' => 'Foo item2', 'cat' => 'cat2'],
            ['url' => '/foo3', 'img' => 'foo3.png', 'title' => 'Foo item3', 'cat' => 'cat3'],
        ];
    ?>
    <script>
    function itemFunc(currentValue, index, arr) {
        /* return `<a href="${currentValue.url}" class="list-group-item">
            <div class="image">
              <img src="${currentValue.img}" />
            </div>
            <p class="list-group-item-text">${currentValue.title}</p>
            </a>
        `; */
        return '<li class="' + currentValue.cat + '"><a href="' + currentValue.url + '"><div class="image"><img src="' + currentValue.img + '" /></div><p class="list-group-item-text">' + currentValue.title + '</p></a></li>';
    }

    function filterItems() {
        var condition1 = $('.list-items').attr('condition1');
        var condition2 = $('.list-items').attr('condition2');
        $('.list-items > li').show();
        if (!condition1 && !condition2) return;
        $('.list-items > li').each(function(index, item) {
            if (!$(item).hasClass(condition1)) {
                $(item).hide();
            }
        });
    }
    // var fragment = document.createDocumentFragment();
    /*for (var i = 0; i < 50; i++) {
        var tmpNode = document.createElement("div");
        tmpNode.innerHTML = "test" + i;
        fragment.appendChild(tmpNode);
    }*/
    /*document.body.appendChild(fragment);*/
    $(function() {
        /*var ajaxUrl = 'demo_test.php';
        $.get(ajaxUrl, function(data, status) {
            console.log('数据: ' + data + '\n状态: ' + status);
            var list = data.data;
            var list = [{
                url: '/foo',
                img: 'foo.png',
                title: 'Foo item'
            }, {
                url: '/bar',
                img: 'bar.png',
                title: 'Bar item'
            }, ];
            $('.list-items').html(list.map(itemFunc).join(''));
        });
        */
        var list = <?php echo json_encode($ret); ?>;
        $('.list-items').html(list.map(itemFunc).join(''));
        $('.tab').click(function() {
            var condition = $(this).attr('data-type') || '';
            $('.list-items').attr('condition1', condition);
            filterItems()
        })
    });
    </script>
</html5>
```

### 34) Bloom filter

### 35) mysql_unbuffered_query 与 mysql_query 的区别

mysql_unbuffered_query 是基于迭代器实现的吗？为什么在内存利用上会更好？

参见: [Buffered and Unbuffered queries](http://php.net/manual/en/mysqlinfo.concepts.buffering.php)

### 36) MySQL查询大小写不敏感

主要是字符集设置问题，在处理具体业务的时候一定要明确自己想要的。

### 37) 检测敏感词

社交聊天，CMS中常用功能，但如何保证查询性能以及准确率。

### 38) PHPOffice

### 39) 页面静态化

**两种策略**

- 真静态
- 伪静态
    - Nginx Rewrite
    - 路由解析

**真静态的优点**

- 访问速度快(不用调用php解析模块，不需要查询数据库)
- 利于SEO
- 防止SQL注入

由此可以看出门户网站最需要静态化

**在以下情况下不建议使用真静态**

- 网站的实时性比较高，也就是说查询频繁（股票，基金）
- 查询一次后，以后很少查询（国家学历认证网，电信话费查询系统）

### 40) 城市站点

按照城市划分子站，其中有哪些考虑要素？

## 参考

- [PHP标准规范](https://psr.phphub.org/)
- [PHP之道](http://laravel-china.github.io/php-the-right-way/)
- [PHP非阻塞实现方法](https://www.awaimai.com/660.html)
- [MySQL批量修改](https://www.awaimai.com/2103.html)
- [WordPress Actions and Filters](https://code.tutsplus.com/articles/the-beginners-guide-to-wordpress-actions-and-filters--wp-27373)
- [MySQL LIKE 用法：搜索匹配字段中的指定内容](http://www.php230.com/mysql-like.html)
- [在PHP中使用协程实现多任务调度](http://www.laruence.com/2015/05/28/3038.html)
- [PHP+MySQL导出大量数据(Iterator yield)](https://www.lbog.cn/blog/22)
- [常见排序算法性能比较](https://www.lbog.cn/blog/39)
- [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)
- [DRDS在浙江移动的生产应用中落地](https://yq.aliyun.com/articles/591914)
- [PHP开发异步高性能的MySQL代理服务器](http://rango.swoole.com/archives/288)
