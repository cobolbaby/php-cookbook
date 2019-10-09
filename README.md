[TOC]

# 目录

## 编写目标

搞PHP也5年了，算是有一些中型项目的开发经验，做个分享希望可以帮助到一些有点迷茫的同学，同时接受各方的批评和指点。

## 每日一题

试试手，看看自己能hold住吗？

- [TiNODE](https://t.ti-node.com/question)
- [四角猫](https://www.sijiaomao.com/exams)

## 基础须知

需要了解5.x版本每次升级主要新增了哪些特性，有利于我们写出更优雅的代码。

### 1) 运行环境

#### 1.1) 版本差异

PHP5.6升级PHP7的负担有哪些？

- [日请求亿级的QQ会员AMS平台PHP7升级实践](
https://www.webfalse.com/read/201768/11898426.html)

除了版本号之外, 还应该关注一下是否是**线程安全**

- [What does thread safety mean when downloading PHP?](http://php.net/manual/en/faq.obtaining.php#faq.obtaining.threadsafety)
- [What is thread safe or non-thread safe in PHP?](https://stackoverflow.com/questions/1623914/what-is-thread-safe-or-non-thread-safe-in-php)

#### 1.2) 执行环境

- Apache + mod_php
- Nginx + PHP-FPM
- OpenResty + PHP-FPM

#### 1.3) 硬件选型

重IO/内存

### 2) 常见函数的坑

#### 2.1) ob_clean

phpqrcode在输出二维码可能出现显示问题，此时可以用ob_clean修正

#### 2.2) array_unique

一定要留意`SORT_REGULAR`参数

[Remove duplicate items from an array](https://stackoverflow.com/questions/5036403/remove-duplicate-items-from-an-array)

#### 2.3) getallheaders

存在平台兼容性问题，同时我们也要注意自定义请求头潜在的问题。

[getallheaders无法获取自定义头的问题](http://www.01happy.com/php-getallheaders-get-user-defined-headers/)

#### 2.4) md5

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

#### 2.5) json_encode

空字典json序列化成了[]

#### 2.6) mysqli query

**查询时大写的字段名称被转化成了小写**

**查询时整型的字段转化成了字符串**

需要注意用PHP查询数据库得到的数据类型与数据库中存储的未必一致。[PHP从MySQL取出int数据，变成了string](http://www.druidcoder.cn/2016/05/10/mysql-driver/)，所以在编写API接口的时候，一定要添加如下设置:

```
$mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1); 
```

#### 2.7) floating point precision

[Float 浮点型](http://php.net/manual/zh/language.types.float.php)

php提供了高精度计算的函数库，实际上就是为了解决这个浮点数计算问题而生的。

#### 2.8) prepared statements

SQL预处理能力提高了安全性以及执行效率。

执行效率高主要体现在预处理语句上，和常规编写的SQL最重要区别是，常规情况下每次都需要传递整条语句，且都会解析，而预处理只解析一次，后续客户端只传递参数就好，减少了多次解析SQL的损耗，所以执行效率更高。

安全性这一点，也是体现在预处理语句上，预处理语句在传递绑定参数的时候，无论变量参数是什么内容，即使变量参数本身就是一句SQL，也只是当做字符串来处理，这样就大大的降低了数据库SQL注入的危险。

#### 2.9) mysql_unbuffered_query

mysql_unbuffered_query 是基于迭代器实现的吗？为什么在内存利用上会更好？

参见: [Buffered and Unbuffered queries](http://php.net/manual/en/mysqlinfo.concepts.buffering.php)

#### 2.10) Trait

自 PHP 5.4.0 起，PHP 实现了一种代码复用的方法，称为 trait。

Trait 是为类似 PHP 的单继承语言而准备的一种代码复用机制。Trait 为了减少单继承语言的限制，使开发人员能够自由地在不同的类中复用 method。

#### 2.11) session_set_save_handler

自定义会话处理机制

#### 2.12) array_map

一句话删除文件目录

```php
array_map('unlink', glob('*'));
```

但上述方式未必最好，该方法还是串行执行

怎么并行起来呢？

#### 2.13) urlencode

如果参数中带有"+"，会有什么影响？GET方式传参一定要注意。

“+”号是一个提交的变量分隔符，php会自动把他用空格替换了，所以一定要提交前进行转义。

#### 2.14) stream_copy_to_stream

[How to Read Big Files with PHP (Without Killing Your Server)](https://www.sitepoint.com/performant-reading-big-files-php/)

流式处理的典范

#### 2.15) php://temp

生成临时文件有两种办法 php://temp 与 tmpfile，你更爱哪一种？ 

推荐前者，如果是小文件，则会临时存储在内存中。

[Generate CSV from Array](https://css-tricks.com/snippets/php/generate-csv-from-array/)

#### 2.16) nowdoc/heredoc

[php针对多行文本的语法糖](http://php.net/manual/zh/language.types.string.php#language.types.string.syntax.nowdoc) 做wordpress开发就会感到很有用了。

#### 2.17) iconv

php iconv() 编码转换出错 `Detected an illegal character`，问题的关键在于每一种编码也是有可表示范围的。

参考: [php iconv() : Detected an illegal character in input string](https://www.jb51.net/article/25528.htm)

#### 2.18) filesize

[filesize 超过2G文件信息错误处理](https://chenxuehu.com/article/2016/08/5540.html) 没啥实际业务，仅作了解

#### 2.19) phpredis 和 predis

[phpredis 和 predis 使用区别](https://learnku.com/articles/7259/phpredis-and-predis)

#### 2.20) in_array

关注一下字段类型转化可能引起的异常, 比如:

```php
$needle = '1abc';
$haystack = array(1,2,3);
var_dump(in_array($needle, $haystack); //输出为 true 
```

#### 2.21) clone

涉及到深浅拷贝的问题, 参考: [对象的复制(拷贝)与__clone()方法](https://www.cnblogs.com/tyrus/p/php_object_clone.html)

#### 2.22) Try Catch

[PHP异常处理](https://www.php.net/manual/zh/language.exceptions.php#119726)

### 3) SPL

#### 3.1) ArrayIterator

相比于Array，好处在于不需要生成一个数组。

#### 3.2) SplFixedArray

[定长数组](https://wiki.swoole.com/wiki/page/634.html)

[SplFixedArray效能测试](https://ithelp.ithome.com.tw/articles/10194418)

#### 3.3) SplHeap

[堆](https://wiki.swoole.com/wiki/page/879.html)

#### 3.4) SplQueue

[队列](https://wiki.swoole.com/wiki/page/507.html)

#### 3.5) spl_autoload_register

If your code has an existing __autoload() function then this function must be explicitly registered on the __autoload queue. 
 
If there must be multiple autoload functions, spl_autoload_register() allows for this. It effectively creates a queue of autoload functions, and runs through each of them in the order they are defined. By contrast, __autoload() may only be defined once.

#### 3.6) SplFileObject

PHP读取和解析大文件

### 4) Composer

[Composer](https://getcomposer.org/)除了解决了包管理的问题，也让惰性加载更快更好的落地。不过要了解Composer是如何引入自己编写的库时，需要知道 `Psr-4` 规范。

#### 4.1) 基础指令

- Install Depandence

```
$ composer install
Cannot create cache directory ~/.composer/cache/repo/https---packagist.org/, or directory is not writable. Proceeding without cache
Cannot create cache directory ~/.composer/cache/files/, or directory is not writable. Proceeding without cache
```

提示这个目录没有可写权限，composer无法缓存下载的包，这样就每次都得重新下载，把目录改成可写可读即可。

```
sudo chmod -R 777 ~/.composer
```

- Remove Depandence

```
$ composer remove tixastronauta/acc-ip
Loading composer repositories with package information
Updating dependencies (including require-dev)
Package operations: 0 installs, 0 updates, 1 removal
  - Removing tixastronauta/acc-ip (1.0.0)
Writing lock file
Generating autoload files
```

- 优化自动加载

```
composer dump-autoload --optimize
```

- 切换国内源

```
composer config -g repo.packagist composer https://packagist.phpcomposer.com
```

### 5) Debug

#### 5.1) Xdebug

#### 5.2) strace跟踪系统调用

```bash
strace -t -e trace=open -o output.log php index.php
# -t 显示调用时间
# -e trace=file 过滤显示
```
## 周边技术

内含各种奇淫巧技 :)

### 1) MySQL

建议看一下: [燕十八MySQL优化视频教程](http://www.php.cn/course/200.html)

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

[计算数据库中各个表的数据量和每行记录所占用空间](http://www.cnblogs.com/yzwdli/p/5337881.html)

[谈数据库容量和性能测算](http://itindex.net/detail/39281-%E6%95%B0%E6%8D%AE%E5%BA%93-%E5%AE%B9%E9%87%8F-%E6%80%A7%E8%83%BD)

#### 1.3) 连接池

原生PHP没办法实现，考虑一下周边的解决方案，比如采用数据库中间件去实现连接池的功能。

#### 1.4) 长连接

仅在PHP-FPM管控下可实现长连接，Apache的话就别想了。不过一定要想好什么时候释放连接资源，别到时候出现`too many clients`的问题。

#### 1.5) 读写分离

利用框架级别实现还是用中间件实现？各有什么优缺点？

除了业务读写分离，最重要的反倒是如何保障主从之间的同步

- 半同步复制
- GTID

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

[如何理解MySQL的表分区？](http://blog.sijiaomao.com/?p=1592)

#### 1.8) 严格模式

有些集成的PHP运行环境(WAMP/XAMPP)自带的MySQL貌似都没有开启MySQL的严格模式。那何为MySQL的严格模式，简单来说就是MySQL自身对数据进行严格的校验（格式、长度、类型等），比如一个整型字段我们写入一个字符串类型的数据，在非严格模式下MySQL不会报错，同样如果定义了char或varchar类型的字段，当写入或更新的数据超过了定义的长度也不会报错。MySQL开启了严格模式从一定程序上来讲是对我们代码的一种测试，如果我们的开发环境没有开启严格模式在，开发过程中也没有遇到错误，那么在上线或代码移植的时候将有可能出现不兼容的情况，因此在开发过程做最好开启MySQL的严格模式。

**如何开启**

1. 可以通过执行SQL语句来开启

    但是只对当前连接有效，下面是SQL语句：
`set sql_mode="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION";`

2. 通过修改MySQL的配置文件

    在`[mysqld]`下查找`sql-mode`，将此行修改成为: 
`sql-mode="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"`，推荐第二种方法，可以一劳永逸。

#### 1.9) 编码字符集

字符集的选择直接影响着SQL查询时大小写是否敏感的问题。

当我们输入不管大小写都能查询到数据，例如：输入aaa或者aaA ,AAA都能查询同样的结果，说明查询条件对大小写不敏感。

于是怀疑Mysql的问题。做个实验：直接使用客户端用sql查询数据库。发现的确是大小不敏感 。

通过查询资料发现需要设置collate（校对）。 collate规则：

- `*_bin`: 表示的是binary sensitive collation，也就是说是区分大小写的
- `*_cs`: sensitive collation，区分大小写
- `*_ci`: insensitive collation，不区分大小写

解决方法：

1. 可以将查询条件用binary()括起来。 比如：  
`select * from TableA where binary columnA ='aaa';`
2. 可以修改该字段的collation为binary
比如：
`ALTER TABLE TABLENAME MODIFY COLUMN COLUMNNAME VARCHAR(50) BINARY CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;`

除了上面提到的关于编码还需要留意一个配置`skip-character-set-client-handshake`，启用选项后，可以避免客户端程序误操作（使用其他字符集连接进来并写入数据，从而引发乱码的问题）

#### 1.10) 字段类型

ip/time/enum/money/汉字

如何存储`IP`，`Money`以及`DATETIME`类型数据

[VARCHAR(50)最多能存多少个汉字](https://zhidao.baidu.com/question/1368150009483131619.html)

[枚举类型ENUM](https://dev.mysql.com/doc/refman/5.6/en/enum.html) 是非常快和紧凑的。实际上，其保存为TINYINT，只是其表面上显示为字符串。看起来用这个字段来做一些选项列表变得相当的完美。 但有哪些潜在的坑呢

弱类型语言在执行SQL插入的时候枚举值没有引号可能有问题，尤其对于新手；并且对于0/''/NULL等值要特别留意，不然会出现意外。

#### 1.11) 事务隔离级别

明确事务的隔离级别，以及什么原因会导致死锁，如何复现? 如何避免？

#### 1.12) 存储过程

其实我们可以把存储过程看做是业务处理下沉的一个体现。在数据库层级实现了部分的业务服务。

#### 1.13) 视图

视图的价值
- 面向不同角色用户，访问不同的视图，方便做权限管理，对数据安全性要求很高的公司必须要考虑的 
- 简化上层业务逻辑，底层表结构对上层近乎透明，DBA做表结构调整时对上层业务代码没有影响，尤其是当一套数据被多个业务子系统共享时，此问题尤为明显。

不过以上情况更通常采用服务化的路子解决。

#### 1.14) 

#### 1.15) Online DDL

#### 1.16) 小表驱动大表

适用于以下查询关键词: `in`, `exists`, `join`

- in/exists
    - 小表驱动大表

- join优化
    - 小表驱动大表
    - 被驱动集合建索引
    - innodb_buffer_pool_size

- not in

    not in 不会用到索引，那咋办？可以考虑用 `union`/`not exists`/`left join` 替代

#### 1.17) 分组聚合

分组聚合的本质就是先排序后聚合，所以一定要利用好索引的左前缀原则，避免生成不必要的临时表。

#### 1.18) 范围查询

`between`，` <`，`>` 的优化主要是利用B+Tree的特性。

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

#### 1.23) 自增主键

分布式或分部分表之后，自增主键有没有必要？如果有必要又该如何维护该信息？

#### 1.24) 预编译

[MySQL · 特性分析 · MySQL的预编译功能](http://mysql.taobao.org/monthly/2018/04/07/)

#### 1.25) 重建索引

[Rebuilding or Repairing Tables or Indexes](https://dev.mysql.com/doc/refman/5.6/en/rebuilding-tables.html)

#### 1.26) 关联表更新

UPDATE SET FROM 将某表的一列替换为其他表的某一列

#### 1.27) 临时表

[MySQL · 特性分析 · 内部临时表](http://mysql.taobao.org/monthly/2016/06/07/)

外部临时表存储在内存还是磁盘？哪些操作会产生内部临时表？而如何内存临时表的数据存储在磁盘的话，磁盘IO可想而知。

子查询/ORDER BY/GROUP BY/DISTINCT/UNION 以上操作可能会创建。。。

#### 1.28) 

#### 1.29) 缓解主从复制延迟的方法

1. 高速网络
2. 从库SSD
3. 降低从库负载，使用多个从库
4. 严格要求一致性的，不要查从库

#### 1.30) 索引长度与区分度

column列也可以增设索引长度，column(index_length)列索引长度

```sql
ALTER TABLE index7 ADD INDEX index7_name(name(20));
```

区分度计算:

```sql
select count(distinct left(word,6))/count(id) from tb_name where 1; 
```

目标是用最小的空间尽可能提高索引的效率。

#### 1.31) 当字段名与MySQL保留字冲突的解决办法

加上反引号 ` , 不过最好是在建表的时候就注意到这一点。

#### 1.32) 查找my.cnf配置文件

如果当您遇到修改MySQL配置之后没有生效，且伴有灵异情况的时候, 查看一下my.cnf配置文件加载的对不对吧. 下面的指令会告诉你你可能会碰到的坑:

```
/opt/lampp/bin/mysql --help | grep my.cnf
```

类似的问题可以想一下php.ini文件加载顺序造成的问题, 查看:

```
/opt/lampp/bin/php -r "phpinfo();" | grep "php.ini"
```

#### 1.33) 配置优化

- [优化 Azure Linux VM 上的 MySQL 性能](https://docs.azure.cn/zh-cn/virtual-machines/linux/classic/optimize-mysql)

### 2) 异步处理

是不是你已厌烦了PHP的同步阻塞IO，那就来看一下利用PHP如何实现原生的非阻塞吧，保证让你有一种恍然大悟的赶脚。

#### 2.1) Promises/A+

`Promise`作为异步编程的解决方案，现在已经在Js代码中烂大街了。其实PHP中也有类似的实现，尤其是 `all` 方法值得PHP程序员学习借鉴，我们可以以此来达到并发请求的目标，同时也确保了写法的优雅程度。当然我们需要知道的是，PHP中 `Promise` 的实现也是同步，只是写法看似是异步而已。

#### 2.2) MQ

AMQP（Advanced Message Queuing Protocol），一个标准的高级消息队列协议。
    
具体一点，该协议描述的内容包含哪些部分？实现 原理类似于什么？协议实现如何保证可靠性？
    
心跳的话，基于何种协议实现？

#### 2.3) Swoole

### 3) 认证鉴权

#### 3.1) Session
#### 3.2) JWT
#### 3.3) OAuth2
#### 3.4) 浏览器指纹

- [Technical analysis of client identification mechanisms](http://www.chromium.org/Home/chromium-security/client-identification-mechanisms)
- [跨浏览器指纹追踪技术：毫无障碍的查看你的浏览记录](https://www.4hou.com/info/news/3380.html)
- [Fingerprint.js](https://fingerprintjs.com/)


### 4) 微服务化

#### 4.1) 服务注册发现

#### 4.2) 服务治理

#### 4.3) Service Mesh

#### 4.4) Zookeeper分布式过程协同

微服务实现难免需要了解Zookeeper/Etcd，但具体到实现阶段，是否要用PHP实现服务注册和服务发现那就另当别论了，一般会考虑常驻进程的语言，而非PHP。可以参考一下 Service Mesh 的实现。

同时在处理分布式事务的时候也需要依赖Zookeeper的强一致性。可参考:

- [大白话聊聊分布式事务](https://www.bo56.com/%E5%A4%A7%E7%99%BD%E8%AF%9D%E8%81%8A%E8%81%8A%E5%88%86%E5%B8%83%E5%BC%8F%E4%BA%8B%E5%8A%A1/)
- [传统事务与柔性事务](https://www.kancloud.cn/xiak/php-node/677625)

### 5) 代码注释

相比于 `Swagger`，`apiDoc` 对注释的写法要求

### 6) 文件上传

获取上传文件大小，如果超过2G的话怎么办?

#### 6.1) 分片上传

如何分片以及如何合并分片

#### 6.2) 流式上传

玩惯了Node.js的流式处理，感觉PHP处理文件上传过程中先让文件落地的方式实在是让人无奈，那有没有别的办法可以实现流式上传呢？主要是想减少本地磁盘IO

- [Web API FileReader](https://developer.mozilla.org/zh-CN/docs/Web/API/FileReader)
- [PHP流式上传和表单上传](https://www.cnblogs.com/52php/p/5675325.html) 走octet-stream上传方式

#### 6.3) 常见漏洞

参考: [文件上传漏洞（绕过姿势）](https://thief.one/2016/09/22/%E4%B8%8A%E4%BC%A0%E6%9C%A8%E9%A9%AC%E5%A7%BF%E5%8A%BF%E6%B1%87%E6%80%BB-%E6%AC%A2%E8%BF%8E%E8%A1%A5%E5%85%85/)

#### 6.4) checksum

### 7) 并发编程

#### 7.1) 多线程

多线程程序只能运行在cli模式下，而且其实现与其他语言有很大的不同，多个线程之间不能通过共享内存变量的方式进行通信。究其原因，会牵涉到 [线程安全](http://www.php-internals.com/book/?p=chapt08/08-03-zend-thread-safe-in-php) 的问题上。

#### 7.2) 多进程

...

#### 7.3) 文件锁

#### 7.4) 信号量

共享内存

#### 7.5) 分布式锁

#### 7.6) sync.Mutex

### 8) 内存优化

如何控制内存的使用率？

- 惰性加载
- 变量引用
- 迭代器

#### 8.1) 迭代器

请简述Array, ArrayObject和ArrayIterator之间的区别?

如果有看过PDO::query的返回值类型的话，我们会发现，这个方法返回的 PDOStatement，正是对 Iterator 的实现。

#### 8.2) 对象引用

php使用引用是否有那么大的好处？毕竟php底层采用写时复制实现。

#### 8.3) 分片处理

对buffer高效利用的体现

#### 8.4) 命名管道


### 9) 排序算法

说到排序真是一把辛酸泪啊，牛逼的公司必问，而且逢考必挂。

常见的排序算法有几种？快排如何实现？堆排序解决了什么问题？能否再举几个应用的示例？

- [常见排序算法性能比较](https://www.lbog.cn/blog/39)

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

#### 10.9) PHP框架漏洞

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

#### 10.11) 服务器文件目录权限

去掉所有PHP文件可执行属性

```
find ./ -type f  -name "*.php" | xargs chmod -x
```

#### 10.12) .htaccess

### 11) 搜索

#### 11.1) Sphinx vs Elasticsearch vs Solr

Sphinx的不足

- 配置繁琐
- 中文分词coreseek不再维护
- 相关度排序实现不太友好
- 无法做到实时更新索引

Solr是新一代的全文检索组件，它比Lucene的搜索效率高很多，还能支持HTTP的访问方式，PHP调用Solr也很方便。

#### 11.2) 纯真IP地址库

纯真IP官网：http://www.cz88.net/

首先需要**将纯真IP库转存为CSV，方便入库**

Windows环境下安装最新的版本，并解压为ip.txt文件。使用vi打开ip.txt文件，执行3次以下命令

```
:%s/\s\+/;/
```

详解：ip.txt有4列。分别是起始ip，结束ip，地区，说明。列之间用不等数量的空格间隔。为了将此文本文件到入到mysql，需要处理掉这些空格。但是只能处理掉前3列的空格，最后一列中的空格要保留。vi中输入的命令意思是，把每一行第一个连续的空格替换成字符';'。%s代表全局搜索替换。\s代表空格。\+代表尽可能多地匹配前面的字符。;代表替换成';' 

使用Excel打开处理后的文件，并指定';'为分隔符

#### 11.3) 二叉搜索树(BST)
#### 11.4) 平衡二叉树(AVL)
#### 11.5) B+Tree/B-Tree

多叉树

#### 11.6) 广度优先(BFS)

常用于以下业务场景中:

- 网络爬虫
- 搜索指定格式的文件
- 查找两点间最短路径
- 查看两点间是否存在联系

### 12) 视图渲染

#### 12.1) DocumentFragment

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

#### 12.2) 页面静态化

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

### 13) 多站点管理

按照城市划分子站，其中有哪些考虑要素？

最好的办法是从你们公司自己的DNS解析去设置，这是效率最高的。还可以在你们所有服务器前端搭一个反向代理，比如Nginx，它有个扩展模块好像叫geo的模块，可以从这里配置，不同地区的ip段代理到不同的分站。最差的方法就是rewrite。三种方式都可以实现。

[城市分站的功能如何实现？](https://segmentfault.com/q/1010000010568305/a-1020000010579431)

[多城市平台性网站的设计思路](https://www.zhihu.com/question/36280479)

### 14) 日志管理

参考一下 [Monolog](https://github.com/Seldaek/monolog)，但此处更重要的是观察者模式如何实现？以及日志能否异步处理或者多观察者并行处理？

### 15) 插件机制

使用钩子可以更好的做到切面编程，说到底就是面向接口的编程。常见的Wordpress中就内置了很多钩子，可以看一下 [WordPress Actions and Filters](https://code.tutsplus.com/articles/the-beginners-guide-to-wordpress-actions-and-filters--wp-27373)

## 参考

- [PHP标准规范](https://psr.phphub.org/)
- [PHP之道](http://laravel-china.github.io/php-the-right-way/)
- [PHP非阻塞实现方法](https://www.awaimai.com/660.html)
- [MySQL批量修改](https://www.awaimai.com/2103.html)
- [在PHP中使用协程实现多任务调度](http://www.laruence.com/2015/05/28/3038.html)
- [PDO查询超时设置方法](https://www.mudoom.com/blog/2017/07/30/pdo%EF%BC%88mysql%E9%A9%B1%E5%8A%A8%EF%BC%89%E6%9F%A5%E8%AF%A2%E8%B6%85%E6%97%B6%E8%AE%BE%E7%BD%AE%E6%96%B9%E6%B3%95/)
- [Choosing a MySQL PHP drivers](http://php.net/manual/en/mysqlinfo.api.choosing.php)
- [我必须得告诉大家的MySQL优化原理](https://www.jianshu.com/p/d7665192aaaf)
