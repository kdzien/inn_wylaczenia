<?php
header('Access-Control-Allow-Origin: *');
require "vendor/autoload.php";
require_once __DIR__ . '/controllers/TurnsControllers.php';
ini_set('max_execution_time', 0);

$app = new Slim\App();

$app->post('/addfile/{product}', function ($request, $response, $args) {
    $productValue = $args['product'];
    if (!isset($_FILES['file']['name'])){
        return $response->withJson([
            'error'=>'Error: Nie wybrales pliku',
            'code'=>404
        ], 400);
    }
    else if (!preg_match('/^.*\.(xls)$/',$_FILES['file']['name'])){
        return $response->withJson([
            'error'=>'Error: Plik nie jest plikiem xls!',
            'code'=>404
        ], 400);
    }
    else {
        move_uploaded_file($_FILES['file']['tmp_name'], "uploads/".$productValue.".xls");
        $result = Controllers\Turns\insertFromExcel($productValue);
        if(is_string($result)  &&  strpos($result, 'ERROR') !== false){
            return $response->withJson([
                'error'=>$result,
                'code'=>404
            ], 400);
        }else{
            $results = Controllers\Turns\getCurrentFromDB($productValue);
            if(is_string($result)  &&  strpos($results, 'ERROR') !== false ){
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


$app->get('/procedura/{product}/{sqlquery}', function ($request, $response, $args) {
    $productValue = $args['product'];
    $procedure_query="CALL ".$args['sqlquery'];

    $result = Controllers\Turns\runProcedure($procedure_query);
    if(is_string($result)  &&  strpos($result, 'ERROR') !== false ){
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

$app->get('/removeOns/{sqlquery}', function ($request, $response, $args) {
    $procedure_query=$args['sqlquery'];
    $result = Controllers\Turns\removeOns($procedure_query);
    if(is_string($result)  &&  strpos($result, 'ERROR') !== false ){
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
$app->get('/updateOns/{sqlquery}', function ($request, $response, $args) {
    $procedure_query=$args['sqlquery'];
    $result = Controllers\Turns\updateOns($procedure_query);
    if(is_string($result)  && strpos($result, 'ERROR') !== false ){
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
$app->get('/gettemplates/{product}', function ($request, $response, $args) {
    $productValue=$args['product'];
    $sql = "";
    if($productValue=='Heko'){
        $sql = "wl wyl heko";
    }else if ($productValue=='Turbiny'){
        $sql = "wl wyl turbiny";
    }else if ($productValue=='Polmo'){
        $sql = "wl wyl polmo";
    }else if ($productValue=='Rezaw'){
        $sql = "wl wyl rezaw";
    }
    $sqlquery = "select description,xml_template,sql_template from ebay_api_calls.turbodziobak_job_predefines where job_type='ReviseInventoryStatus' and `name` = '".$sql."';";
    $result = Controllers\Turns\sendJobs($sqlquery);
    if(is_string($result)  && strpos($result, 'ERROR') !== false ){
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
