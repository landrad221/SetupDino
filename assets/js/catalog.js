'use strict';
const menuButton = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');
if (menuButton && menu) {
  menuButton.addEventListener('click', () => {
    const open = menu.classList.toggle('is-open');
    menuButton.setAttribute('aria-expanded', String(open));
  });
}
// Filtering uses GET requests so URLs remain shareable and work without JS.
// Enhance select controls with auto-submit while preserving the search text.
const filterForm = document.querySelector('[data-filter-form]');
if (filterForm) {
  filterForm.querySelectorAll('select').forEach(select => {
    select.addEventListener('change', () => filterForm.requestSubmit());
  });
}
