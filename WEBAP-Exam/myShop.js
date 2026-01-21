$(start);
function start() {
  $.post(
    "./AhmAb795.php",
    { pageloaded: true },
    function (initialReply) {
      $("#items").remove();
      let tbody = $("<tbody>");
      initialReply.forEach((row) => {
        let option = $("<option>").attr("value", row.itemId).text(row.itemName);
        $("#itemList").append(option);

        let tr = $("<tr>");
        tr.append($("<td>").text(row.itemName));
        tr.append($("<td>").text(row.itemStock));
        tbody.append(tr);

        /*  $("#items").append(row.itemName);
        $("#orders").append(row.itemStock); */
      });
      $("#items").append(tbody);
    },
    "json",
  );
  $("#Order").on("click", function () {
    let inputQuantity = $("#Quantity").val();
    let selectedOption = $("#itemList").val();
    $.post(
      "./AhmAb795.php",
      {
        selectedOption: selectedOption,
        inputQuantity: inputQuantity,
      },
      function (initialReply) {},
    );
  });
}
