$(start);
function start() {
  $("#Add").click(function () {
    let firstInput = $("#A").val();
    let secondInput = $("#B").val();
    console.log(firstInput);
    console.log(secondInput);

    let output = parseInt(firstInput) + parseInt(secondInput);
    console.log(output);
    $("#Result").text(output);
  });
}
