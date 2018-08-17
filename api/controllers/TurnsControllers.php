<?php
namespace Controllers\Turns;
ini_set('max_execution_time', 0);
header('Access-Control-Allow-Origin: *');
require_once './excel_reader/php-excel-reader/excel_reader2.php';
require_once './excel_reader/SpreadsheetReader.php';
$conn = new \PDO('mysql:host=192.168.1.60;dbname=app_wylaczenia;port=13306', 'app_wylaczenia', 'Qaz@654#');
$conn -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

function process(){

}
function insertFromExcel($productValue){
    global $conn;
    
    $Reader = new \SpreadsheetReader("uploads/".$productValue.".xls");
    $data = array();
    foreach ($Reader as $Row){	
        if($productValue=='Polmo' ){
            if(isset($Row[0]) && preg_match("/P/i",$Row[0])){
                if(isset($Row[1]) && preg_match("/^[0-1]$/",$Row[1])){
                    array_push($data,(object)array("sku" => strtoupper($Row[0]), "wylaczenie" => $Row[1]));
                }else{
                    return  "ERROR: W kolumnie z wylaczeniem jest cos innego niz 1 lub 0";
                }
            }else{
                return  "ERROR: W kolumnie z sku jakiegos brakuje, lub jest jakies,ktoro nie jest polmo";
            }
        }
        else if($productValue=='Rezaw'){
            if(isset($Row[0]) && preg_match("/G|R|W|S/i",$Row[0])){
                if(isset($Row[1]) && preg_match("/^[0-1]$/",$Row[1])){
                    if(isset($Row[2]) && preg_match("/^(GB|ALL)$/",$Row[2])){
                        array_push($data,(object)array("sku" => $Row[0], "wylaczenie" => $Row[1], "kraj" => $Row[2]));
                    }else{
                        return  "ERROR: W kolumnie z krajem jest cos innego niz GB lub ALL";
                    }
                }else{
                    return  "ERROR: W kolumnie z wylaczeniem jest cos innego niz 1 lub 0";
                }
            }else{
                return  "ERROR: W kolumnie z sku jakiegos brakuje, lub jest jakies,ktoro nie jest rezawu";
            }
        }
        else if($productValue=='Turbiny'){
            if(isset($Row[0]) && preg_match("/O/i",$Row[0])){
                if(isset($Row[1]) && preg_match("/^[0-1]$/",$Row[1])){
                    array_push($data,(object)array("sku" => $Row[0], "wylaczenie" => $Row[1]));
                }else{
                    return  "ERROR: W kolumnie z wylaczeniem jest cos innego niz 1 lub 0";
                }
            }else{
                return  "ERROR: W kolumnie z sku jakiegos brakuje, lub jest jakies,ktoro nie jest turbina";
            }
        }
        else if($productValue=='Heko'){
            if(isset($Row[0]) && preg_match("/H/i",$Row[0])){
                if(isset($Row[1]) && preg_match("/^[0-1]$/",$Row[1])){
                    if(isset($Row[2]) && preg_match("/^(GB|ALL)$/",$Row[2])){
                        array_push($data,(object)array("sku" => $Row[0], "wylaczenie" => $Row[1], "kraj" => $Row[2]));
                    }else{
                        return  "ERROR: W kolumnie z krajem jest cos innego niz GB lub ALL";
                    }
                }else{
                    return  "ERROR: W kolumnie z wylaczeniem jest cos innego niz 1 lub 0";
                }
            }else{
                return  "ERROR: W kolumnie z sku jakiegos brakuje, lub jest jakies,ktoro nie jest heko";
            }
        }
    }
    if($productValue=='Polmo'){
        $sql = "INSERT INTO app_wylaczenia.wl_wyl_polmo (sku, do_wlaczenia, `data`) VALUES ";
    }else if($productValue=='Heko'){
        $sql = "INSERT INTO app_wylaczenia.wl_wyl_heko (sku, do_wlaczenia,country, `data`) VALUES ";
    }else if($productValue=='Turbiny'){
        $sql = "INSERT INTO app_wylaczenia.wl_wyl_turbiny (sku, do_wlaczenia, `data`) VALUES ";
    }else if($productValue=='Rezaw'){
        $sql = "INSERT INTO app_wylaczenia.wl_wyl_rezaw (sku, do_wlaczenia,country, `data`) VALUES ";
    }
    
    foreach($data as $row){
        if($productValue=='Polmo' || $productValue=='Turbiny'){
            $sql = $sql." ('$row->sku', $row->wylaczenie,current_date()), ";
        }else if($productValue=='Heko' || $productValue=='Rezaw'){
            $sql = $sql." ('$row->sku', $row->wylaczenie,'$row->kraj',current_date()), ";
        }
    } 
    
    try {
        $sql = substr($sql, 0, -2);
        $conn->exec($sql);
        return true; 
    }catch(\PDOException $e){
        return "ERROR: " . $e->getMessage();
    }
}

function getCurrentFromDB($productValue){
    global $conn;
    $testArray = array();
    $sql = "";
    if($productValue=='Heko'){
        $sql = "select * from app_wylaczenia.wl_wyl_heko";
    }else if ($productValue=='Turbiny'){
        $sql = "select * from app_wylaczenia.wl_wyl_turbiny";
    }else if ($productValue=='Polmo'){
        $sql = "select * from app_wylaczenia.wl_wyl_polmo";
    }else if ($productValue=='Rezaw'){
        $sql = "select * from app_wylaczenia.wl_wyl_rezaw";
    }
    try{
        $result = $conn->query($sql);
        while($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            if($productValue=='Heko'){
                array_push($testArray,(object)array("sku" => $row["sku"], "do_wlaczenia" => $row["do_wlaczenia"],"kraj" => $row["country"], "data" => $row["data"]));
            }else if ($productValue=='Turbiny'){
                array_push($testArray,(object)array("sku" => $row["sku"], "do_wlaczenia" => $row["do_wlaczenia"], "data" => $row["data"]));
            }else if ($productValue=='Polmo'){
                array_push($testArray,(object)array("sku" => $row["sku"], "do_wlaczenia" => $row["do_wlaczenia"], "data" => $row["data"]));
            }else if ($productValue=='Rezaw'){
                array_push($testArray,(object)array("sku" => $row["sku"], "do_wlaczenia" => $row["do_wlaczenia"],"kraj" => $row["country"], "data" => $row["data"]));
            }
        }
        return json_encode($testArray);
    }catch(\PDOException $e){
        return "ERROR: " . $e->getMessage();
    }
}

function removeOns($sql){
    global $conn;
    try{
        $conn->exec($sql);
        return "Records deleted successfully";
    }catch(\PDOException $e){
        return "ERROR: " . $e->getMessage();
    }
}
function updateOns($sql){
    global $conn;
    try{
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return "Records UPDATED successfully";
    }catch(\PDOException $e){
        return "ERROR: ". $e->getMessage();
    }
}

function runProcedure($sql){
    global $conn;
    $stmt= $conn->prepare($sql);
    try{
        $success = $stmt->execute();
        return 'Wykonano procedure';
    }catch(\PDOException $e){
        return "ERROR: ". $e->getMessage();
    }
}
function sendJobs($sqlquery){
    global $conn;
    $testArray = array();
    try{
        $stmt = $conn->prepare($sqlquery); 
        $stmt->execute(); 
        $row = $stmt->fetch();
        $sthx = $conn->prepare(str_replace("\n", " ", $row['sql_template']));
        $sthx -> execute();
        if ($sthx->rowCount() > 0) {
            return $row;
        }else {
            return "ERROR: Nie wygenerowano wl/wyl na dzis";
        }

    }catch(\PDOException $e){
        return "ERROR: " . $e->getMessage();
    }
}

?>