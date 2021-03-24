<?php

date_default_timezone_set("America/New_York");

//Twilio Phone Carrier Type (i.e Mobile or Landline)
require_once '../vendor/autoload.php'; // Loads the library

use Twilio\Rest\Client;

// Your Account Sid and Auth Token from twilio.com/user/account
$sid = "AC488b1908a49d2520b231dfebf8f770c1";
$token = "036777675c9396d32c1f3cb3bd36a8a1";

$client = new Client($sid, $token);


include 'config.php';
include 'class-db.php';



if(isset($_POST['process']))
{
    
    $process = $_POST['process'];
    
    switch ($process){
        
        case 'autocomplete':
            
            $cell_phone = $_POST['cell_phone'];
            $business_id = $_POST['biz_id'];
            $business_name = $_POST['business_name'];
            
            //Validation Variables
            $person_exists = 0;
            $is_mobile = 0;
            $relationship_exists = 0;
            //Setting Opt_in = 1 in case we want to use Opt_ins in future
            $opt_in = 1;
            
            
            //Check if person exists in person table
            $table = 'person';
            $columns = array('person_id', 'first_name', 'phone_carrier_type');
            $where = 'cell_phone';
            
            $results = $db->select($table, $columns, $where, $cell_phone);
            
            if($results->num_rows > 0)
            {
                $person_exists = 1;
                
                $row = $results->fetch_assoc();
                
                    
                    $customer_id = $row["person_id"];
                    $phone_carrier_type = strtolower($row["phone_carrier_type"]);
            
                    if($phone_carrier_type == 'm')
                    {
                        $mobile = 1;
                    }

                    $results->free();
            }
            
            if($person_exits == 1)
            {
                //check if relationship exists
                $table = 'relationship';
                $columns = 'relationship_id';
                $where = array('customer_id', 'business_id');
                $equals = array($customer_id, $business_id);
                
                    $results = checkRelationship($table, $columns, $where, $equals);
                
                        if($results->num_rows > 0)
                        {
                            $relationship_exists = 1;   
                        }
                
                
            }
            elseif($person_exists == 0)
            {
                //check if numer they entered is mobile
                $number = $client->lookups
                ->phoneNumbers("+1".$cell)
                ->fetch(
                    array("countryCode" => "US", "type" => "carrier")
                );
            
                $phone_type = $number->carrier["type"];
            
                if(substr($phone_type,0,1) == 'm')
                {
                    $is_mobile = 1;
                }
            }
            
            
            //Send Back Response According to Info just verified
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
                        echo "Please Enter a Mobile Number<br>Landlines Are Invalid";
                    }
                    else
                    {
                        echo "Hi! <br> Welcome Back!";
                    }
                }
            elseif ($person_exists == 1 and $opt_in == 1) 
                {
                    if($relationship_exists == 1)
                    {
                        echo "Hi! <br> Welcome Back!";
                    }
                    else
                    {
                        echo "Hey! <br> Welcome to the ".$business_name." Reward Club! <br> Click below to sign-up.";
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
            
            break;
            
        case 'add_point':
            
            $cell_phone = $_POST['cell_phone'];
            $business_id = $_POST['business_id'];
            
            
            //Get Relationship ID
            $tables = array('relationship', 'person');
            $columns = array('relationship_id', 'person_id');
            $where = array('business_id', 'cell_phone');
            $equals = array($business_id, $cell_phone);
            
                $results = $db->getTabletRelationshipID($tables, $columns, $where, $equals);
            
                $row = $results->fetch_assoc();
            
                $relationship_id = $row['relationship_id'];
            
            //Get Check-in Tally
            $table = 'check_in_tally';
            $columns = array('check_in_count', 'last_check_in_date');
            $where = 'relationship_id';
            
                $results = $db->select($table, $columns, $where, $relationship_id);
            
                $row = $results->fetch_assoc();
            
                $results->free();
            
                    
                    //Gets last_check_in date
                    $last_check_in = strtotime($row['last_check_in_date']);
                    $current_count = $row['check_in_count'];
            
                    //reformats date-time to date only
                    $last_check_in = date("Y-m-d", $last_check_in);
            
                    //finds what today's date is via php
                    $today = date("Y-m-d");
            
                        //CHECKS if Customer already Checked in Once Today
                        if($last_check_in == $today)
                        {
                            echo "Sorry, You've already checked-in today";

                        }
                        else
                        {
                            //Get Check-in Threshold
                            $table = 'business';
                            $columns = 'check_in_threshold';
                            $where = 'business_id';

                                $results = $db->select($table, $columns, $where, $business_id);

                                $row = $results->fetch_assoc();

                                $results->free();
                            
                                $threshold = $row['check_in_threshold'];
                            
                            
                                    if(($check_in_count + 1) >= $threshold)
                                    {
                                        //Reset Check_in_count to 0
                                        $table = 'check_in_tally';
                                        $columns = array('check_in_count', 'last_check_in_date');
                                        $columns_in = array('0', date("Y-m-d H:i:s"));
                                        $where = 'relationship_id';
                                        
                                            $db->update_check_in_log($table, $columns, $columns_in, $where, $relationship_id);
                                        
                                        //Insert Check In into Visit Log
                                        $table = 'check_in_log';
                                        $fields = array('relationship_id', 'check_in_date', 'check_in_dollars');
                                        $values = array($relationship_id, date("Y-m-d H:i:s"), '0');
                                        
                                            $log_id = $db->insert($table, $fields, $values);
                                        
                                        
                                        //Send Response + Redeem Text
                                        echo "Thank you for your business! <br>You should receive a text message shortly with a promo code ".$redeemCode." for your reward. <br>Please show this to the customer service agent. Enjoy!<br>Code expires: ".$exp;
                                            
                                            require 'tablet_redeem_text.php';
                                        
                                    }
                                    else
                                    {
                                        //New Tally Count
                                        $new_count = $check_in_count+1;
                                        
                                        $table = 'check_in_tally';
                                        $columns = array('check_in_count', 'last_check_in_date');
                                        $columns_in = array($new_count, date("Y-m-d H:i:s"));
                                        $where = 'relationship_id';
                                        
                                            $db->update_check_in_log($table, $columns, $columns_in, $where, $relationship_id);
                                        
                                        //Insert Check In into Visit Log
                                        $table = 'check_in_log';
                                        $fields = array('relationship_id', 'check_in_date', 'check_in_dollars');
                                        $values = array($relationship_id, date("Y-m-d H:i:s"), '0');
                                        
                                            $log_id = $db->insert($table, $fields, $values);
                                        
                                        
                                        //Send Response + New Count
                                        echo "Welcome Back. <br>Thank you for checking-in. <br>You have ".$new_count." points";    
                                        
                                    }
                        }

            break;
        
        case 'newMember':
            
            $firstname = $_POST['firstname'];  
            $biz_id = $_POST['biz_id'];
            $cell = $_POST['postnum'];
            $to_number = "+1".$cell;
            $from_number = "+12033090525";
            $today = date("Y-m-d h:i:s");
            $biz_name = NULL;
            $first_word_biz_name = NULL;
            
            //Place holder for if number is mobile #
            $is_mobile = 0;
            
            //check if numer they entered is mobile
                $number = $client->lookups
                ->phoneNumbers("+1".$cell)
                ->fetch(
                    array("countryCode" => "US", "type" => "carrier")
                );
            
                $phone_type = $number->carrier["type"];
            
                if(substr($phone_type,0,1) == 'm')
                {
                    $is_mobile = 1;
                }
            
            if($is_mobile != 1)
            {
                echo "Please Enter a Mobile Number<br>Landlines Are Invalid";
            }
            else
            {
                //Insert into Person table
                $table = 'person';
                $fields = array('first_name', 'cell_phone', 'is_active_person', 'person_added_on');
                $values = array($_POST['firstname'], $_POST['cell_phone'], 'y', date("Y-m-d H:i:s"));

                    $person_id = $db->insert($table, $fields, $values);
                
                
                //Insert into Customer table
                $table = 'customer';
                $fields = array('wants_all_text', 'wants_all_email', 'is_active_customer', 'customer_added_on', 'person_id', 'avatar_link');
                $values = array('y', 'y', 'y', 'y', date("Y-m-d H:i:s"), "$person_id", 'https://i.socialiite.io/resources/images/selfie/selfie.jpg');

                    $customer_id = $db->insert($table, $fields, $values);
                
                
                //Insert into Relationship table
                $table = 'relationship';
                $fields = array('business_id', 'customer_id', 'wants_text', 'wants_email', 'is_active', 'added_on', 'source', 'review_sent', 'sign_up');
                $values = array("$business_id", "$customer_id", 'y', 'y', 'y', date("Y-m-d H:i:s"), 'T', 'n', 'y');

                    $relationship_id = $db->insert($table, $fields, $values);
                
                //Echo Welcome Message
                echo " <br>Congratulations!<br>
                        You will receive a <br> 
                        Text Message shortly.<br>
                        Complete your membership<br>
                        by replying with a 'Y'.";

                require 'new_member_opt_in.php';
            }
            
            break;
        
        case 'add_relationship':
            
            $cell_phone = $_POST['cell_phone'];
            $business_id = $_POST['business_id'];
            $business_name = $_POST['business_name'];
            
            //Get customer_id
            $tables = array('customer', 'person');
            $columns = array('customer_id', 'person_id');
            $where = array('person_id', 'cell_phone');
            
                $customer_id = $db->getTabletCustomerID($tables, $columns, $where, $cell_phone);
            
            //Insert new relationship
            $table = 'relationship';
            $fields = array('business_id', 'customer_id', 'wants_text', 'wants_email', 'is_active', 'added_on', 'source', 'review_sent', 'sign_up');
            $values = array("$business_id", "$customer_id", 'y', 'y', 'y', date("Y-m-d H:i:s"), 'T', 'n', 'y');
                
                $relationship_id = $db->insert($table, $fields, $values);
            
            echo "Congrats! You're all set up. <br> You have 1 point.";
            
            require 'new_relationship.php';
            
            break;
            
            
            
    }
}
else
{
    echo "You've Reached This Page in Error";
}
?>    