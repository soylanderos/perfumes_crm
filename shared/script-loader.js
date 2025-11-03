const page = document.body.dataset.page || "index";
const role = document.body.dataset.userRole || "GUEST";

const base_scripts = [
  "utilities/js/jquery.js",
  "utilities/JqueryUI/jquery-ui.min.js",
  "utilities/bootstrap/bootstrap.min.js",
  "utilities/sweetalert2/sweetalert2.min.js",
  "utilities/fontawesome/all.min.js",
  "utilities/evo-calendar/js/evo-calendar.js",
  "utilities/Chart.js/chart.umd.js",
];

const controller_scripts = [];

// Carga mínima para login
if (page === "login") {
  controller_scripts.push("modules/login/js/login_event_controller.js");
} else {
  // Carga de scripts comunes
  controller_scripts.push("shared/navigation/js/navigation_event_controller.js");
  controller_scripts.push("modules/dashboard/event/dashboard_event_controller.js");
  controller_scripts.push("modules/clients/event/clients_event_controller.js");
  controller_scripts.push("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css");


  // Carga de scripts específicos por rol
  if (role === "admin") {
    controller_scripts.push("modules/admin/event/admin_event_controller.js");
  }

  if (role === "PROJECT_MANAGER") {
    //controller_scripts.push("modules/clients/js/clients_event_controller.js");
    // no admin controller
  }

  // Puedes agregar más reglas por rol
}

const scripts = [...base_scripts, ...controller_scripts];

function loadScriptSequentially(src) {
  return new Promise((resolve, reject) => {
    const s = document.createElement("script");
    s.src = src;
    s.defer = true;
    s.onload = () => resolve();
    s.onerror = () => reject(new Error(`Script load error: ${src}`));
    document.head.appendChild(s);
  });
}

(async () => {
  for (let i = 0; i < scripts.length; i++) {
    await loadScriptSequentially(scripts[i]);
  }
})();
