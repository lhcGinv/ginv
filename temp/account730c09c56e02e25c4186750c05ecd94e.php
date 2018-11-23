<?php

function ginVAccountList ($params=[]) {
    extract($params);
    $sql ="
    select *
        from accounts
    where true

    ";
    if ($account_type===0) {
        $sql.="
        and account_type = 0
    ";
    }

    
    if ($account_type) {
        $sql.="
        and account_type=:$account_type
    ";
    }

    
    if ($in_account_id) {
        $sql.="
        and account_id ";
        $sql .= " in (". assembleSqlIn("in_account_id", count($in_account_id)). ")
    ";
    }

    
    if($limit) {
        $sql.="
        limit :limit
    ";
    }

    
    if($offset) {
        $sql.="
            offset :offset
    ";
    }
    return $sql;
}


function ginVAccountCount ($params=[]) {
    extract($params);
    $sql ="
    select count(*)
        from accounts
    where
        true

    ";
    if ($account_type===0) {
        $sql.="
        and account_type = 0
    ";
    }

    
    if ($account_type) {
        $sql.="
        and account_type=:$account_type
    ";
    }

    
    if ($in_account_id) {
        $sql.="
        and account_id ";
        $sql .= " in (". assembleSqlIn("in_account_id", count($in_account_id)). ")
    ";
    }
                
    $sql .="limit 1";
                return $sql;
}


