<?php

date_default_timezone_set("America/New_York");

$current_date_time = date("Y-m-d h:i:s");

                               
if(isset($_POST['biz_id']))
{
    
    $biz_id = $_POST['biz_id'];
    $cell = $_POST['cell_phone'];
    
    //Opens DB connection
    require 'db_connect.php';

        //Selects person_id to be used in relationship table
        $sql = "SELECT `person_id` 
                FROM `person` 
                WHERE `cell_phone` = '$cell'";
        
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            
            $cust_id = $row['person_id'];
            
            //Inserts Relationship into Relationship table
            $sql = "INSERT INTO `relationship`(`business_id`, `customer_id`, `current_points`, `total_points`, `wants_text`, `is_active`, `added_on`, `redemptions_claimed`, `last_visit`) VALUES ('$biz_id', '$cust_id', 1, 1, 'y', 'y', '$current_date_time',0,'$current_date_time')";
            
            if ($conn->query($sql) === TRUE)
            {
                echo "Congrats! You are registered. <br> You have 1 point.";
            }
            else
            {
                echo "Error Relationship record: " . $conn->error;       
            }
        }
        else
        {
            echo "No Records Found";
        }
        
        
}


?>