// Notification tones + attention helpers. Browsers block audio until the user has
// interacted with the page, so ONE AudioContext is created up front and resumed on the
// first tap/click/key — an "Enable sound" chip stays visible until that has worked.
// Livewire components fire the `aries-notify` browser event; this file plays the tone.
(function () {
  const MUTE_KEY = 'aries_muted';
  let ctx = null;
  let unlocked = false;
  let unread = 0;
  const baseTitle = document.title;
  let flashTimer = null;

  const isMuted = () => { try { return localStorage.getItem(MUTE_KEY) === '1'; } catch (e) { return false; } };
  const setMuted = (v) => { try { localStorage.setItem(MUTE_KEY, v ? '1' : '0'); } catch (e) { /* private mode */ } };

  function ensureCtx() {
    if (!ctx) {
      const C = window.AudioContext || window.webkitAudioContext;
      if (!C) return null;
      ctx = new C();
    }
    return ctx;
  }

  function renderChip() {
    const btn = document.getElementById('aries-rt-sound');
    if (!btn) return;
    if (!unlocked) { btn.textContent = '🔔 Tap to enable sound'; btn.dataset.state = 'locked'; }
    else if (isMuted()) { btn.textContent = '🔕 Muted'; btn.dataset.state = 'muted'; }
    else { btn.textContent = '🔔 Sound on'; btn.dataset.state = 'on'; }
  }

  function unlock() {
    const c = ensureCtx();
    if (!c || unlocked) return;
    c.resume().then(() => {
      if (c.state === 'running') {
        // A silent blip fully unlocks iOS Safari.
        const o = c.createOscillator();
        const g = c.createGain();
        g.gain.value = 0.0001;
        o.connect(g); g.connect(c.destination);
        o.start(); o.stop(c.currentTime + 0.02);
        unlocked = true;
        renderChip();
      }
    }).catch(() => {});
  }
  ['pointerdown', 'touchstart', 'keydown', 'click'].forEach((ev) => document.addEventListener(ev, unlock, { passive: true }));

  // [frequency Hz, start s, duration s, peak gain]
  const TONES = {
    // Chef: ~2s attention pattern, four alternating pulses.
    chefOrder: [[880, 0, 0.35, 0.5], [1175, 0.5, 0.35, 0.5], [880, 1.0, 0.35, 0.5], [1175, 1.5, 0.45, 0.5]],
    // Waiter: a new order landed (~1s).
    waiterOrder: [[660, 0, 0.25, 0.4], [880, 0.3, 0.25, 0.4], [1100, 0.6, 0.4, 0.4]],
    // Waiter: chef marked something ready — distinct two-note rising chime.
    ready: [[988, 0, 0.3, 0.45], [1319, 0.35, 0.6, 0.45]],
    // Waiter call bell (kept from the original).
    call: [[880, 0, 0.3, 0.4], [660, 0.18, 0.5, 0.4]],
    // Customer: soft chime.
    customer: [[784, 0, 0.25, 0.2], [988, 0.25, 0.45, 0.2]],
  };

  function play(name) {
    if (isMuted()) return;
    const c = ensureCtx();
    const pattern = TONES[name];
    if (!c || !pattern) return;
    const go = () => {
      const t0 = c.currentTime + 0.02;
      pattern.forEach(([freq, start, dur, peak]) => {
        const osc = c.createOscillator();
        const gain = c.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, t0 + start);
        gain.gain.exponentialRampToValueAtTime(peak, t0 + start + 0.03);
        gain.gain.exponentialRampToValueAtTime(0.0001, t0 + start + dur);
        osc.connect(gain); gain.connect(c.destination);
        osc.start(t0 + start); osc.stop(t0 + start + dur + 0.05);
      });
    };
    if (c.state === 'suspended') { c.resume().then(go).catch(() => {}); } else { go(); }
  }

  function attention() {
    if (navigator.vibrate) { try { navigator.vibrate([200, 100, 200]); } catch (e) { /* unsupported */ } }
    if (!document.hidden) return;
    unread += 1;
    clearInterval(flashTimer);
    let on = false;
    flashTimer = setInterval(() => {
      on = !on;
      document.title = on ? '(' + unread + ') 🔔 New activity' : baseTitle;
    }, 1000);
  }
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) { unread = 0; clearInterval(flashTimer); document.title = baseTitle; }
  });

  function notify(detail) {
    const d = Array.isArray(detail) ? detail[0] : detail;
    if (!d) return;
    if (d.tone) play(d.tone);
    if (d.message && typeof showToast === 'function') showToast(d.message);
    attention();
  }

  window.addEventListener('aries-notify', (e) => notify(e.detail));
  // The original waiter-call event still rings the bell.
  document.addEventListener('livewire:initialized', () => {
    if (window.Livewire) window.Livewire.on('waiter-call-received', () => notify({ tone: 'call', message: 'New waiter call!' }));
  });

  const btn = document.getElementById('aries-rt-sound');
  if (btn) {
    btn.addEventListener('click', () => {
      if (!unlocked) { unlock(); setTimeout(renderChip, 150); return; }
      setMuted(!isMuted());
      renderChip();
      if (!isMuted()) play('customer');
    });
  }
  renderChip();
  window.AriesNotify = { play, unlock };
})();
