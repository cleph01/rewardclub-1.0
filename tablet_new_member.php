<?php

date_default_timezone_set("America/New_York");

//Twilio Phone Carrier Type (i.e Mobile or Landline)
    require_once '../vendor/autoload.php'; // Loads the library

    use Twilio\Rest\Client;

    // Your Account Sid and Auth Token from twilio.com/user/account
    $sid = "AC488b1908a49d2520b231dfebf8f770c1";
    $token = "036777675c9396d32c1f3cb3bd36a8a1";

    $client = new Client($sid, $token);
                               
if(isset($_POST['firstname']))
{
    
    $firstname = $_POST['firstname'];  
    $biz_id = $_POST['biz_id'];
    $cell = $_POST['postnum'];
    $to_number = "+1".$cell;
    $from_number = "+12033090525";
    $today = date("Y-m-d h:i:s");
    $biz_name = NULL;
    $first_word_biz_name = NULL;

/*
    $number = $client->lookups
        ->phoneNumbers("$to_number")
        ->fetch(
            array("countryCode" => "US", "type" => "carrier")
        );

    $phone_carrier_type = $number->carrier["type"];
    
    $phone_carrier_type = strtoupper(substr($phone_carrier_type, 0,1));
*/

            //Opens DB connection
            require 'db_connect.php';
            
    
            //Inserts New Member into Person table
            $sql = "INSERT INTO `person`(`first_name`, `cell_phone`, `phone_carrier_type`, `is_active_person`, `person_added_on`) VALUES ('$firstname', '$cell', 'M', 'y', '$today')";

                if ($conn->query($sql) == TRUE)
                {
                    
                    //Query the Person_ID to be used as the Customer_ID in the Relationship Insert
                        
                        $sql = "SELECT `person_id` 
                                FROM `person` 
                                WHERE `cell_phone` = '$cell'";
                    
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0)
                        {
                            
                            $row = $result->fetch_assoc();  
                            
                            $cust_id = $row['person_id'];
                            
                            $sql = "INSERT INTO `relationship` (`business_id`, `customer_id`, `current_points`, `total_points`, `wants_text`, `is_active`, `added_on`, `redemptions_claimed`, `last_visit`) VALUES ('$biz_id', '$cust_id', 1, 1, 'y', 'y', '$today', 0, '$today');";
                            
                            $sql .= "INSERT INTO `visits` (`business_id`, `customer_id`, `points_earned`, `points_redeemed`, `visit_time`, `new_member`) VALUES ('$biz_id', '$cust_id', 1, 0, '$today', 'y')";
                            
                                if ($conn->multi_query($sql) === TRUE)
                                {

                                    echo " <br>Congratulations!<br>
                                            You will receive a <br> 
                                            Text Message shortly.<br>
                                            Complete your membership<br>
                                            by replying with a 'Y'.";

                                    require 'new_member_opt_in.php';
                                }
                                else
                                {
                                    echo "Error Inserting Relationship: " . $conn->error;
                                }       
                        }
                        else
                        {
                            echo "Error Selecting Persion ID from Table: " . $conn->error;
                        }
                    
                }
                else
                {
                    echo "Error Inserting New Person: " . $sql . "<br>" . $conn->error;
                }
                        
        
 

}


?>