<?php

class SendTwilio{
    
    function sms($to_number, $from_number, $msg_type, $business_name, $business_id){
    
        switch ($msg_type){

            case "redeem":

                //generates random 4-digit redeemption code for use in Redemption text
                /*
                $redeemCode = rand(1000, 9999);
                $redeemCode = strval($redeemCode);
                //sets the expiration date
                $d=strtotime("+1 week");
                $exp = date("M-d-Y", $d);
                */

                $msg = "Hi, from $business_name: Congratulations! Click the following link to Redeem, Share, or Trade your Prize. Enjoy! -> https://letsplay.socialiite.io/wallet.php";


                //Instantiates Twilio Message sender
                $sms = $client->account->messages->create(

                // the number we are sending to - Any phone number
                $to_number,

                array(
                    // Step 6: Change the 'From' number below to be a valid Twilio number 
                    // that you've purchased
                    'from' => $from_number, 

                            // the sms body
                            'body' => "$msg"
                            )
                        );

                break;    
           
        } 
    }
}

$sendTwil = new SendTwilio();
?>