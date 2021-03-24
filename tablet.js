
// BEGIN HELPER FUNCTIONS

//------start---------



// Clear Button in Join Modal
//------start-------
$(document).ready(function(){
  $("#clear-btn").click(function(){
    
      $('#cell_number').val("");

    });
});
//------end-----------


//------start-------
// Adds hyphen to Number input field in Sign-up modal
function addHyphen(){
    
    var num = $("#cell_number").val();
        
    //if the number already has 3 or 7 digits, then add a hyphen to help with visual appearance of a phone #
    if(num.length == 3 || num.length == 7)
        {
          num = num + "-";

          $("#cell_number").val(num);      
        }        
}
        
//------end---------


// -----start---------
// Removes hyphen from number input field 
function removeHyphen(){
    
        var num = $("cell_number").val();
    
        //Variables for hyphen removal loop
        var test = num;
        var newString = "";
    
        //iterates through each letter of the number and creates a new string with those digits that arent hyphens.
        for(var i = 0; i < test.length; i++ )
            {
                var x = test.substring(i, i+1);

                if(x != "-")
                    {
                        newString = newString + x;
                    }
             }

            var num = newString;
    
            return num;
}

    // -----end---------


// END HELPER FUNCTIONS


// *** BEGIN REAL FUNCTIONS ***


//------BEGIN---------
//Controls Join Now button
$(document).ready(function(){
  $('#footer-btn').click(function(){
      
      $('#main-screen').show();
      $('#footer').hide();
      
  });
});

// -----END---------


//------BEGIN---------
//Controls Close button for main-join modal
$(document).ready(function(){
  $('#close-main-screen').click(function(){
      $('#main-screen').hide();
  });
});

// -----END---------


//------BEGIN---------
//Code executed when customer relationship exists and hits Check In Btn 

$(document).ready(function(){
  $("#check-in-main").click(function(){
      
                add_point();
                
                stopFiveSecTimeout();
                
                setFiveSecTimeout();
                
            });
});
//------END-----------


// -----BEGIN---------

//Sends the Check-In Number to the sever for processing
function add_point()
{     
    var num = removeHyphen();
    var business_id = $("#business_id").val().trim();
    
    $.post('add_point.php', {process:'add_point', cell_phone:num, business_id:business_id},
    function(data)
    {  
        $("#modal-welcome-back").show();
        $('#check-in').html(data);
        
        //setFiveSecTimeout();
        
    });        
}
// -----END-----------


//------BEGIN---------
//Code executed when customer NOT in DB at all and hits Signup Button 
$(document).ready(function(){
  $("#sign-up-btn").click(function(){
      
    var num = $("#cell_number").val();
           
        //Checks if Cell # input is empty
          if(num.length == 0)
                {
                    $('#response-msg').html("<h5 class='bg-danger text-white'>Please Enter a Number</h5>");
                    num = "";
                                        
                } 
                    // Checks if a complete cell # has been entered and if dashes are included
            else if (num.length < 12)
                {
                    $('#response-msg').html("<h5 class='bg-danger text-white'>Number is Incomplete</h5>");
                
                }

            else
            {
                newMember();

            }
            
        });
});
//------END-----------


// -----BEGIN---------

function newMember(){
    
    var business_id = $("#business_id").val().trim();
    
    var num = $("#cell_number").val();
    var firstname = $('#first-name').val();
    
    
        if(firstname == "" && num.length == 12)
            {
               $("#response-message").html("<h5>Please Enter Your First Name</h5>");
                
            }
            else if (firstname != "" && num.length == 12)
            {
                
                var num = removeHyphen();
                
                $.post('tablet_new_member.php', {process:'newMember', firstname:firstname, cell_phone:num, business_id:business_id},
                function(data)
                {

                    $('#response-message').html(data);

                    $("#finishSignup").show();
                    
                    setTenSecTimeout();
                    
                });
            }
}
        

// -----END-----------






//------BEGIN--------
// Executes Join AutoComplete Function on KeyUp for #Number input field
function checkNum(val){
        
    if(val.length == 12){
        
        if(val.substring(0,1) == "+" || val.substring(0,1) == 1)
            {
               
                $('#response-msg').html("<h5 class='bg-danger text-white'> Please re-enter your cell phone number without a 1 before the area code.</h5>");
                
                $("#cell_number").val('');
                
                $("#cell_number").focus();
            }
        else
            {
                    
            var num = removeHyphen();
                
            var business_id = $("#business_id").val().trim();
        
                $.ajax({
                    url:"tablet_autocomplete_opt_in.php",
                    method:"POST",
                    data:{process:'autocomplete', business_id_id: business_id, cell_phone: num},
                    success: function(data)
                    {
                      
                        $found = data;
                        
                        if($found.substring(1,2) == "i")
                            {
                                
                                $('#response_msg').html("<h5 class='bg-success text-white'>"+ data+"</h5>");
                                
                            }
                        else if ($found.substring(1,2) == "l")
                            {
                                
                                $('#response_msg').html("<h5 class='bg-success text-white'>"+ data+"</h5>");
                                
                            }
                        else if ($found.substring(1,2) == "e")
                            {
                                $('#response_msg').html("<h5 class='bg-success text-white'>"+ data+"</h5>");
                                
                            }
                        else if ($found.substring(1,2) == "o")
                            {
                
                               $('#response_msg').html("<h5 class='bg-success text-white'>"+ data+"</h5>");
                                
                            }
                        else 
                            {
                                $('#cell-input').hide();
                                
                                $('#response_msg').html("<h5 class='bg-success text-white'>"+ data+"</h5>");
                            }
                    }
                });
                
                document.activeElement.blur();
            }
        }
    }

//------ end -------

//------BEGIN--------
// Reveals SignUp button only after user starts typing their name
function checkName(val){
        
    //$('#main-join').css({'height':'250'});
    $('#sign-up-btn').show();
}

//------ end -------

//------BEGIN---------
//Code executed when person exists but new relationship 

$(document).ready(function(){
  $("#assign_relationship").click(function(){
      
    var num = removeHyphen();
      
    var business_id = $("#business_id").val().trim();
    
    var business_name = $("#business_name").val();
      
      $.ajax({
                url:"assign_relationship.php",
                method:"POST",
                data:{process: 'add_relationship', cell_phone:num, business_id: business_id, business_name:business_name},
                success: function(data)
                {
                    $('#response_msg').html("<h5 class='bg-success text-white'>"+ data+"</h5>");
                    
                    //setTimeout(function(){ backHome(); }, 3000);
                }
            });
      });
});
      
//----------end----------


      
      