document.addEventListener("DOMContentLoaded", function () {
  const page = document.body.dataset.page || "index";
  const role = document.body.dataset.userRole || "GUEST";

  const dynamic_styles = [];

  // Login solo usa sweetalert2 extra
  if (page === "login") {
    dynamic_styles.push("utilities/sweetalert2/sweetalert2.min.css");
  } else {
    dynamic_styles.push("utilities/JqueryUI/jquery-ui.min.css");
    dynamic_styles.push("utilities/sweetalert2/sweetalert2.min.css");
    dynamic_styles.push("utilities/evo-calendar/css/evo-calendar.css");
    dynamic_styles.push("utilities/evo-calendar/css/evo-calendar.royal-navy.css");
    dynamic_styles.push("utilities/entheo/entheo_styles.css");

    // Admin styles
      dynamic_styles.push("assets/css/admin.css");
      dynamic_styles.push("https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0");


    if (role === "admin") {
      

    }

    if (role === "PROJECT_MANAGER") {
      // PM styles
    }
  }

  dynamic_styles.forEach(href => {
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = href;
    document.head.appendChild(link);
  });

});
