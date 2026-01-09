$(start);
function start() {
  /* keep track of the html select tag */
  $("#Fruits").on("change", function () {
    /* getting value of select option */
    let selectedOptionValue = $(this).val();
    /* let selectedOptionValue = $("#Fruits").val(); */
    let data = {
      optionSelected: true,
      selectedOptionValue: selectedOptionValue,
    };
    $.post("./startup.php", data, function (htmlReply) {
      console.log(htmlReply);
    });
  });
}
