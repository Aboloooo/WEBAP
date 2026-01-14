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
      let result = JSON.parse(htmlReply);
      let ProductId = result.ID;
      let Quantity = result.Quantity;

      let output =
        Quantity > 0
          ? "Quantity available: " + result.Quantity
          : "Product out of stock";
      $("#FruitData").text(output);

      $("#FruitOrder").empty();
      if (result.Quantity > 0) {
        /* 
        $("#FruitOrder").empty();
        if (result.Quantity > 0) {
          $("#FruitOrder").html(`
            <input type="text" id="orderInputQuantity" placeholder="Order Quantity">
            <button id="submitOrderBtn">Order</button>
          `);
        } 
        */
        let inputTag = $("<input>")
          .attr("type", "text")
          .attr("placeholder", "Order Quantity")
          .attr("id", "orderInputQantity");
        let submitOrderBtn = $("<button>")
          .text("Order")
          .attr("id", "submitOrderBtn");
        $("#FruitOrder").append(inputTag).append(submitOrderBtn);
        $("#submitOrderBtn").on("click", function () {
          let QunatityOrdered = $("#orderInputQantity").val();
          let QuantityLeft = Quantity - QunatityOrdered;
          if (QuantityLeft > 0) {
            alert("Order placed successfully ");
            return;
          } else if (QuantityLeft == 0) {
            alert("You have emptied our stock ");
            return;
          } else {
            alert("Please enter a valid order ");
            return;
          }
          let data1 = {
            btnClicked: true,
            ProductId: ProductId,
            newQuantity: QuantityLeft,
          };
          $.post("./AhmAb795.php", data1, function (htmlReply1) {
            console.log(htmlReply1);
          });
        });
      } else {
        $("#FruitOrder").empty();
      }
    });
  });
}
