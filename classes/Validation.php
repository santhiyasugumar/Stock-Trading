<?php
class Validation 
{
    public function check_empty($data, $fields)
    {
        $msg = null;
        foreach ($fields as $value) {
            if (empty($data[$value])) {
                $msg .= "$value field empty <br />";
            }
        } 
        return $msg;
    }
    
    public function date_check($fromDate, $toDate)
    {
        $msg = null;
        if ($fromDate > $toDate) {   
            $msg .= "From Date is lesser than To Date";
        } 
        return false;
    }
    
}
?>