<?php
include_once("classes/Validation.php");
$validation = new Validation();

$stockName = $_POST['drpStockName'];
$csvName = $_FILES['csv']['name'];
$fromDateValue = $_POST['fromDate'];
$toDateValue = $_POST['toDate'];

$msg = $validation->check_empty($_POST, array('drpStockName', 'fromDate', 'toDate'));
$msg_csv = $validation->check_empty($_FILES, array('csv'));
// checking empty fields
if($msg != null && $msg_csv != null) {	
    echo json_encode(array(
        "result" => $msg
    ));
    die();
}

$stockName = $_POST['drpStockName'];
$fromDate = $_POST['fromDate'];
$toDate = $_POST['toDate'];

$dateFrom = str_replace('/', '-', $fromDate);
$fromDate = date('Y-m-d', strtotime($dateFrom)); 

$dateTo = str_replace('/', '-', $toDate);
$toDate = date('Y-m-d', strtotime($dateTo)); 

$check_date = $validation->date_check($fromDate, $toDate);
if($check_date != null) {	
    echo json_encode(array(
        "result" => $msg
    ));
    die();
}

$name = $_FILES['csv']['name'];
$ext = strtolower(end(explode('.', $_FILES['csv']['name'])));
$type = $_FILES['csv']['type'];
$tmpName = $_FILES['csv']['tmp_name'];
$csv = array();
$price = 0;
$totalPrice = 0;
$totalCount = 0;
if(($handle = fopen($tmpName, 'r')) !== FALSE) {
    // necessary if a large csv file
    set_time_limit(0);
    $row = 0;
    try {
        while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            // number of fields in the csv
            $col_count = count($data);
            // get the values from the csv
            if($row > 0) {
                $dateCSV = str_replace('/', '-', $data[1]);
                $csvDate = date('Y-m-d', strtotime($dateCSV)); 
            }
            if($fromDate <= $csvDate && $toDate >= $csvDate) {
                // echo $row;
                if($stockName == $data[2]) {
                    $totalCount++;
                    $csv[$row]['id_no'] = $data[0];
                    $csv[$row]['date'] = $data[1];
                    $csv[$row]['stock_name'] = $data[2];
                    if(empty($data[3])) {
                        $csv[$row]['price'] =  $price;
                    } else {
                        // If the price of stock is not available at that date, it should take the price of the stock on the previous date
                        $price = $csv[$row]['price'] = $data[3];
                    }
                    $totalPrice = number_format(((float)$totalPrice+(float)($csv[$row]['price'])), 2, '.', '');
                    $keys = array_column($csv, 'price');
                    $final_data = array_multisort($keys, SORT_DESC, $csv);
                } 
            } else if($row > 0){
                echo json_encode(array(
                        "result" => 'No Record Found'
                    )
                );
                die();
            } 
            // inc the row
            $row++;
        }
        fclose($handle);
        $mean = $totalPrice/$totalCount;
        $standardDeviation = sqrt($mean/$totalCount);

        echo json_encode(array(
                "data" => $csv,
                "result" => 'success',
                "mean" => $mean,
                "standardDeviation" => $standardDeviation
            )
        );
    } //catch exception
    catch(Exception $e) {
      echo 'Message: ' .$e->getMessage();
    }
}

?>