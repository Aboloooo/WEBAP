$(start);

function start() {
  /* creating template */
  template();
  $("#submit_form_btn").click(create_table);
}

function template() {
  let formTag = $("<form>");
  formTag.attr("method", "post");

  let inputTag1 = $("<input/>");
  inputTag1.attr("type", "number");
  inputTag1.attr("name", "targetRow");
  inputTag1.attr("id", "targetRow");
  inputTag1.attr("placeholder", "rows");

  let inputTag2 = $("<input/>");
  inputTag2.attr("type", "number");
  inputTag2.attr("name", "targetCol");
  inputTag2.attr("id", "targetCol");
  inputTag2.attr("placeholder", "columns");

  let inputTag3 = $("<input/>");
  inputTag3.attr("type", "number");
  inputTag3.attr("name", "targetX");
  inputTag3.attr("id", "targetX");
  inputTag3.attr("placeholder", "targetX");

  let inputTag4 = $("<input/>");
  inputTag4.attr("type", "number");
  inputTag4.attr("name", "targetY");
  inputTag4.attr("id", "targetY");
  inputTag4.attr("placeholder", "targetY");

  let input_submit = $("<input/>");
  input_submit.attr("type", "submit");
  input_submit.attr("id", "submit_form_btn");
  input_submit.attr("value", "click");

  formTag.append(inputTag1);
  formTag.append(inputTag2);
  formTag.append(inputTag3);
  formTag.append(inputTag4);
  formTag.append(input_submit);

  $("body").append(formTag);
}
function create_table() {
  let inputRowValue = $("#targetRow").val();
  let inputColValue = $("#targetCol").val();
  let inputTargetXValue = $("#targetX").val();
  let inputTargetYValue = $("#targetY").val();

  for (let i = 0; i < inputRowValue; i++) {
    console.log(" * ");
    for (let g = 0; g < inputColValue; g++) {
      console.log(" * ");
    }
  }

  /*  console.log(inputRowValue);
  console.log(inputColValue);
  console.log(inputTargetXValue);
  console.log(inputTargetYValue); */
}
