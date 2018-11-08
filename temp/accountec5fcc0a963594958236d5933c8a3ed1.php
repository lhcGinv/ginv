<?php
function ginVAccountList ($params=[]) {
    $sql ="
    select
        *
    from
        accounts
    where
        true
        ";
    if (isset($params[':id']) && $params[':id']) {
        $sql.="
            and id = :id
        ";
    }
    if (isset($params[':limit']) && $params[':limit']) {
        $sql.="
            limit :limit
        ";
    }
    if (isset($params[':offset']) && $params[':offset']) {
        $sql.="
            offset :offset
        ";
    } 
    return $sql;
}