// Plays a bell the moment Livewire tells us a new waiter call arrived
// (dispatched from App\Livewire\Waiter\CallsBadge / CallsIndex when the
// Reverb broadcast for WaiterCallCreated lands). Replaces the old ~8s poll.
(function () {
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

  document.addEventListener('livewire:initialized', function () {
    Livewire.on('waiter-call-received', function () {
      playBell();
      if (typeof showToast === 'function') showToast('New waiter call!');
    });
  });
})();
