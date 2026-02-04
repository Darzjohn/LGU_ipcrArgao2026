// GLOBAL JAVASCRIPT - RPTMS V12

document.addEventListener("DOMContentLoaded", function () {
  // Sidebar toggle for small screens
  const sidebar = document.querySelector(".sidebar");
  const toggleBtn = document.querySelector("#menu-toggle");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("active");
    });
  }

  // Highlight active link in sidebar
  const currentPath = window.location.pathname.split("/").pop();
  document.querySelectorAll(".sidebar ul li a").forEach((link) => {
    if (link.getAttribute("href").includes(currentPath)) {
      link.classList.add("active");
    }
  });
});
