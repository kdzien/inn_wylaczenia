<?php
require "vendor/autoload.php";
require_once __DIR__ . '/controllers/turns.php';
ini_set('max_execution_time', 0);
header('Access-Control-Allow-Origin: *');

$app = new Slim\App();


$app->post('/addfile/{product}', function ($request, $response, $args) {
    $productValue = $args['product'];
    if ( 0 < $_FILES['file']['error'] ) {
        return $response->withJson([
            'error'=>'Error: ' . $_FILES['file']['error'] . '<br>',
            'code'=>404
        ], 400);
    }
    else {
        move_uploaded_file($_FILES['file']['tmp_name'], "uploads/".$productValue.".xls");
        $result = Controllers\Turns\insertFromExcel($productValue);
        if(strpos($result, 'ERROR') !== false){
            return $response->withJson([
                'error'=>$result,
                'code'=>404
            ], 400);
        }else{
            $results = Controllers\Turns\getCurrentFromDB($productValue);
            if(strpos($results, 'ERROR') !== false ){
                return $response->withJson([
                    'error'=>$results,
                    'code'=>404
                ], 400);
            }else{
                return $response->withJson([
                    'body'=>$results,
                ], 200);
            }
        }
    }
});
$app->get('/automatyzacja', function ($request, $response, $args) {
    $result = Controllers\Turns\runProcedure('CALL konradd.automatyzacja_polmo()');
    if(strpos($result, 'ERROR') !== false ){
        return $response->withJson([
            'error'=>$result,
            'code'=>404
        ], 400);
    }else{
        return $response->withJson([
            'body'=>$result,
        ], 200);
    }
});

$app->get('/procedura/{product}', function ($request, $response, $args) {
    $productValue = $args['product'];
    $procedure_query='';
    if($productValue=='Polmo'){
        $procedure_query='CALL konradd.clear_kpl_polmo_wyl()';
    }else if($productValue=='Heko'){
        $procedure_query='CALL mateuszp.automatyzacja_heko()';
    }else if($productValue=='Turbiny'){
        $procedure_query='CALL mateuszp.automatyzacja_turbiny()';
    };;
    $result = Controllers\Turns\runProcedure($procedure_query);
    if(strpos($result, 'ERROR') !== false ){
        return $response->withJson([
            'error'=>$result,
            'code'=>404
        ], 400);
    }else{
        return $response->withJson([
            'body'=>$result,
        ], 200);
    }
});

$app->get('/removeOns', function ($request, $response, $args) {
    $result = Controllers\Turns\removeOns();
    if(strpos($result, 'ERROR') !== false ){
        return $response->withJson([
            'error'=>$result,
            'code'=>404
        ], 400);
    }else{
        return $response->withJson([
            'body'=>$result,
        ], 200);
    }
});
$app->get('/updateOns', function ($request, $response, $args) {
    $result = Controllers\Turns\updateOns();
    if(strpos($result, 'ERROR') !== false ){
        return $response->withJson([
            'error'=>$result,
            'code'=>404
        ], 400);
    }else{
        return $response->withJson([
            'body'=>$result,
        ], 200);
    }
});
$app->run();
