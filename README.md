## 1.0 编写目标

搞PHP也5年了，算是有一些大型项目的开发经验，做个分享希望可以帮助到一些有点迷茫的同学，同时接受各方的批评和指点。

## 2.0 奇淫巧技

### 2.1 **非阻塞请求**

- **简介:**

    是不是你已厌烦了PHP的同步阻塞IO，那就来看一下利用PHP如何实现原生的非阻塞吧，保证让你有一种恍然大悟的赶脚。

- **场景:**

    消息推送但不考虑结果状态，如Webhook

- **代码:**

    > framework/src/Request.class.php

### 2.2 **Promises/A+**

- **简介:**

    `Promise`作为异步编程的解决方案，现在已经在Js代码中烂大街了。其实PHP中也有类似的实现，尤其是 `all` 方法值得PHP程序员学习借鉴，我们可以以此来达到并发请求的目标，同时也确保了写法的优雅程度。当然我们需要知道的是，PHP中 `Promise` 的实现也是同步，只是写法看似是异步而已。

- **场景:**

    并发请求接口，如批量发送邮件，爬虫

- **代码:**

    > GuzzleHttp/Promise

### 2.3 **Redis管道**

- **简介:**

    如果对 `Redis` 进行批量操作的话，管道是一个很好的选择。与 `单一操作Key` 相比，其性能有了大幅的提升，主要是因为 `TCP` 连接中减少"交互往返"的时间。此时你是否会想到另外两个批量操作的命令 `mset` 以及 `mget` ，那与 `pipeline` 相比哪个性能更高呢，好奇的同学不妨一探究竟。

- **场景:**

    批量操作 `Redis` 中的键值，如缓存用户列表的信息

- **代码:**

    > 

### 2.4 **AMQP**

- **简介:**

    `AMQP`，即 `Advanced Message Queuing Protocol`，一个提供统一消息服务的应用层标准高级消息队列协议，是应用层协议的一个开放标准，为面向消息的中间件设计。而消息队列有两大牛逼优势：解耦和异步。所以基于该协议我们可以更加优雅地编写代码。

- **场景:**

    异步任务执行，降低功能模块之间的耦合

- **代码:**

    > 

### 2.5 **MySQL BatchInsert**



### 2.6 **Deamon Process**

在处理消息队列中的任务时，采用守护进程的方式处理，但守护进程需要考虑的因素有很多，比如内存溢出，程序退出，优雅重启，避免窗口退出造成进程死掉

### 2.7 **PThread**

多线程程序只能运行在cli模式下，而且其实现与其他语言有很大的不同，多个线程之间不能通过共享内存变量的方式进行通信，因为多线程之间被互相隔离了

### 2.8 **Memory**

如何控制内存的使用率，变量引用，迭代器，GC，重启，限制单个进程的内存使用

### 2.9 **Hooks**

使用钩子可以更好的做到切面编程

### 2.10 **Logger**

Mongologger

### 2.11 **HeapSort**

TopK

### 2.12 **PHP自动加载规范Psr-4**



### 2.13 **Lock**



### 2.14 **phptrace**



### 2.15 **Cache-Aside**
### 2.16 **Zookeeper**
### 2.17 **Static Variables**
### 2.18 **MySQL Pools**
