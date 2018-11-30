# 简介
## 为什么是ginv
PHP的开源框架已琳琅满目,为什么还要有ginv? 因为ginv和我们往常使用的PHP框架不冲突,它相当于的是这些框架的model层和service层,是作为面向服务编程soa的服务,它解决的是一个复杂项目代码繁乱难以维护的问题.
所以, 通常我们用一个熟练的其他框架来调用各个ginv编写的服务,当然,ginv的各个服务之间也可以相互调用.所以,ginv里面没有专业路由,没有mvc模式.
## ginv的优点
1. 简单易用
2. 依赖低,速度快
3. 非orm的sql模板调用,更易优化和维护
4. 干净好用,ide内无警告

# 安装和配置
## 安装
ginv使用 [Composer](https://getcomposer.org/) 来管理其依赖性。所以你必须确认在你电脑上是否安装了 Composer。

通过 Composer 在命令行执行 create-project 来安装创建一个demo的服务(项目), 命令如下:

```bash
composer create-project --prefer-dist ginv/ginv demo
```
## 环境需求
* php7以上
* PHP JSON 扩展包
* 数据库尽量采用最新稳定版
* [yar](http://pecl.php.net/package/yar "yar扩展PECL安装地址")
* [SeasLog](http://pecl.php.net/package/SeasLog "SeasLog扩展PECL安装地址")

## 配置
ginv 几乎不需配置就可以马上使用。然而某些配置项是为规范和区分不同服务,所以,一般请先查看`config/app.php` 文件和几个配置文件的的注释说明.
> 注意： 你不应该在正式环境中将 app.model 配置为 debug。
> debug模式是为了在生产环境方便开发者以web方式通过test.php访问调试

## 权限
ginv 有一个目录`temp` 要让服务器有写入的权限。
## web服务器配置

ginv在web服务器配置伪静态后, 我们通过几个地址参数来进行访问的确定
1. 版本号参数, 为了实现多个版本接口共存,第一个参数确定服务的版本,即api目录下的版本目录.
2. 服务类, 版本号目录下对应的php类类名
3. 服务方法,对应方法名

例如: web访问地址:`http://localhost:8080/v1/test/index`,
版本号是v1,服务类名是test,方法名是index

rpc方法调用有所不同

我们在`config/server.php`添加一个配置

```php
'demo' => 'http://localhost:8080/v1/rpc/'
```

此时,在另外一个服务可以通过`rpc('demo','test')->call('index')`调用

### Nginx
在 Nginx，在你的网站配置中增加下面的配置：
```bash
location / { 
    if (!-e \$request_filename) {
        rewrite ^/([^/]*)/([^/]*)/([^/]*)$ /index.php?ginV_version=\$1&ginV_api=\$2&ginV_method=\$3 last;
        break;
    }
}
```

# 函数
ginv的助手函数非常少
## config
config函数用户获取一个配置:
```php
// 获取config/app.php的key为name的配置
config('app.name') 
```

## db
db 函数启用数据库连接并实例化db类
```php
$db     = db();
        $count  = $db->count('account.count', ['account_uuid' => $account_uuid]);
```

## redis_key
redis_key 函数用于读取config/redis_key.php的配置,若有第二个参数,根据第二个参数进行格式化
```php
// config/redis_key.php中
//    'account_info' => 'demo:account_info:%d'
// 下面的$key的值为demo:account_info:1
$id = 1;
$key = redis_key('account_info',$id);
```
## redis
redis函数启用redis连接并实例化redis类
```php
$id = 1;
$key = redis_key('account_info',$id);
$account_info = redis()->do('get',$key);
```

## base_path
base_path 函数获取相对于项目根目录的完整路径
```php
// 项目的temp目录
base_path('temp')
```

## dd
dd 函数用于打印并中断程序执行
```php
// dump and die;
dd('111');
```

# 数据查询

## 调用另外一个服务的数据
$this->rpc(服务名,服务类)->call(服务函数[,函数参数1[, 函数参数2...]]);
> 服务名在配置文件`config/service.php`配置

## 查询数据库数据
数据库查询最常用的函数如下:

| 函数      |      说明      |
|----------|:-------------|
| query    |  查询多条记录   |
| queryRow |  查询单条记录   |
| exec     | 执行一条 SQL 语句，并返回受影响的行数 |
| count    | count查询快捷返回函数 |

其他数据库的函数:
| 函数      |      说明      |
|----------|:-------------|
| lastInsertId    |  插入查询后,获取最后插入的id   |
| begin |  启动事务   |
| commit     | 提交事务 |
| rollBack    | 回滚事务 |
| sql    | 获取sql语句 |


## ginv的一个简单例子
```php
<?php

namespace api\v1;
use api\Base;

class Blog extends Base
{
    /**
     * 分页获取博客列表
     * @param string $account_uuid 用户uuid
     * @param int    $page 当前页
     * @param int    $limit 每页数量
     *
     * @return array
     */
    public function blogList($account_uuid = '',$page = 1, $limit = 10) {
        $offset = ($page-1) * $limit;
        $db = db();
        $params = [
            'account_uuid' => $account_uuid,
            'limit' => $limit,
            'offset' => $offset
        ];
        $count = $db->count('account.count',$params);
        $list = $db->query('account.list',$params);
        $account_uuid_array = array_column($list,'account_uuid');
        // 获取用户的用户名
        $account_array = $this->rpc('demo_account','account')->call('accountList',$account_uuid_array);
        foreach ($list as &$item) {
            foreach ($account_array as $account) {
                if ($item['account_uuid'] == $account['account_uuid']) {
                    $item['account_name'] = $account['account_name'];
                }
            }
        }
        $result = compact('count', 'list');
        return $this->set($result)->response();
    }
}
```

template目录下blog.blade.php文件中sql模板如下:
```
@section("blog.list")
    select
        *
    from
        blog
    where
        true
        @if($account_uuid)
            and account_uuid = :account_uuid
        @endif
        @if($limit)
            limit :limit
        @endif
        @if($offset)
            offset :offset
        @endif
@endsection


@section("blog.count")
    select
        count(*)
    from
        blog
    where
        true
    @if($account_uuid)
        and account_uuid = :account_uuid
    @endif
    limit 1
@endsection
```
