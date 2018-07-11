<?php
namespace Controllers\Turns;
ini_set('max_execution_time', 0);
header('Access-Control-Allow-Origin: *');
require_once '/../excel_reader/php-excel-reader/excel_reader2.php';
require_once '/../excel_reader/SpreadsheetReader.php';
$conn = new \PDO('mysql:host=192.168.1.60;dbname=konradd;port=13306', 'konradd', 'samba20');
$conn -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

function process(){

}
function insertFromExcel($productValue){
    global $conn;
    
    $Reader = new \SpreadsheetReader("uploads/".$productValue.".xls");
    $data = array();
    foreach ($Reader as $Row){	
        if($productValue=='Polmo' ){
            if(preg_match("/P/i",$Row[0])){
                array_push($data,(object)array("sku" => $Row[0], "wylaczenie" => $Row[1]));
            }else{
                return  "ERROR: W kolumnie z sku jest jakies, ktoro nie jest polmo";
            }
        }
        else if($productValue=='Turbiny'){
            if(preg_match("/O/i",$Row[0])){
                array_push($data,(object)array("sku" => $Row[0], "wylaczenie" => $Row[1]));
            }else{
                return  "ERROR: W kolumnie z sku jest jakies, ktoro nie jest turbina";
            }
        }
        else if($productValue=='Heko'){
            if(preg_match("/H/i",$Row[0])){
                array_push($data,(object)array("sku" => $Row[0], "wylaczenie" => $Row[1], "kraj" => $Row[2]));
            }else{
                return  "ERROR: W kolumnie z sku jest jakies, ktoro nie jest heko";
            }
        }
    }
    if($productValue=='Polmo'){
        $sql = "INSERT INTO konradd.wl_wyl_polmo (sku, do_wlaczenia, `data`) VALUES ";
    }else if($productValue=='Heko'){
        $sql = "INSERT INTO mateuszp.wl_wyl_heko (sku, do_wlaczenia,country, `data`) VALUES ";
    }else if($productValue=='Turbiny'){
        $sql = "INSERT INTO mateuszp.wl_wyl_turbiny (sku, do_wlaczenia, `data`) VALUES ";
    }
    
    foreach($data as $row){
        if($productValue=='Polmo' || $productValue=='Turbiny'){
            $sql = $sql." ('$row->sku', $row->wylaczenie,current_date()), ";
        }else if($productValue=='Heko'){
            $sql = $sql." ('$row->sku', $row->wylaczenie,'$row->kraj',current_date()), ";
        }
    } 
    
    try {
        $sql = substr($sql, 0, -2);
        $conn->exec($sql);
        return true;
    }catch(\PDOException $e){
        return "ERROR: ". "<br>" . $e->getMessage();
    }
    return true;
}
function getCurrentFromDB(){
    global $conn;
    $testArray = array();
    $sql = "SELECT * FROM konradd.wl_wyl_polmo where `data`=current_date()";
    try{
        $result = $conn->query($sql);
        while($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            array_push($testArray,(object)array("sku" => $row["sku"], "do_wlaczenia" => $row["do_wlaczenia"], "data" => $row["data"]));
        }
        return json_encode($testArray);
    }catch(\PDOException $e){
        return "ERROR: <br>" . $e->getMessage();
    }
}

function removeOns(){
    global $conn;
    $sql = "delete from konradd.wylaczone_sku where sku in ( SELECT sku FROM konradd.wl_wyl_polmo where do_wlaczenia=1 and data=current_date() )";
    try{
        $conn->exec($sql);
        return "Records deleted successfully";
    }catch(\PDOException $e){
        return "ERROR: <br>" . $e->getMessage();
    }
}
function updateOns(){
    global $conn;
    $sql = "update konradd.wylaczone_aukcje_polmo set data_wlaczenia=current_timestamp() where sku in (SELECT sku FROM konradd.wl_wyl_polmo where do_wlaczenia=1 and data=current_date()) and data_wlaczenia is null";
    try{
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount() . " records UPDATED successfully";;
    }catch(\PDOException $e){
        return "ERROR: ".  "<br>" . $e->getMessage();
    }
}

function runProcedure($sql){
    global $conn;
    $stmt= $conn->prepare($sql);
    try{
        $success = $stmt->execute();
        return 'Wykonano procedure';
    }catch(\PDOException $e){
        return "ERROR: " . "<br>" . $e->getMessage();
    }
}

?>