$(document).ready(function documentIsReady() {
  // we can start writing here

  $(window).on("scroll", function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
      PageScrollDetector(); // call the function each time user scrolls
    }, 100); // debounce delay
  });

  PageScrollDetector();

  $("#goToLogin").on("click", function () {
    window.location.href = "./sign_in_up.php";
  });
});

let timer;

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
      console.log("In section: " + sectionId);
      if (sectionId) {
        $('nav a[href="#' + sectionId + '"]').addClass("active");
      }
    } else {
      $('nav a[href="#' + sectionId + '"]').removeClass("active");
    }
  });
}
