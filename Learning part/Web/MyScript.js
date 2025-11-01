$(document).ready(function documentIsReady() {
  // we can start writing here
  start();

  $(window).on("scroll", function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
      PageScrollDetector(); // call the function each time user scrolls
    }, 100); // debounce delay
  });

  PageScrollDetector();
});

function start() {
  $("#increamentBtn").click(incrementBtnF);
}

let timer;

function incrementBtnF() {
  let currentValue = $("#result").html();
  currentValue++;

  $("#result").html(currentValue);
}

function PageScrollDetector() {
  const scrollPosition = $(this).scrollTop();
  $("section").each(function () {
    let sectionId = $(this).attr("id");
    const sectionTop = $(this).offset().top;
    const sectionHeight = $(this).outerHeight();

    if (
      scrollPosition >= sectionTop - sectionHeight / 3 &&
      scrollPosition < sectionTop + sectionHeight - sectionHeight / 3
    ) {
      // Example: add a class or trigger animation
      $(this).addClass("active");
    } else {
      $(this).removeClass("active");
    }
  });
}
