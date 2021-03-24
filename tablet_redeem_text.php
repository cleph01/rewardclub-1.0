<?php

//function to send Twilio Welcome Text

date_default_timezone_set("America/New_York");

$text_log_record = 0;

$error_log_record = array();

$current_date_time = '';

$to_number = "+1".$cell;


//Step 1: Get the Twilio-PHP library 
    require_once "../vendor/autoload.php";
    use Twilio\Rest\Client;

    // Step 2: set our AccountSid and AuthToken
    $AccountSid = "AC488b1908a49d2520b231dfebf8f770c1";
    $AuthToken = "036777675c9396d32c1f3cb3bd36a8a1";

    $from_number = "+18888110989";

    // Step 3: instantiate a new Twilio Rest Client
    $client = new Client($AccountSid, $AuthToken);

    
        //Query Db for biz and person names to include on redeem text
        require 'db_connect.php';

        $sql = "SELECT person.first_name, business.business_name
                FROM  `person` 
                INNER JOIN  `relationship` ON person.person_id = relationship.customer_id
                INNER JOIN  `business` ON relationship.business_id = business.business_id
                AND (relationship.business_id = '$biz_id'
                    AND person.cell_phone = '$cell')";

        $result = $conn->query($sql);
    
if ( $result->num_rows > 0)
    {
        $row = $result->fetch_assoc();
            
        $customer_name = $row['first_name'];
        $biz_name = $row['business_name'];
            
        try 
            {
                //Instantiates Twilio Message sender
                $sms = $client->account->messages->create(

                // the number we are sending to - Any phone number
                $to_number,

                array(
                    // Step 6: Change the 'From' number below to be a valid Twilio number 
                    // that you've purchased
                    'from' => $from_number, 

                            // the sms body
                            'body' => "Hey $customer_name, from $biz_name: Congratulations! Please stop in and show promo code ".$redeemCode." by ".$exp." to redeem your prize. Enjoy! <If you enjoy getting GREAT deals DO NOT reply STOP to unsubscribe>"
                            )
                        );
                    
                        //Populate the Text Log Array to be inserted into DB below
    
                        $current_date_time = date("Y-m-d h:i:s");

                        $text_log_record = 1;
                }
            catch (Exception $e)
                {
                    //catch any exception errors from Twilio
                    $error_code = $e->getCode();
                    $error_message = $e->getMessage();
                    //Steps to remove problematic DB update chars from error_message
                    $arr_remove = array("-","/","'");
                    $db_error_message = str_replace($arr_remove, "", $error_message);
                        
                        //Populate the Error Log Array to be inserted into DB below
            
                        $current_date_time = date("Y-m-d h:i:s");

                        $error_log_record['error_code'] = $error_code;
                        $error_log_record['error_message'] = $db_error_message; 
                }


    }
else
    {
                
        echo "Error Selecting Person Name and Biz Name: " . $conn->error;
            
    }

if ($text_log_record == 1)
{
    //Inserts Record of Welcome text in db_text_log
    
    $sql = "INSERT INTO `business_text_log`(`business_id`, `customer_id`, `to_number`, `twilio_number`, `text_purpose`, `date_time`,`text_message`) VALUES ('$biz_id', '$cust_id', '$cell', '$from_number', 'redeem', '$current_date_time', 'redeem')";

        if ($conn->query($sql) != TRUE)
        {
            echo "Error Inserting Welcome Text: " . $conn->error;
        }
}
 
if (count($error_log_record) > 0)
{
     //Inserts record of Error Message into db_text_error_log
    
    $err_code = $error_log_record['error_code'];
    $err_msg = $error_log_record['error_message'];
                             
    $sql = "INSERT INTO `text_error_log`(`business_id`, `customer_id`, `to_number`, `error_code`, `error_message`, `date_time`, `twilio_number`, `text_message`) VALUES ('$biz_id', '$cust_id', '$cell', '$err_code', '$err_msg', '$current_date_time', '$from_number', 'redeem')";

        if ($conn->query($sql) != TRUE)
        {
            echo "Error Inserting Welcome Error Record: " . $conn->error;
        }    
}

    
?>