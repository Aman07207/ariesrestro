// Waiter app behavior. Cancel/transfer/attend/manual-order actions are presentational
// only for now (toast feedback, no persistence) — real business logic + auth land in a
// later phase (brief Steps 6/7). Table grid / detail data itself is real, server-rendered.

function openCancelModal(itemId) {
  const overlay = document.getElementById('overlay-cancel');
  if (!overlay) return;
  overlay.classList.add('active');
  overlay.innerHTML = `
    <div class="popcard">
      <h3 style="text-align:center; margin-bottom:14px;">Cancel this item?</h3>
      <label class="flabel">Cancellation reason</label>
      <select id="cancel-reason"><option>Customer request</option><option>Item unavailable</option><option>Kitchen error</option><option>Other</option></select>
      <label class="flabel">Additional note (optional)</label>
      <textarea id="cancel-note"></textarea>
      <button type="button" class="btn btn-primary" style="margin-top:16px;" onclick="confirmCancel(${itemId})">Confirm cancellation</button>
      <button type="button" class="btn btn-outline" style="margin-top:9px;" onclick="closeAllOverlays()">Go back</button>
    </div>`;
}
function confirmCancel(itemId) {
  const reason = document.getElementById('cancel-reason')?.value || 'Other';
  const note = document.getElementById('cancel-note')?.value || '';

  fetch(`${window.ARIES_ORDER_ITEMS_URL_BASE}/${itemId}/cancel`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Accept': 'application/json',
    },
    body: JSON.stringify({ reason, note }),
  })
    .then(async (res) => {
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data.message || 'Could not cancel this item');
      closeAllOverlays();
      showToast(data.message || 'Item cancelled');
      window.location.reload();
    })
    .catch((err) => {
      closeAllOverlays();
      showToast(err.message || "Couldn't cancel this item");
    });
}

function openTransferModal() {
  const overlay = document.getElementById('overlay-transfer');
  if (!overlay) return;
  overlay.classList.add('active');
  overlay.innerHTML = `
    <div class="popcard">
      <h3 style="text-align:center; margin-bottom:14px;">Change table</h3>
      <p style="font-size:12px; color:var(--navy-soft); text-align:center; margin-bottom:14px;">Move this active session to a new table. Customers won't need to rescan.</p>
      <label class="flabel">Reason</label>
      <input type="text" id="transfer-reason" placeholder="e.g. bigger table needed">
      <button type="button" class="btn btn-primary" style="margin-top:16px;" onclick="confirmTransfer()">Confirm transfer</button>
      <button type="button" class="btn btn-outline" style="margin-top:9px;" onclick="closeAllOverlays()">Cancel</button>
    </div>`;
}
function confirmTransfer() {
  closeAllOverlays();
  showToast('Table changed — session moved successfully');
}

function confirmBill() {
  showToast('Table marked paid & freed up');
  setTimeout(() => { window.location.href = window.ARIES_TABLES_URL || '/waiter/tables'; }, 600);
}

// Manual order: lines are built locally, then sent in one POST. Only ids/qty/notes go up —
// the server prices everything itself.
const manualLines = [];

function renderManualLines() {
  const box = document.getElementById('manual-lines');
  if (!box) return;
  box.textContent = '';
  if (manualLines.length === 0) {
    const p = document.createElement('p');
    p.className = 'smallmute';
    p.style.textAlign = 'left';
    p.textContent = 'Nothing added yet.';
    box.appendChild(p);
    return;
  }
  manualLines.forEach((line, i) => {
    const row = document.createElement('div');
    row.className = 'orderitem';
    const left = document.createElement('div');
    const name = document.createElement('div');
    name.className = 'oi-name';
    name.textContent = line.name; // textContent, never innerHTML — names/notes are untrusted text
    const meta = document.createElement('div');
    meta.className = 'oi-qty';
    meta.textContent = 'Qty ' + line.quantity + (line.note ? ' · ' + line.note : '');
    left.append(name, meta);
    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'iconbtn';
    remove.style.cssText = 'width:26px; height:26px;';
    remove.textContent = '×';
    remove.onclick = () => { manualLines.splice(i, 1); renderManualLines(); };
    row.append(left, remove);
    box.appendChild(row);
  });
}

function addManualLine() {
  const select = document.getElementById('manual-item');
  const opt = select.options[select.selectedIndex];
  if (!opt) return;
  const quantity = Math.max(1, Math.min(20, parseInt(document.getElementById('manual-qty').value, 10) || 1));
  manualLines.push({
    menu_item_id: parseInt(select.value, 10),
    name: opt.dataset.name,
    quantity,
    note: document.getElementById('manual-note').value.trim(),
  });
  document.getElementById('manual-qty').value = 1;
  document.getElementById('manual-note').value = '';
  renderManualLines();
}

function submitManualOrder() {
  if (manualLines.length === 0) { showToast('Add at least one item first'); return; }
  const button = document.getElementById('manual-send');
  button.disabled = true;

  fetch(window.ARIES_MANUAL_ORDER_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      table_id: parseInt(document.getElementById('manual-table').value, 10),
      items: manualLines.map(l => ({ menu_item_id: l.menu_item_id, quantity: l.quantity, note: l.note })),
    }),
  })
    .then(async (res) => {
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data.message || 'Could not place the order');
      showToast(data.message);
      setTimeout(() => { window.location.href = data.redirect; }, 700);
    })
    .catch((err) => {
      showToast(err.message);
      button.disabled = false;
    });
}
