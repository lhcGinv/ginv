<?php

namespace api\v1;
use api\Base;

use DB;

class Account extends Base
{
    /**
     * @param $page
     *
     * @return array
     */
    public function index($page=1, $limit = 10) {
        $account_list = DB::conn()->query('account.list',['page' => $page, 'limit' => $limit]);
        return $this->set($account_list)->response();
    }
}