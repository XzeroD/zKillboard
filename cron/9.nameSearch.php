<?php

require_once '../init.php';

$minute = date('i');
if ($minute != 0) {
    exit();
}
$hour = date('H');
if ($hour == 10) {
    Db::execute('truncate zz_name_search');
}

$entities = $mdb->getCollection('information')->find();

foreach ($entities as $entity) {
    $type = $entity['type'];
    $id = $entity['id'];
    $name = @$entity['name'];
    if ($name == '') {
        continue;
    }

    $flag = '';
    switch ($type) {
        case 'warID':
            continue;
        case 'corporationID':
            $flag = @$entity['ticker'];
            break;
        case 'allianceID':
            $flag = @$entity['ticker'];
            break;
        case 'typeID':
            if ($mdb->exists('killmails', ['involved.shipTypeID' => $id])) {
                $flag = 'ship';
            }
            break;
    }
    if ($flag == null) {
        $flag = '';
    }

    $count = Db::queryField('select count(1) count from zz_name_search where type = :type and id = :id', 'count', [':type' => $type, ':id' => $id], 0);
    if ($count > 0) {
        continue;
    }
    //echo "$type $id $name $flag\n";
    Db::execute('insert ignore into zz_name_search (type, id, name, flag) values (:type, :id, :name, :flag)', [':type' => $type, ':id' => $id, ':name' => $name, ':flag' => $flag]);
}
