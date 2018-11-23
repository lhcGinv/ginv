<?php

namespace api\v1;
use api\base;

class demo extends base
{

    /**
     * 一个简单的例子
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    public function index(int $page=1, int $limit = 10) {
        $offset = ($page-1) * $limit;
        $db = db();
        $params = ['in_account_id' => ['1','18','24']];
        $count =  $db->count('account.count', $params);
        $list = $db->select('account_id','account_uuid', 'account_name')->query('account.list', ['limit' => 10, 'offset' => $offset]);
        return $this->set(compact('list', 'count'))->response();
    }
}