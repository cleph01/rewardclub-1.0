<?php

include 'config.php';
include 'class-db.php';

$store_id = trim($_GET["store_id"]);

//Get Business_id, Name, And Background Image
$table = "business";
$columns = array('business_id', 'business_name', 'background_url');
$where = 'unique_key';
    
    $result = $db->select($table, $columns, $where, $store_id);
    
    $row = $result->fetch_assoc();

        $business_id = $row['business_id'];
        $business_name = $row['business_name'];
        $background_url = $row['background_url'];

?>

<!DOCTYPE html>
<html>

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"> 

<meta name="apple-mobile-web-app-capable" content="yes">    
    
<head>
    
    <title><?php echo $business_name ?>'s Reward Club</title>
    
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    
    <!--Font Awesome-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    
    <!--Quicksand Font-->
    <link href='https://fonts.googleapis.com/css?family=Quicksand' rel='stylesheet'>
    
    <!--Local JQuery Script-->
    <script type="text/javascript" src="lib/tablet.js"></script>

    <!--Hides the Address Bar-->
    <script>

        $(document).ready(function(){
            window.addEventListener("load",function() {
                // Set a timeout...
                setTimeout(function(){
                    // Hide the address bar!
                    window.scrollTo(0, 1);
                }, 0);
            });
        });
    </script>
    
    <style>
        
    /*
        #bg-img{
            background-image:url('<?php echo $background_url ?>');
            }
      */  
    </style>
    
</head>    

<body>
    
<div id="bg-img" class="container">


<!--Begin Main Join/CheckIn Modal-->
    
<div id="main-screen" class="card shadow" style="display:none;">
        
    <div class="card-header">
        <button type="button" id="close-main-join" class="close">&times;</button> 

        <div id="response-msg"></div>
    </div>

    <div class="card-body">

        <div id="cell-input" class="form-group">
            
            <!--Tel Number Field Pops Up After User Clicks Check In Button-->
            <div class="input-group">
                <div class="input-group-prepend">
                    +1
                </div>
                    <input id="cell_number" type="tel" class="form-control" oninput="addHyphen()" maxlength="12" onkeyup="checkNum(this.value)" placeholder="Enter Your Cell"/>    <br>         
                    <label><small>e.g.</small> xxx-xxx-xxxx</label>
                    
            </div>    
            
            <!-- Sign up button when # NOT in db -->
            <button id="clear-btn">Clear</button>

        </div>

        <div id="sign-up" class="form-group" style="display:none;">
            
            <input id="first-name" class="w3-border w3-round" type="text" size="80" maxlength="20" onkeyup="checkName(this.value)" placeholder="Enter First Name" />

            <!-- Sign up button when # NOT in db -->
            <button id="sign-up-btn" style="display:none;">CLICK ME</button>
            
            <!-- Complete Signup New Customer -->
            <button id="complete-btn" onclick="backHome()">Complete Signup</button>

            <!-- Check In when # IS FOUND and Relationship DOES exist -->
            <button id="check-in-btn" style="display:none;">CLICK ME</button>

            <!-- Creates Relationship when # is found but Relationship DOES NOT exist -->
            <button id="assign_relationship">CLICK ME</button>
        </div>


    </div>
        

    
<!-- Begin Hidden Input Field to hold Business ID -->    
    
    <input type="hidden" id="business_id" value="<?php echo $business_id ?>" />
    
    <input type="hidden" id="business_name" value="<?php echo $business_name ?>" />
    
<!-- END Hidden Input Field to hold Business ID -->    
    

<!-- Closing DIV tag for main DIV container for webpage -->        
</div>   
    
    <!-- Begin Footer Container -->

    <div id="footer" class="fixed-bottom" style="text-align:center;">

            <button id="footer-btn" class="btn btn-primary" style="font-size:48px;color:#ffffff;">CLICK TO CHECK IN &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-angle-right" style="text-align:right;"></i></button>

    </div>
    
<!-- End Footer Container -->
    
</div>    
     

    
<script>

//////Handles Fullscreen Effect Button Behind QR Code    
var elem = document.getElementById("bg-img");

    function openFullscreen() {
        
      if (elem.requestFullscreen) {
        elem.requestFullscreen();
      } else if (elem.mozRequestFullScreen) { /* Firefox */
        elem.mozRequestFullScreen();
      } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
        elem.webkitRequestFullscreen();
      } else if (elem.msRequestFullscreen) { /* IE/Edge */
        elem.msRequestFullscreen();
      }
    }
    

//Enter key Event Listener
$(document).ready(function(){
    document.addEventListener("keydown", function(event) {
        if(event.which == 13)
        {
            signup();
            $('#cell_phone').blur();
        }
    });
});
    
</script>    
</body>    
</html>
