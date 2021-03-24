<?php

date_default_timezone_set("America/New_York");

//finds what today's date via php
$today = date("Y-m-d");

//Step 1: Get the Twilio-PHP library 
    require_once "../vendor/autoload.php";
    use Twilio\Rest\Client;

    // Step 2: set our AccountSid and AuthToken
    $AccountSid = "AC488b1908a49d2520b231dfebf8f770c1";
    $AuthToken = "036777675c9396d32c1f3cb3bd36a8a1";

    // Step 3: instantiate a new Twilio Rest Client
    $client = new Client($AccountSid, $AuthToken);

    $i = 0;

    //Opens DB connection 
    require 'db_connect.php';
    
    
    $sql = "SELECT * FROM `twilio_numbers`;";
    
    $sql .= "SELECT `cell`, `firstname` FROM `chicken_shack_customers`";
    
    if($conn->multi_query($sql) === TRUE)
    {
        //1st Query
        $result = $conn->store_result();
        
        //Creates empty array to store Twilio numbers
        $twilio_numbers = array();
        
        while($row = $result->fetch_assoc())
        {
            $twilio_numbers[] = "+1".$row['from_number'];
        }
        
        $result->free();
        
         
        //2nd Query
        $conn->next_result();
        
        //Stores result of Customer_Business relationship cell numbers
        $result = $conn->store_result();
              
            if ($result->num_rows > 0) 
            {
                
                //Create a 2 dimensional array of arrays which will hold the Eligibile Customer Info
                $eligible_customers = array();
                
                while($row = $result->fetch_assoc())
                {
                    
                    
                    //Appends all eligible customer numbers from SQL query to array
                    $eligible_customers[] = array(
                        'to_number'=>$row['cell'],
                        'name'=>$row['firstname'],
                        
                    );
                }
                
                //Frees result variable from final SQL query
                $result->free();
                
                
                //Loop $eligible_customers and send SMS on each
                foreach($eligible_customers as $customer_row)
                {
                    $to_cell_number = $customer_row['to_number'];
                    //$to_number = "+1".$to_cell_number;
                    $to_number = $to_cell_number;
                    $name = $customer_row['name'];
                    
                    
                    
                    //Resets the Twilio Number Array index once index value out of range
                    if($i == count($twilio_numbers))
                    {
                        $i = 0;
                    }
                    
                    //Selects Twilio From number based on index position (i)
                    $from_number = $twilio_numbers[$i];
                    
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
                            'body' => "Hey $name, Happy Holidays from all of us at Chicken Shack!  Let’s make 2019 your best year! Sending our best wishes to you this holiday season! <If you enjoy getting GREAT deals DO NOT reply STOP to unsubscribe>",

                            'mediaUrl' => "http://chickenshack.rewardclub.us/images/holidays.gif"
                            )
                        );
                        
                        $i++;
                        
                        
                        
                    }
                    catch (Exception $e)
                    {
                        //blacklist_code = '21610'
                        $error_code = $e->getCode();
                        $error_message = $e->getMessage();
                        

                    }
                
                    
                }
                
                //Response message to send blast AJAX call
                echo "Messages have been sent";

              
            }
            else
            {
                echo "0 results";
                
                $conn->close();
            }
        
    }
    else
    {
        echo "Multi Query Failed";
        
        $conn->close();
    }
    


?>