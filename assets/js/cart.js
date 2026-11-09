(() => {
  'use strict';
  const KEY = 'SetupDino_cart_v1';
  function read() { try { const data=JSON.parse(localStorage.getItem(KEY)||'[]'); return Array.isArray(data)?data.filter(x=>Number.isInteger(x.id)&&x.id>0&&Number.isInteger(x.quantity)&&x.quantity>0&&x.quantity<=99).slice(0,50):[]; } catch { return []; } }
  function save(items) { localStorage.setItem(KEY, JSON.stringify(items)); document.dispatchEvent(new Event('cart:updated')); }
  const money = cents => new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(cents/100);
  function count() { document.querySelectorAll('[data-cart-count]').forEach(el=>el.textContent=read().reduce((n,x)=>n+x.quantity,0)); }
  function add(id,name,priceCents,stock) {
    const items=read(); let row=items.find(x=>x.id===id);
    if(row) { if(row.quantity>=stock) {alert('Você já atingiu o limite disponível em estoque.');return;} row.quantity++; }
    else { if(items.length>=50) {alert('Seu carrinho atingiu o limite de itens.');return;} items.push({id,name,priceCents,stock,quantity:1}); }
    save(items); alert('Produto adicionado ao carrinho!');
  }
  function render() {
    const root=document.querySelector('[data-cart-items]'); if(!root)return;
    const items=read(); root.replaceChildren();
    if(!items.length){const p=document.createElement('p');p.textContent='Seu carrinho está vazio.';root.append(p);}
    let total=0;
    items.forEach(item=>{
      const row=document.createElement('div');row.className='cart-line';
      const name=document.createElement('strong');name.textContent=item.name;
      const price=document.createElement('span');price.textContent=money(item.priceCents*item.quantity);total+=item.priceCents*item.quantity;
      const qty=document.createElement('input');qty.type='number';qty.min='1';qty.max=String(item.stock);qty.value=String(item.quantity);qty.setAttribute('aria-label','Quantidade de '+item.name);
      qty.addEventListener('change',()=>{const value=Number(qty.value);if(!Number.isInteger(value)||value<1||value>item.stock){qty.value=String(item.quantity);return;}item.quantity=value;save(items);render();});
      const remove=document.createElement('button');remove.type='button';remove.className='btn btn-outline';remove.textContent='Remover';remove.addEventListener('click',()=>{save(items.filter(x=>x.id!==item.id));render();});
      row.append(name,qty,price,remove);root.append(row);
    });
    const sum=document.querySelector('[data-cart-total]');if(sum)sum.textContent=money(total);
    const checkout=document.querySelector('[data-checkout-link]');if(checkout)checkout.hidden=!items.length;
  }
  document.addEventListener('click', e=>{const b=e.target.closest('[data-add-cart]');if(!b)return;add(Number(b.dataset.id),b.dataset.name,Number(b.dataset.priceCents),Number(b.dataset.stock));});
  document.addEventListener('cart:updated',()=>{count();render();});
  document.addEventListener('DOMContentLoaded',()=>{
    count();render();
    const checkout=document.querySelector('[data-checkout-form]');
    if(checkout){checkout.addEventListener('submit',e=>{const items=read();if(!items.length){e.preventDefault();alert('Seu carrinho está vazio.');return;}document.querySelector('[name="cart_json"]').value=JSON.stringify(items.map(({id,quantity})=>({id,quantity})));});}
    if(new URLSearchParams(location.search).has('ordered'))save([]);
  });
})();
