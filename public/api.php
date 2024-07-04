<?php

require_once 'db-connection.php';
require_once '../vendor/autoload.php';

try{
    $employees = new \App\Employees($pdo);

    \App\Subordinates::addForeignKey($employees, 'id', 'employee_id');
    \App\Subordinates::addForeignKey($employees, 'id', 'subordinate_id');
    $subordinates = new \App\Subordinates($pdo);

    $GET = function() use($employees) {
        return !empty($_GET['id']) ? $employees->read($_GET['id']) : $employees->readAll();
    };

    $POST = function() use($employees, $subordinates) {
        $employeeMethods = [
            'update' => fn() => $employees->change($_POST, $_POST['id']),
            'delete' => fn() => $employees->remove($_POST['id']),
            'create' => fn() => $employees->create($_POST)
        ];

        $subordinateMethods = [
            'add' => fn() => $subordinates->add($_POST['id'], $_POST['name']),
            'read' => fn() => $subordinates->read($_POST['id']),
            'delete' => fn() => $subordinates->remove($_POST['id'])
        ];

        $entityMethods = [
            'employee' => $employeeMethods,
            'subordinate' => $subordinateMethods
        ];

        $entity = strtolower($_POST['entity']);
        $method = strtolower($_POST['method']);
        
        return $entityMethods[$entity][$method]();
    };

    switch($_SERVER['REQUEST_METHOD']){
        case 'GET': $request = $GET(); break;
        case 'POST': $request = $POST(); break;
    }

    /*$factory = $employees->factory(Faker\Factory::create(), 50);
    $subordinates->factory($factory[0], $factory[1]);*/

    echo $request;
}catch(\Exception $e){
    header('HTTP/1.0 400');
    echo $e->getMessage();
}
