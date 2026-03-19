$(start);

function start() {
  let h1CounterTxt = $("<h1>").text("Active square number: ");
  $("body").append(h1CounterTxt);

  let h1Counter = $("<h1>").attr("id", "counter");
  $("body").append(h1Counter);
  for (i = 0; i < 6; i++) {
    $("body").append($("<div>").attr("class", "box"));
  }
  let counter = 0;
  $(".box").on("click", function () {
    if ($(this).hasClass("boxClick")) {
      $(this).removeClass("boxClick").addClass("boxNoClick");
      counter--;
    } else {
      $(this).removeClass("boxNoClick").addClass("boxClick");
      counter++;
    }
    $(h1Counter).text(counter);
  });
}
