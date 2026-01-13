$(start);
function start() {
  pageLoaded = {
    pageLoadedAll: true,
  };
  $.post(
    "./AhmAb795.php",
    pageLoaded,
    function (initialReply) {
      initialReply.forEach((row) => {
        let option = $("<option>")
          .attr("value", row.fruitId)
          .text(row.fruitName);
        $("#Fruits").append(option);
      });
    },
    "json"
  );
  /* keep track of the html select tag */
  $("#Fruits").on("change", function () {
    /* getting value of select option */
    let selectedOptionValue = $(this).val();
    /* let selectedOptionValue = $("#Fruits").val(); */
    let data = {
      optionSelected: true,
      selectedOptionValue: selectedOptionValue,
    };
    $.post("./AhmAb795.php", data, function (htmlReply) {
      console.log(htmlReply);
      /*   let result =
        JSON.parse(htmlReply) > 0
          ? "Quantity in stock: " + JSON.parse(htmlReply)
          : "Product out of stock"; */
      let result = JSON.parse(htmlReply);
      $("#FruitData").text(result.Quantity);
    });
  });
}
