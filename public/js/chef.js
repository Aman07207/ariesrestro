// Chef app behavior. Advancing an item's status and toggling stock are presentational
// only for now (toast + local DOM update) — real persistence lands in a later phase
// (brief Step 7), once auth + business-logic controllers are wired.

function advanceItem(button, itemId, newStatus) {
  const card = button.closest('.kancard');
  const tableLabel = card ? card.querySelector('.kt').textContent : '';
  showToast(tableLabel + ' — marked ' + newStatus);
  if (card) card.style.opacity = '.4';
}

function toggleStock(button, itemId) {
  const nowUnavailable = button.classList.contains('btn-primary');
  button.classList.toggle('btn-primary', !nowUnavailable);
  button.classList.toggle('btn-outline', nowUnavailable);
  button.textContent = nowUnavailable ? 'Mark unavailable' : 'Mark in stock';
}
