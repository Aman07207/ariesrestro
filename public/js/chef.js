// Chef app behavior (stock toggle). Advancing kitchen items is a Livewire action on
// Chef\QueueBoard; the server decides the legal next status from the item's current one.

function chefPost(url) {
  return fetch(url, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Accept': 'application/json',
    },
  }).then(async (res) => {
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Something went wrong — please try again');
    return data;
  });
}

function toggleStock(button, itemId) {
  button.disabled = true;
  chefPost(`${window.ARIES_STOCK_URL_BASE}/${itemId}/toggle`)
    .then((data) => {
      button.classList.toggle('btn-primary', data.is_available);
      button.classList.toggle('btn-outline', !data.is_available);
      button.textContent = data.is_available ? 'Mark unavailable' : 'Mark in stock';
      showToast(data.message);
    })
    .catch((err) => showToast(err.message))
    .finally(() => { button.disabled = false; });
}
