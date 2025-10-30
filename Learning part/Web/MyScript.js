/* 
$(document).ready(function documentIsReady(){
we can start writing here
});
  */
// or

$(start);

function start(){
    $("#increamentBtn").click(incrementBtnF)
}


function incrementBtnF(){
    let currentValue = $("#result").html();
    currentValue++;
    
    $("#result").html(currentValue)
}