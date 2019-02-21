[TOC]

# 目录

## 编写目标

搞PHP也5年了，算是有一些大型项目的开发经验，做个分享希望可以帮助到一些有点迷茫的同学，同时接受各方的批评和指点。

## 基础须知

需要了解5.x版本每次升级主要新增了哪些特性，有利于我们写出更优雅的代码。

## 奇淫巧技

### 1) MySQL

#### 1.1) 批量插入

对于一些数据量较大的系统，数据库面临的问题除了查询效率低下，还有就是数据入库时间长。特别像报表系统，每天花费在数据导入上的时间可能会长达几个小时或十几个小时之久。因此，优化数据库插入性能是很有意义的。

数据插入优化时很容易想到合并insert语句进而达到批量添加的目的。之前做过一个性能测试，验证一下插入1000条数据的时间，最后发现一次性插入的话算上网络延迟撑死也就3s，而如果逐条插入，即便采用连接池，时间也得在300s开外，如果每插入一次还要建一次连接的话，那时间可想而知肯定会更长，所以能批量插入的时候绝对要首选。

有的时候还需要注意一点就是autocommit，如果起始设置为false，则后续做的插入或者修改操作都会在最后一次性提交，所以批量插入的时候可以采用 合并数据+合并事务+有序插入 的方式。

插入记录时，MySQL会根据表的索引对插入的记录进行排序。如果插入大量数据时，这些排序会降低插入的速度。为了解决这种情况，在插入记录之前先禁用索引。等插入之后再启用索引。对于新创建的表，可以先不创建索引，等记录都导入以后再创建索引。这样可以提高导入数据的速度。

```
ALTER TABLE 表名 DISABLE KEYS;
ALTER TABLE 表名 ENABLE KEYS;
```

**[⬆ back to top](#table-of-contents)**

#### 1.2) 容量估算

计算数据库中各个表的数据量和每行记录所占用空间

- http://www.cnblogs.com/yzwdli/p/5337881.html
- http://itindex.net/detail/39281-%E6%95%B0%E6%8D%AE%E5%BA%93-%E5%AE%B9%E9%87%8F-%E6%80%A7%E8%83%BD

#### 1.3) 连接池

原生PHP没办法实现，考虑一下周边的解决方案，比如采用数据库中间件去实现连接池的功能。

#### 1.4) 长连接

仅在PHP-FPM管控下可实现长连接，Apache的话就别想了。不过一定要想好什么时候释放连接资源，别到时候出现`too many clients`的问题。

#### 1.5) 读写分离

利用框架级别实现还是用中间件实现？各有什么优缺点？

除了业务读写分离，最重要的反倒是如何保障主从之间的同步

#### 1.6) Proxy

作用:

- 分库分表
- 读写分离
- 连接池

#### 1.7) 分库分表分区

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

#### 1.8) 严格模式

有些集成的PHP运行环境(WAMP/XAMPP)自带的MySQL貌似都没有开启MySQL的严格模式。那何为MySQL的严格模式，简单来说就是MySQL自身对数据进行严格的校验（格式、长度、类型等），比如一个整型字段我们写入一个字符串类型的数据，在非严格模式下MySQL不会报错，同样如果定义了char或varchar类型的字段，当写入或更新的数据超过了定义的长度也不会报错。MySQL开启了严格模式从一定程序上来讲是对我们代码的一种测试，如果我们的开发环境没有开启严格模式在，开发过程中也没有遇到错误，那么在上线或代码移植的时候将有可能出现不兼容的情况，因此在开发过程做最好开启MySQL的严格模式。

**如何开启**

1. 可以通过执行SQL语句来开启

    但是只对当前连接有效，下面是SQL语句：
`set sql_mode="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION";`

2. 通过修改MySQL的配置文件

    在`[mysqld]`下查找`sql-mode`，将此行修改成为: 
`sql-mode="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"`，推荐第二种方法，可以一劳永逸。

#### 1.9) 查询大小写不敏感

主要是字符集设置问题，在处理具体业务的时候一定要明确自己想要的。

#### 1.10) ORM

ORM(Object Relational Mapping)到底是个啥？怎么理解对象关系映射？

ORM可以拆分为两部分理解，O以及RM，即对象以及关系映射。ORM Lib的作用就是将 对象 与 数据库表结构 进行映射关联，从而实现操控对象就是操控数据库中数据记录。

**优缺点**

- 直接拼写SQL会有以下几个问题：
    - 每条SQL语句都需要指明表的前缀
    - SQL语句复杂时，极易出现编写错误的情况
- ORM的优势：
    - 操纵对象相当于操控数据记录，较易规避问题

#### 1.11) 事务

明确事务的隔离级别。。。

并发下如何保证事务安全

#### 1.12) 存储过程

存储过程的价值体现在哪？为什么需要存储过程？

#### 1.13) 视图的价值

#### 1.14) 死锁

了解死锁之前，需要知道MySQL中行级锁和表级锁该怎么理解，并且什么原因会导致死锁，如何复现? 如何避免？

#### 1.15) Online DDL

#### 1.16) 小表驱动大表

适用于以下查询关键词: `in`, `exists`, `join`

- in/exists
    - 小表驱动大表

- join优化
    - 小表驱动大表
    - 被驱动集合建索引
    - innodb_buffer_pool_size

#### 1.17) 分组聚合

如何优化聚合的执行效率

#### 1.18) 范围查询

#### 1.19) 分页查询

采用延迟索引

#### 1.20) 左前缀原则

如(f1,f2,f3,fN)的复合索引

where条件按照索引建立的顺序来写，就可以使用到索引，而且因为查询优化器的存在，即便不按照顺序写，最后也能用到索引。

如果条件中不含中间某列，或者某列的查询条件是like，则会导致后面的列无法应用索引。

```
mysql> explain select * from ocenter_verify where uid = 0 and oid = 0 and type = 'mobile';
+----+-------------+----------------+------+---------------+-------------+---------+-------------------+------+-----------------------+
| id | select_type | table          | type | possible_keys | key         | key_len | ref               | rows | Extra                 |
+----+-------------+----------------+------+---------------+-------------+---------+-------------------+------+-----------------------+
|  1 | SIMPLE      | ocenter_verify | ref  | idx_example   | idx_example | 90      | const,const,const |   17 | Using index condition |
+----+-------------+----------------+------+---------------+-------------+---------+-------------------+------+-----------------------+
1 row in set (0.01 sec)

mysql> explain select * from ocenter_verify where uid = 0 and type = 'mobile' and verify = '1111';
+----+-------------+----------------+------+---------------+-------------+---------+-------+------+-----------------------+
| id | select_type | table          | type | possible_keys | key         | key_len | ref   | rows | Extra                 |
+----+-------------+----------------+------+---------------+-------------+---------+-------+------+-----------------------+
|  1 | SIMPLE      | ocenter_verify | ref  | idx_example   | idx_example | 4       | const |   34 | Using index condition |
+----+-------------+----------------+------+---------------+-------------+---------+-------+------+-----------------------+
1 row in set (0.00 sec)

mysql> explain select * from ocenter_verify where uid = 0 and type like 'mobile%' and verify = '1111';
+----+-------------+----------------+------+---------------+-------------+---------+-------+------+-----------------------+
| id | select_type | table          | type | possible_keys | key         | key_len | ref   | rows | Extra                 |
+----+-------------+----------------+------+---------------+-------------+---------+-------+------+-----------------------+
|  1 | SIMPLE      | ocenter_verify | ref  | idx_example   | idx_example | 4       | const |   34 | Using index condition |
+----+-------------+----------------+------+---------------+-------------+---------+-------+------+-----------------------+
1 row in set (0.01 sec)

mysql> explain select * from ocenter_verify where oid = 0 and type = 'mobile';
+----+-------------+----------------+------+---------------+------+---------+------+------+-------------+
| id | select_type | table          | type | possible_keys | key  | key_len | ref  | rows | Extra       |
+----+-------------+----------------+------+---------------+------+---------+------+------+-------------+
|  1 | SIMPLE      | ocenter_verify | ALL  | NULL          | NULL | NULL    | NULL |  191 | Using where |
+----+-------------+----------------+------+---------------+------+---------+------+------+-------------+
1 row in set (0.00 sec)

mysql> explain select * from ocenter_verify where type = 'mobile' and verify = '1111';
+----+-------------+----------------+------+---------------+------+---------+------+------+-------------+
| id | select_type | table          | type | possible_keys | key  | key_len | ref  | rows | Extra       |
+----+-------------+----------------+------+---------------+------+---------+------+------+-------------+
|  1 | SIMPLE      | ocenter_verify | ALL  | NULL          | NULL | NULL    | NULL |  191 | Using where |
+----+-------------+----------------+------+---------------+------+---------+------+------+-------------+
1 row in set (0.00 sec)
```

#### 1.21) 覆盖索引

如果查询的列恰好是索引的一部分，那么查询的时候就不需要回行到磁盘再找数据，这样的查询速度非常快。

#### 1.22) 精度损失

浮点类型常见问题，所有的语言貌似都存在此类问题。

### 2) 异步处理

是不是你已厌烦了PHP的同步阻塞IO，那就来看一下利用PHP如何实现原生的非阻塞吧，保证让你有一种恍然大悟的赶脚。

#### 2.1) Promises/A+

`Promise`作为异步编程的解决方案，现在已经在Js代码中烂大街了。其实PHP中也有类似的实现，尤其是 `all` 方法值得PHP程序员学习借鉴，我们可以以此来达到并发请求的目标，同时也确保了写法的优雅程度。当然我们需要知道的是，PHP中 `Promise` 的实现也是同步，只是写法看似是异步而已。

#### 2.2) MQ

AMQP（Advanced Message Queuing Protocol），一个标准的高级消息队列协议。
    
具体一点，该协议描述的内容包含哪些部分？实现 原理类似于什么？协议实现如何保证可靠性？
    
心跳的话，基于何种协议实现？

#### 2.3) ...

### 3) Redis

#### 3.1) Redis管道

如果对 `Redis` 进行批量操作的话，管道是一个很好的选择。与 `单一操作Key` 相比，其性能有了大幅的提升，主要是因为 `TCP` 连接中减少"交互往返"的时间。此时你是否会想到另外两个批量操作的命令 `mset` 以及 `mget` ，它们和 `pipeline`分别用在什么场景下呢？好奇的同学不妨一探究竟。

**场景:**

批量操作 `Redis` 中的键值，如缓存用户列表的信息

#### 3.2) Session共享

横向扩展的前提是多实例间共享会话信息，而用Reids就可以统一维护会话信息。

PHP如何将会话信息保存到Redis呢？有必要了解一下session_set_save_handler，同时一定要注意并发情况下Session读写问题。

#### 3.3) Cache-Aside

当存在缓存的时候，如何确保读取到的数据是正确的？

#### 3.4) 计数器

#### 3.5) 队列

#### 3.6) Redis Cluster

首先需要了解请求Redis Cluster的执行过程，方便问题定位。

其次需要明确哪些预想的操作不能做？比如：Redis Cluster下客户端原生不支持多key操作

#### 3.7) 随机过期

防雪崩

#### 3.8) Memcached优势

### 4) 微服务化

#### 4.1) 服务注册发现

#### 4.2) 服务治理

#### 4.3) Service Mesh

#### 4.4) 注册中心

### 5) 按需加载

#### 5.1) Composer

`Composer`已成为PHP库引入的规范，如果你写的库文件仍然不支持被自由引用，那就有点out了。不过要了解Composer时如何引入自己编写的库时需要知道 `Psr-4` 规范

### 6) 文件上传

#### 6.1) 分片上传

如何分片以及如何合并分片

#### 6.2) 流式上传

玩惯了Node.js的流式处理，感觉PHP处理文件上传过程中先让文件落地的方式实在是让人无奈，那有没有别的办法可以实现流式上传呢？主要是想减少本地磁盘IO

#### 6.3) 常见漏洞

模拟漏洞以明确哪些潜在的坑

#### 6.4) Ceph

#### 6.5) Aliyun OSS

#### 6.6) checksum

### 7) 并发编程

#### 7.1) 多线程

多线程程序只能运行在cli模式下，而且其实现与其他语言有很大的不同，多个线程之间不能通过共享内存变量的方式进行通信。究其原因，会牵涉到 [线程安全](http://www.php-internals.com/book/?p=chapt08/08-03-zend-thread-safe-in-php) 的问题上。

#### 7.2) 多进程

...

#### 7.3) 协程


#### 7.4) 守护进程

在处理消息队列中的任务时，采用守护进程的方式处理，但守护进程需要考虑的因素有很多，比如内存溢出，程序退出，优雅重启，避免窗口退出造成进程死掉

#### 7.5) 锁

常见锁有几种类型？各类锁的使用场景？

- 分布式锁
- 悲观锁
- 乐观锁

#### 7.6) 共享内存

Linux操作系统的/dev/shm是什么你知道吗？

#### 7.7) 信号量

### 8) 内存使用

如何控制内存的使用率？

- 惰性加载
- 变量引用
- 迭代器

#### 8.1) 迭代器

请简述Array, ArrayObject和ArrayIterator之间的区别?

如果有看过PDO::query的返回值类型的话，我们会发现，这个方法返回的 PDOStatement，正是对 Iterator 的实现。

#### 8.2) 引用

php使用引用是否有那么大的好处？毕竟php底层采用写时复制实现。

### 9) 排序算法

说到排序真是一把辛酸泪啊，牛逼的公司必问，而且逢考必挂。

常见的排序算法有几种？快排如何实现？堆排序解决了什么问题？能否再举几个应用的示例？

### 10) 安防

#### 10.1) IP白名单

#### 10.2) 检测敏感词

社交聊天，CMS中常用功能，但如何保证检测性能以及误报。

Bloom filter

#### 10.3) Nginx限速

#### 10.4) CDN

百度云加速

#### 10.5) 服务器杀软

云锁/安全狗

#### 10.6) WAF

Web Application Firewall

#### 10.7) 硬件防火墙

#### 10.8) Cobra

#### 10.9) TP框架漏洞

关注一下渗透测试，看看有没有需要修复的bug

对历史漏洞有个了解

#### 10.10) 防SQL注入

```php
//从360找的可以有效解决PHP安全问题的代码
function StopAttack($StrFiltValue, $ArrFiltReq) {
    if (is_array($StrFiltValue)) {
        $StrFiltValue = implode($StrFiltValue);
    }
    if (preg_match("/" . $ArrFiltReq . "/is", $StrFiltValue) == 1) {
        print "360websec notice:Illegal operation!";
        exit();
    }
}
$getfilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
foreach ($_GET as $value) {
    StopAttack($value, $getfilter);
}
$postfilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
foreach ($_POST as $value) {
    StopAttack($value, $postfilter);
}
$cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
foreach ($_COOKIE as $value) {
    StopAttack($value, $cookiefilter);
}
```

### 11) 搜索

#### 11.1) Sphinx vs Elasticsearch vs Solr

Sphinx的不足

- 配置繁琐
- 中文分词coreseek不再维护
- 相关度排序实现不太友好
- 无法做到实时更新索引

Solr是新一代的全文检索组件，它比Lucene的搜索效率高很多，还能支持HTTP的访问方式，PHP调用Solr也很方便。

#### 11.2) 纯真IP地址库

文本数据库的数据结构还是值得借鉴一下

#### 11.3) 二叉搜索树(BST)
#### 11.4) 平衡二叉树(AVL)
#### 11.5) B+Tree/B-Tree

### 12) 页面静态化

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

### 13) 分布式

#### 13.1) Zookeeper分布式过程协同

微服务实现难免需要了解Zookeeper/Etcd，但具体到实现阶段，是否要用PHP实现服务注册和服务发现那就另当别论了，一般会考虑常驻进程的语言，而非PHP。可以参考一下 Service Mesh 的实现。

同时在处理分布式事务的时候也需要依赖Zookeeper的强一致性。

### 14) Session & Cookie



### 15) 城市分站

按照城市划分子站，其中有哪些考虑要素？

最好的办法是从你们公司自己的DNS解析去设置，这是效率最高的。还可以在你们所有服务器前端搭一个反向代理，比如Nginx，它有个扩展模块好像叫geo的模块，可以从这里配置，不同地区的ip段代理到不同的分站。最差的方法就是rewrite。三种方式都可以实现。

[城市分站的功能如何实现？](https://segmentfault.com/q/1010000010568305/a-1020000010579431)

[多城市平台性网站的设计思路](https://www.zhihu.com/question/36280479)

### 16) 日志管理

参考一下 [Monolog](https://github.com/Seldaek/monolog)，但此处更重要的是观察者模式如何实现？以及日志能否异步处理或者多观察者并行处理？

### 17) 插件机制

使用钩子可以更好的做到切面编程，说到底就是面向接口的编程。常见的Wordpress中就有很多钩子，这也造就了一大批优秀的WP插件。

### 18) 视图渲染

#### 18.1) DocumentFragment

Wordpress中应用

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

### 19) 统计代码行数

```bash
find ./ -type f -name "*.php" -print0 | xargs -0 wc -l
```

### 20) apiDoc

相比于 `Swagger`，`apiDoc` 对注释的写法要求

### 21) Grammar

语法中潜在的坑

#### 21.1) ob_clean

二维码输出显示问题

#### 21.2) array_unique

[Remove duplicate items from an array](https://stackoverflow.com/questions/5036403/remove-duplicate-items-from-an-array)

#### 21.3) getallheaders

存在平台兼容性问题，同时我们也要注意自定义请求头潜在的问题。

[getallheaders无法获取自定义头的问题](http://www.01happy.com/php-getallheaders-get-user-defined-headers/)

#### 21.4) md5

PHP在处理字符串时，会利用"!="或"=="来对哈希值进行比较，它把每一个以”0E”开头的哈希值都解释为0，所以如果两个不同的密码经过哈希以后，其哈希值都是以”0E”开头的，那么PHP将会认为他们相同，都是0。

```
QNKCDZO
0e830400451993494058024219903391
  
s878926199a
0e545993274517709034328855841020
  
s155964671a
0e342768416822451524974117254469
  
s214587387a
0e848240448830537924465865611904
  
s214587387a
0e848240448830537924465865611904
  
s878926199a
0e545993274517709034328855841020
  
s1091221200a
0e940624217856561557816327384675
  
s1885207154a
0e509367213418206700842008763514
  
s1502113478a
0e861580163291561247404381396064
  
s1885207154a
0e509367213418206700842008763514
  
s1836677006a
0e481036490867661113260034900752
  
s155964671a
0e342768416822451524974117254469
  
s1184209335a
0e072485820392773389523109082030
  
s1665632922a
0e731198061491163073197128363787
  
s1502113478a
0e861580163291561247404381396064
  
s1836677006a
0e481036490867661113260034900752
  
s1091221200a
0e940624217856561557816327384675
  
s155964671a
0e342768416822451524974117254469
  
s1502113478a
0e861580163291561247404381396064
  
s155964671a
0e342768416822451524974117254469
  
s1665632922a
0e731198061491163073197128363787
  
s155964671a
0e342768416822451524974117254469
  
s1091221200a
0e940624217856561557816327384675
  
s1836677006a
0e481036490867661113260034900752
  
s1885207154a
0e509367213418206700842008763514
  
s532378020a
0e220463095855511507588041205815
  
s878926199a
0e545993274517709034328855841020
  
s1091221200a
0e940624217856561557816327384675
  
s214587387a
0e848240448830537924465865611904
  
s1502113478a
0e861580163291561247404381396064
  
s1091221200a
0e940624217856561557816327384675
  
s1665632922a
0e731198061491163073197128363787
  
s1885207154a
0e509367213418206700842008763514
  
s1836677006a
0e481036490867661113260034900752
  
s1665632922a
0e731198061491163073197128363787
  
s878926199a
0e545993274517709034328855841020
```

#### 21.5) json_encode

空字典json序列化成了[]

#### 21.6) pdo

**查询时大写的字段名称被转化成了小写**

**查询时整型的字段转化成了字符串**

需要注意用PHP查询数据库得到的数据类型与数据库中存储的未必一致。[PHP从MySQL取出int数据，变成了string](http://www.druidcoder.cn/2016/05/10/mysql-driver/)，所以在做API接口的时候，留心一下 `$mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1); `。

#### 21.7) 浮点数计算

php提供了高精度计算的函数库，实际上就是为了解决这个浮点数计算问题而生的。

#### 21.8) PDO vs mysqli vs mysql

参考: [mysqli vs PDO](http://php.net/manual/en/mysqlinfo.api.choosing.php)

PDO 提供了一个 数据访问 抽象层，这意味着，不管使用哪种数据库，都可以用相同的函数来查询和获取数据。换句话说 PDO 扩展自身并不实现任何数据库功能，只是定义了一致的接口。
这样当遇到数据库迁移的时候，实施成本无疑会低很多。

除此之外还需要知道以下两点:

**SQL预处理提高了安全性以及执行效率**

执行效率高主要体现在预处理语句上，MYSQL在执行SQL语句的时候是一条一条的通过服务器编译执行，而MYSQLI的预处理语句，则是把预处理语句缓存在服务器端，然后再通过绑定的参数进行处理，和MYSQL的重要区别是，MYSQL传递的是整条语句，而MYSQLI只传递参数，语句已经在服务器上准备好了。

安全性较高这一点，也是体现在预处理语句上，MYSQL执行语句的时候，是把传入的变量当成SQL语句的一部分，而MYSQLI有预处理语句在传递绑定参数的时候，无论变量参数是什么内容，即使变量参数本身就是一句SQL，MYSQLI也只是当做字符串来处理，这样就大大的降低了数据库SQL注入的危险。

**迭代器的实现支持读取大数据量的场景**

#### 21.9) mysql_unbuffered_query

mysql_unbuffered_query 是基于迭代器实现的吗？为什么在内存利用上会更好？

参见: [Buffered and Unbuffered queries](http://php.net/manual/en/mysqlinfo.concepts.buffering.php)

#### 21.10) spl_autoload_register

If your code has an existing __autoload() function then this function must be explicitly registered on the __autoload queue. 
 
If there must be multiple autoload functions, spl_autoload_register() allows for this. It effectively creates a queue of autoload functions, and runs through each of them in the order they are defined. By contrast, __autoload() may only be defined once.

#### 21.11) session_set_save_handler

自定义会话处理机制

### 22) 硬件选型


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
- [DRDS在浙江移动的生产应用中落地](https://yq.aliyun.com/articles/591914)
- [PHP开发异步高性能的MySQL代理服务器](http://rango.swoole.com/archives/288)
- [PDO(MySQL驱动)查询超时设置方法](https://www.mudoom.com/blog/2017/07/30/pdo%EF%BC%88mysql%E9%A9%B1%E5%8A%A8%EF%BC%89%E6%9F%A5%E8%AF%A2%E8%B6%85%E6%97%B6%E8%AE%BE%E7%BD%AE%E6%96%B9%E6%B3%95/)
