<?php
function fetchAllAssoc($db, $sql, $params = [])
{
    $st = $db->prepare($sql);
    foreach ($params as $k => $v) $st->bindValue(is_int($k) ? $k + 1 : $k, $v);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
