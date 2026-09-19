// Customer app behavior. Cart contents live in sessionStorage as a placeholder so the
// demo stays interactive across real page loads — this gets replaced by real POST
// /order/cart/add + server-side session persistence once Controllers (brief Step 7) land.
// Namespaced by table UUID (window.ARIES_TABLE_UUID, set per-page) so one browser
// visiting two different tables doesn't mix their carts — the actual session auth is
// the separate httpOnly cookie, this is just a cache-key discriminator.
function cartKey() {
  return 'aries_customer_cart_' + (window.ARIES_TABLE_UUID || 'default');
}

function readCart() {
  try {
    return JSON.parse(sessionStorage.getItem(cartKey())) || [];
  } catch (e) {
    return [];
  }
}
function writeCart(cart) {
  sessionStorage.setItem(cartKey(), JSON.stringify(cart));
}

function cartQty(itemId) {
  const line = readCart().find(c => c.itemId === itemId);
  return line ? line.qty : 0;
}

function cartChangeQty(itemId, delta) {
  const cart = readCart();
  let line = cart.find(c => c.itemId === itemId);
  if (!line && delta > 0) {
    const el = document.querySelector(`.menuitem[data-id="${itemId}"]`);
    if (!el) return;
    line = { itemId, name: el.dataset.name, price: parseFloat(el.dataset.price), qty: 0, note: '' };
    cart.push(line);
  }
  if (line) {
    line.qty += delta;
    const updated = line.qty > 0 ? cart : cart.filter(c => c.itemId !== itemId);
    writeCart(updated);
  }
  renderQtySlot(itemId);
  updateStickyCart();
}

function renderQtySlot(itemId) {
  const slot = document.querySelector(`[data-qty-slot="${itemId}"]`);
  if (!slot) return;
  const q = cartQty(itemId);
  slot.innerHTML = q > 0
    ? `<div class="qtystepper"><button type="button" onclick="cartChangeQty(${itemId},-1)">−</button><span>${q}</span><button type="button" onclick="cartChangeQty(${itemId},1)">+</button></div>`
    : `<button type="button" class="addbtn" onclick="cartChangeQty(${itemId}, 1)">Add +</button>`;
}

function cartTotal() {
  return readCart().reduce((s, l) => s + l.price * l.qty, 0);
}
function cartCount() {
  return readCart().reduce((s, l) => s + l.qty, 0);
}

function updateStickyCart() {
  const n = cartCount();
  const amt = cartTotal();
  const sc = document.getElementById('stickycart');
  if (sc) {
    if (n > 0) {
      sc.classList.remove('hidden');
      document.getElementById('sticky-count').textContent = n + ' item' + (n > 1 ? 's' : '');
      document.getElementById('sticky-amt').textContent = '₹' + amt;
    } else {
      sc.classList.add('hidden');
    }
  }
  const badge = document.getElementById('cart-badge');
  if (badge) {
    if (n > 0) { badge.style.display = 'flex'; badge.textContent = n; }
    else badge.style.display = 'none';
  }
}

function setNote(itemId, val) {
  const cart = readCart();
  const line = cart.find(c => c.itemId === itemId);
  if (line) { line.note = val; writeCart(cart); }
}

function renderCart() {
  const el = document.getElementById('cart-content');
  if (!el) return;
  const cart = readCart();
  if (cart.length === 0) {
    el.innerHTML = `<div class="emptystate"><div class="em-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6L4 3H2"/></svg></div><h4>Your cart is empty</h4><p>Browse the menu and add a few dishes to get started.</p><a class="btn btn-primary" style="margin-top:18px; width:180px; display:inline-flex;" href="${window.ARIES_MENU_URL || '/order/menu'}">Browse menu</a></div>`;
    return;
  }
  const rows = cart.map(l => `
    <div class="cartline">
      <div class="menuitem-thumb" style="width:48px; height:48px; font-size:19px; background:var(--orange-tint);">🍽️</div>
      <div style="flex:1;">
        <div class="ci-name">${l.name}</div>
        <textarea placeholder="Add a note e.g. less spicy" style="margin-top:6px; font-size:11.5px; min-height:34px; padding:7px 10px;" oninput="setNote(${l.itemId}, this.value)">${l.note || ''}</textarea>
        <div class="ci-price">₹${l.price} × ${l.qty} = ₹${l.price * l.qty}</div>
      </div>
      <div class="qtystepper" style="align-self:flex-start;"><button type="button" onclick="cartChangeQty(${l.itemId},-1); renderCart();">−</button><span>${l.qty}</span><button type="button" onclick="cartChangeQty(${l.itemId},1); renderCart();">+</button></div>
    </div>`).join('');
  const subtotal = cartTotal();
  el.innerHTML = `${rows}
    <div style="margin-top:16px;">
      <div class="billrow"><span>Subtotal</span><span>₹${subtotal}</span></div>
      <div class="smallmute" style="text-align:left; margin-top:4px;">Taxes &amp; service charge are calculated when you place the order.</div>
    </div>
    <button type="button" class="btn btn-primary" id="place-order-btn" style="margin-top:16px;" onclick="placeOrder()">Place order</button>`;
}

function placeOrder() {
  const cart = readCart();
  if (cart.length === 0) return;

  const button = document.getElementById('place-order-btn');
  if (button) { button.disabled = true; button.textContent = 'Placing order…'; }

  fetch(window.ARIES_PLACE_ORDER_API_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      items: cart.map(l => ({ menu_item_id: l.itemId, quantity: l.qty, note: l.note || '' })),
    }),
  })
    .then(async (res) => {
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data.message || 'Could not place the order');
      writeCart([]);
      showToast(data.message || 'Order placed — kitchen has been notified');
      window.location.href = window.ARIES_PLACE_ORDER_URL || '/order/track';
    })
    .catch((err) => {
      showToast(err.message || "Couldn't place the order — please try again");
      if (button) { button.disabled = false; button.textContent = 'Place order'; }
    });
}

function openWaiterCallModal() {
  const overlay = document.getElementById('overlay-waitercall');
  if (!overlay) return;
  overlay.classList.add('active');
  overlay.innerHTML = `
    <div class="popcard">
      <div style="width:52px; height:52px; border-radius:50%; background:var(--orange-tint); color:var(--orange); display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
      </div>
      <h3>Call a waiter?</h3>
      <textarea id="waitercall-note" placeholder="Optional note, e.g. need extra napkins" style="margin:14px 0;"></textarea>
      <button type="button" class="btn btn-primary" id="waitercall-confirm" onclick="confirmWaiterCall()">Notify waiter</button>
      <button type="button" class="btn btn-outline" style="margin-top:9px;" onclick="closeAllOverlays()">Cancel</button>
    </div>`;
}
function confirmWaiterCall() {
  const note = document.getElementById('waitercall-note')?.value || '';
  const button = document.getElementById('waitercall-confirm');
  if (button) { button.disabled = true; button.textContent = 'Notifying…'; }

  fetch(window.ARIES_CALL_WAITER_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Accept': 'application/json',
    },
    body: JSON.stringify({ note }),
  })
    .then(res => res.ok ? res.json() : Promise.reject(res))
    .then(data => {
      closeAllOverlays();
      showToast(data.message || "Waiter notified — they're on the way");
    })
    .catch(() => {
      closeAllOverlays();
      showToast("Couldn't reach the waiter — please try again");
    });
}

function selectPayMethod(el) {
  el.parentElement.querySelectorAll('.filterchip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
  const cattabs = document.getElementById('cattabs');
  if (cattabs) {
    cattabs.addEventListener('click', (e) => {
      const tab = e.target.closest('.cattab');
      if (!tab) return;
      cattabs.querySelectorAll('.cattab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      applyMenuFilters();
    });
  }
  const filterrow = document.querySelector('.filterrow');
  if (filterrow) {
    filterrow.addEventListener('click', (e) => {
      const chip = e.target.closest('.filterchip');
      if (!chip) return;
      filterrow.querySelectorAll('.filterchip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      applyMenuFilters();
    });
  }
  if (document.getElementById('menu-list')) {
    readCart().forEach(l => renderQtySlot(l.itemId));
    updateStickyCart();
  }
  if (document.getElementById('cart-content')) renderCart();
});

function applyMenuFilters() {
  const activeCat = document.querySelector('.cattab.active')?.dataset.cat || 'all';
  const activeVeg = document.querySelector('.filterchip.active')?.dataset.veg || 'all';
  document.querySelectorAll('#menu-list .menuitem').forEach(item => {
    const matchesCat = activeCat === 'all' || item.dataset.cat === activeCat;
    const matchesVeg = activeVeg === 'all' || item.dataset.veg === activeVeg;
    item.style.display = matchesCat && matchesVeg ? 'flex' : 'none';
  });
}
