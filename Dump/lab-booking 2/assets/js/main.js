// UniKLAB RCMP — Main JS

document.addEventListener('DOMContentLoaded', function () {

  // --- Mobile Hamburger ---
  const hamburger    = document.getElementById('hamburger');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const overlay      = document.getElementById('drawerOverlay');

  function openDrawer()  { mobileDrawer?.classList.add('open'); overlay?.classList.add('open'); }
  function closeDrawer() { mobileDrawer?.classList.remove('open'); overlay?.classList.remove('open'); }

  hamburger?.addEventListener('click', () => {
    mobileDrawer?.classList.contains('open') ? closeDrawer() : openDrawer();
  });
  overlay?.addEventListener('click', closeDrawer);

  // --- Flash message auto-dismiss ---
  const flash = document.querySelector('.flash-message');
  if (flash) setTimeout(() => flash.remove(), 5000);

  // --- Active nav auto-highlight (fallback) ---
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-link').forEach(link => {
    if (link.getAttribute('href') && currentPath.endsWith(link.getAttribute('href').replace(/^\//, ''))) {
      link.classList.add('active');
    }
  });

});
