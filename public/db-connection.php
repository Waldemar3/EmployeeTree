<?php 

require_once '../config/db.php';

try {
    $pdo = new PDO("mysql:host=".$db['host'].";dbname=".$db['name'],$db['user'],$db['password']);
}catch (\PDOException $e) {
    header('HTTP/1.0 500 Database connection error');
    echo 'Database connection error';
}