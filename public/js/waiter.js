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

function submitManualOrder() {
  const tableSelect = document.getElementById('manual-table');
  const label = tableSelect ? tableSelect.options[tableSelect.selectedIndex].text : '';
  showToast('Item added to ' + label + ' order');
}
