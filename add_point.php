<?php

date_default_timezone_set("America/New_York");

//generates random 4-digit redeemption code for use in Redemption text
$redeemCode = rand(1000, 9999);
$redeemCode = strval($redeemCode);
//sets the expiration date
$d=strtotime("+1 week");
$exp = date("M-d-Y", $d);
//

/* - BEGIN OF - FUNCTIONS INDEX TO BE USED IN MAIN CODE BELOW*/

//function to return the number of points customer has accumulated
//to be used in return message of addPoint() function.
function returnPoints(){
    
    $cell = $_POST['postnumber'];
    
    $biz_id = $_POST['biz_id'];
    
    //Opens DB connection
    require 'db_connect.php';
    
    //retrievs Points from db
    $sql = "SELECT `current_points` FROM `relationship` 
            WHERE `business_id` = '$biz_id'
            AND `customer_id`= (SELECT `person_id` 
                                FROM `person`
                                WHERE `cell_phone` = '$cell')";
    
    //checks if sql command was successfull
    $result = $conn->query($sql);
        
    $row = $result->fetch_assoc();
                
    return $row["current_points"];
            
}

function returnThreshold(){
    
    $cell = $_POST['postnumber'];
    
    $biz_id = $_POST['biz_id'];
    
    //Opens DB connection
    require 'db_connect.php';
    
    //retrievs Points from db
    $sql = "SELECT `redemption_trigger` FROM `redemption_threshold` 
            WHERE `business_id` = '$biz_id'";
    
    //checks if sql command was successfull
    $result = $conn->query($sql);
    
    $row = $result->fetch_assoc();
                
    return $row["redemption_trigger"];
         
}


/* - END OF Helper FUNCTIONS TO BE USED IN MAIN CODE BELOW*/

if(isset($_POST['postnumber']))
{
    
    
    //Variable to be used in thes queries 
    $cell = $_POST['postnumber'];
    
    $biz_id = $_POST['biz_id'];
    
    $cust_id = '';
    
    
    //Opens DB connection
    require 'db_connect.php';
    
    // Queries DB
    $sql = "SELECT * FROM `relationship` 
            WHERE `business_id` = '$biz_id'
            AND `customer_id`= (SELECT `person_id` 
                                FROM `person`
                                WHERE `cell_phone` = '$cell')";
    
    $result = $conn->query($sql);
    
        if($result->num_rows != 0)
        {
            
            $row = $result->fetch_assoc();
                        
                //Saves Cust_id
                $cust_id = $row['customer_id'];
            
                //pulls last-visit date-time for that customer from database
                $last_check_in = strtotime($row['last_visit']);
                
                //reformats date-time to date only
                $last_visit = date("Y-m-d",$last_check_in);
            
                //finds what today's date via php
                $today = date("Y-m-d");
            
                $current_date_time = date("Y-m-d h:i:s");


                    //CHECKS today's PHP DATE VS. SQL LAST CHECK-IN DATE
                    if($last_visit == $today)
                    {
                        echo "Sorry, You've already checked-in today";
                        
                    }
                    else
                    {
                        
                        $points = returnPoints();
                        
                        $points_compare = $points + 1;
                        
                        $threshold = returnThreshold();
                        
                        if($points_compare >= $threshold)
                        {
    
                            $sql = "UPDATE `relationship` 
                                    SET `current_points`=0, `total_points`=(`total_points`+1), `redemptions_claimed` = (`redemptions_claimed`+1 ), `last_visit`= '$current_date_time'  
                                    WHERE `business_id` = '$biz_id' AND `customer_id`= '$cust_id';";
                            
                            $sql .= "INSERT INTO `visits` (`business_id`, `customer_id`, `points_earned`, `points_redeemed`, `visit_time`, `new_member`) VALUES ('$biz_id', '$cust_id', 1, 1, '$current_date_time', 'n')";
                                        
                                        if($conn->multi_query($sql) === TRUE)
                                        {
                                            echo "Thank you for your business! <br>You should receive a text message shortly with a promo code ".$redeemCode." for your reward. <br>Please show this to the customer service agent. Enjoy!<br>Code expires: ".$exp;
                                            
                                            require 'tablet_redeem_text.php';

                                        }
                                        else
                                        {
                                            echo "Error Updating Redemption Details: " . $conn->error;
                                        }
                                    
                            }
                            else
                            {
                                    
                                     //MULTIPLE SQLI 
                                    $sql = "UPDATE `relationship` 
                                            SET `current_points`= (`current_points`+1), `total_points` = (`total_points`+1), `last_visit`= '$current_date_time'
                                            WHERE `business_id` = '$biz_id' 
                                            AND `customer_id` = '$cust_id';";

                                    $sql .= "INSERT INTO `visits` (`business_id`, `customer_id`, `points_earned`, `points_redeemed`, `visit_time`, `new_member`) VALUES ('$biz_id', '$cust_id', 1, 0, '$current_date_time', 'n')";


                                        //checks if sql command was successfull
                                        if ($conn->multi_query($sql) != TRUE)
                                            {
                                                echo "Error Updating Relationship Points and Time: " . $conn->error;
                                            }
                                        else
                                            {
                                                echo "Welcome Back. <br>Thank you for checking-in. <br>You have ".$points_compare." points";
                                            }
                                    
                            }
            
                   

                                
                                
                                
                                
                    }
        }
}
    

$conn->close();

?>