'use strict';
// Comportamento do menu de navegação em todas as larguras.
const menuButton = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');
if (menuButton && menu) {
  menuButton.addEventListener('click', () => {
    const open = menu.classList.toggle('is-open');
    menuButton.setAttribute('aria-expanded', String(open));
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      menu.classList.remove('is-open');
      menuButton.setAttribute('aria-expanded', 'false');
      menuButton.focus();
    }
  });

  document.addEventListener('click', event => {
    if (!menu.contains(event.target) && !menuButton.contains(event.target)) {
      menu.classList.remove('is-open');
      menuButton.setAttribute('aria-expanded', 'false');
    }
  });

  menu.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      menu.classList.remove('is-open');
      menuButton.setAttribute('aria-expanded', 'false');
    });
  });
}
// Os filtros usam GET para permitir links compartilháveis e funcionamento sem JavaScript.
// Os controles de seleção são enviados automaticamente sem apagar o texto da busca.
const filterForm = document.querySelector('[data-filter-form]');
if (filterForm) {
  filterForm.querySelectorAll('select').forEach(select => {
    select.addEventListener('change', () => filterForm.requestSubmit());
  });
}
