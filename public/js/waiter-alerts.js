// Polls for new pending waiter calls and rings a bell when one arrives. There's no
// realtime broadcast (Reverb) wired yet, so this is a short-interval poll instead —
// honest about the limitation, but still gives a real audible alert.
(function () {
  const POLL_URL = window.ARIES_WAITER_CALLS_PENDING_URL;
  if (!POLL_URL) return;

  let lastSeenId = 0;
  let primed = false; // don't ring for calls that were already pending before this page loaded

  function playBell() {
    try {
      const Ctx = window.AudioContext || window.webkitAudioContext;
      const ctx = new Ctx();
      const now = ctx.currentTime;
      [880, 660].forEach((freq, i) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        const start = now + i * 0.18;
        gain.gain.setValueAtTime(0.0001, start);
        gain.gain.linearRampToValueAtTime(0.3, start + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.001, start + 0.4);
        osc.connect(gain).connect(ctx.destination);
        osc.start(start);
        osc.stop(start + 0.45);
      });
    } catch (e) {
      // Web Audio unavailable (or blocked until a user gesture) — fail silently.
    }
  }

  function updateBadge(count) {
    const badge = document.getElementById('waiter-calls-badge');
    if (!badge) return;
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
  }

  function poll() {
    fetch(POLL_URL, { headers: { Accept: 'application/json' } })
      .then(res => res.json())
      .then(data => {
        updateBadge(data.count);
        const newLatest = data.latestId || 0;
        if (primed && newLatest > lastSeenId) {
          playBell();
          if (typeof showToast === 'function') showToast('New waiter call!');
        }
        lastSeenId = Math.max(lastSeenId, newLatest);
        primed = true;
      })
      .catch(() => {});
  }

  poll();
  setInterval(poll, 8000);
})();
