// Shared realtime bootstrap for staff (private channels) and customers (public track
// channel). Reverb gives instant pushes; every Livewire screen also polls (wire:poll) so
// nothing goes stale if the socket is down. This file only builds Echo and shows the
// connection dot — pages subscribe through Livewire's #[On('echo...')] handlers.
(function () {
  const cfg = window.ARIES_RT;
  if (!cfg) return;

  // Loaded in <head> (Echo must exist before Livewire boots), so the dot may not be in the
  // DOM yet — look it up lazily and re-apply the last state once the page has parsed.
  let lastState = 'connecting';
  const setState = (state) => {
    lastState = state;
    const dot = document.getElementById('aries-rt-dot');
    if (!dot) return;
    dot.dataset.state = state;
    dot.title = state === 'live'
      ? 'Live updates on'
      : (state === 'connecting' ? 'Connecting…' : 'Live updates offline — refreshing every few seconds');
  };
  document.addEventListener('DOMContentLoaded', () => setState(lastState));
  setState('connecting');

  if (typeof Pusher === 'undefined' || typeof Echo === 'undefined') { setState('offline'); return; }

  try {
    window.Pusher = Pusher;
    window.Echo = new Echo.default({
      broadcaster: 'reverb',
      key: cfg.key,
      wsHost: cfg.host,
      wsPort: cfg.port,
      wssPort: cfg.port,
      forceTLS: cfg.scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      authEndpoint: cfg.authEndpoint,
      csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
    });
    const conn = window.Echo.connector.pusher.connection;
    conn.bind('connected', () => setState('live'));
    conn.bind('connecting', () => setState('connecting'));
    ['disconnected', 'unavailable', 'failed'].forEach((s) => conn.bind(s, () => setState('offline')));
  } catch (e) {
    setState('offline');
  }
})();
