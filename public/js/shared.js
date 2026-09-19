// Common helpers used by every role's pages (toast + modal overlay open/close).
function showToast(msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(showToast._timer);
  showToast._timer = setTimeout(() => t.classList.remove('show'), 2200);
}

function closeAllOverlays() {
  document.querySelectorAll('.overlay').forEach(o => o.classList.remove('active'));
}

document.addEventListener('click', (e) => {
  if (e.target.classList && e.target.classList.contains('overlay')) {
    e.target.classList.remove('active');
  }
});
