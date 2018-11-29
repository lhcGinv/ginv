# 说明
`ginv`是一个简单干净的php微服务框架.

# 安装
创建一个`demo`的服务(项目), 命令如下:
```bash
composer create-project --prefer-dist ginv/ginv demo
```
# 版本要求
* php7以上

# 依赖
* [yar](http://pecl.php.net/package/yar "yar扩展PECL安装地址")
* [SeasLog](http://pecl.php.net/package/SeasLog "SeasLog扩展PECL安装地址")

# 数据查询

## 调用另外一个服务
$this->rpc(服务名,服务类)->call(服务函数[,函数参数1[, 函数参数2...]]);

## 查询数据库数据
数据库查询有用的函数如下:

| 函数      |      说明      |
|----------|:-------------|
| query    |  查询多条记录   |
| queryRow |  查询单条记录   |
| exec     | 执行一条 SQL 语句，并返回受影响的行数 |
| count    | count查询快捷返回函数 |

## 一个简单的例子
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
        $db     = db();
        $count  = $db->count('account.count', ['account_uuid' => $account_uuid]);
        $list   = $db->select('id','title', 'description', 'account_uuid')
                     ->query('account.list', [
                        'account_uuid' => $account_uuid,
                        'limit' => $limit,
                        'offset' => $offset
                     ]);
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
