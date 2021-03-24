<?php
    
// Get the PHP helper library from twilio.com/docs/php/install
require_once '../vendor/autoload.php'; 

use Twilio\Rest\Client;

// Your Account Sid and Auth Token from twilio.com/user/account
$sid = "AC488b1908a49d2520b231dfebf8f770c1";
$token = "036777675c9396d32c1f3cb3bd36a8a1";


$person_exists = 0;
$opt_in = 0;
$relationship_exists = 0;
$mobile = 0;
$landline = 0;
$opt_in_missing_and_landline = 0;
$opt_in_missing_and_mobile = 0;
$name = NULL;
$customer_id = NULL;
$business_name = NULL;


if(isset($_POST['query']))
{
    $cell = $_POST['query'];
    
    $business_id = $_POST['biz_id'];
    
    
    require 'db_connect.php';
    
    //First check to see if person details already exists in person table
    $sql = "SELECT `first_name`, `person_id`, `phone_carrier_type`   
            FROM  `person` 
            WHERE `cell_phone` = '$cell'";
    
    $result = $conn->query($sql);
    
        if($result->num_rows > 0)
            {

                $person_exists = 1;

                $row = $result->fetch_assoc();

                    $name = $row["first_name"];
                    $customer_id = $row["person_id"];
                    $phone_carrier_type = strtolower($row["phone_carrier_type"]);
            
                    if($phone_carrier_type == 'm')
                    {
                        $mobile = 1;
                    }
                    else
                    {
                        $landline = 1;
                    }

                    $result->free();
            }
        
        
    if ($person_exists == 1)
        {
            ######BEGIN - Checks Opt In Table
            $sql = "SELECT *   
                    FROM  `opt_in` 
                    WHERE `customer_id` = '$customer_id'";
        
            $result = $conn->query($sql);
            
                if($result->num_rows > 0)
                {        
                    $opt_in = 1;
                }
            ######END - Checks Opt In Table
            
            
            
            ######BEGIN - Checks if Relationship
            $sql = "SELECT `business_name`   
                    FROM  `business` 
                    INNER JOIN  `relationship` ON relationship.business_id = business.business_id
                    AND (relationship.business_id = '$business_id' AND relationship.customer_id = '$customer_id')";

                        $result = $conn->query($sql);

                            if($result->num_rows != 0)
                                {

                                    $relationship_exists = 1;
                                
                                    $row = $result->fetch_assoc();
                                
                                    $business_name = $row['business_name'];

                                }
            ######END - Checks if Relationship
                            else
                                {
                                    $sql = "SELECT `business_name`   
                                            FROM  `business` 
                                            WHERE business_id = '$business_id'";

                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0)
                                    {
                                        $row = $result->fetch_assoc();

                                        $business_name = $row['business_name'];
                                    }
                                }
                
        }
        elseif ($person_exists == 0)
        {
            $client = new Client($sid, $token);

            $number = $client->lookups
                ->phoneNumbers("+1".$cell)
                ->fetch(
                    array("countryCode" => "US", "type" => "carrier")
                );
            
                $is_mobile = $number->carrier["type"];
            
                if(substr($is_mobile,0,1) == 'm')
                {
                    $mobile = 1;
                }
        }

    
    
        if ($person_exists == 0 and $mobile == 0)
            {
                echo "Please Enter<br>a Mobile Number";
            }
        elseif($person_exists == 0 and $mobile == 1)
            {
                echo "Enter Your First Name";
            }
        elseif ($person_exists == 1 and $landline == 1) 
            {
                if ($relationship_exists == 0)
                {
                    echo "Please Enter a Mobile Number<br>Landlines No Longer Accepted";
                }
                else
                {
                    echo "Hi ".$name."! <br> Welcome Back!";
                }
            }
        elseif ($person_exists == 1 and $opt_in == 1) 
            {
                if($relationship_exists == 1)
                {
                    echo "Hi ".$name."! <br> Welcome Back!";
                }
                else
                {
                    echo "Hey ".$name."! <br> Welcome to the ".$business_name." Reward Club! <br> Click below to sign-up.";
                }
            }
        elseif ($person_exists == 1 and $opt_in == 0) 
            {
                if($mobile == 1 and $relationship_exists == 1)
                {
                    echo "Good to see ya ".$name."!<br>
                    To complete your sign-up, <br>
                    Reply with 'Y' to the text <br>
                    that you will receive shortly.<br>
                    Once you've done that,<br>
                    Re-Enter Your Cell Phone<br>
                    Here Again.";
            
                    require "resend_opt_in_request.php";
                }
                elseif($mobile == 1 and $relationship_exists == 0)
                {
                    echo "Good to see ya ".$name."!<br>
                    To complete your sign-up, <br>
                    Reply with 'Y' to the text <br>
                    that you will receive shortly.<br>
                    Once you've done that,<br>
                    Re-Enter Your Cell Phone<br>
                    Here Again.";
            
                    require "resend_opt_in_request.php";
                }
                
            }
    
}


?>