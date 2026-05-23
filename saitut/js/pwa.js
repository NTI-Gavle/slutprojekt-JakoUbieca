if ('serviceWorker' in navigator) {                   // pwa.js registers service worker and shows install button
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js', { scope: './' })
      .then(reg => console.log('ServiceWorker регистриран:', reg.scope))
      .catch(err => console.error('ServiceWorker грешка:', err));
  });
}

let deferredPrompt = null;

window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault();
  deferredPrompt = e;
  const btn = document.getElementById('pwa-install-btn');
  if (btn) btn.style.display = 'flex';
});

window.addEventListener('appinstalled', () => {
  const btn = document.getElementById('pwa-install-btn');
  if (btn) btn.style.display = 'none';
  deferredPrompt = null;
  console.log('PWA installed successfully');
});

function promptInstall() {
  if (!deferredPrompt) return;
  deferredPrompt.prompt();
  deferredPrompt.userChoice.then(choice => {
    console.log('User choice:', choice.outcome);
    deferredPrompt = null;
    const btn = document.getElementById('pwa-install-btn');
    if (btn) btn.style.display = 'none';
  });
}

window.addEventListener('offline', () => {
  const toast = document.getElementById('pwa-offline-toast');
  if (toast) { toast.style.opacity = '1'; toast.style.pointerEvents = 'auto'; }
});

function syncOfflineScores() {
  const scores = JSON.parse(localStorage.getItem('freaky_offline_scores') || '[]');
  if (scores.length === 0) return;

  Promise.all(scores.map(scoreData => {
    return fetch("php/save_score.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(scoreData)
    }).then(res => res.json());
  })).then(() => {
    localStorage.removeItem('freaky_offline_scores');
    console.log('Offline results synced successfully!');

    const toast = document.getElementById('pwa-offline-toast');
    if (toast) {
      toast.innerText = '✅ Offline results synced successfully!';
      toast.style.background = 'rgba(40, 167, 69, 0.9)';
      toast.style.borderColor = '#28a745';
      toast.style.opacity = '1';
      setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
          toast.innerText = '⚠️ No internet connection';
          toast.style.background = 'rgba(30,30,30,0.92)';
          toast.style.borderColor = 'rgba(255,100,100,0.4)';
        }, 500);
      }, 4000);
    }
  }).catch(err => console.error("Sync failed", err));
}

window.addEventListener('online', () => {
  const toast = document.getElementById('pwa-offline-toast');
  if (toast) { toast.style.opacity = '0'; toast.style.pointerEvents = 'none'; }
  syncOfflineScores();
});

window.addEventListener('load', () => {
  if (navigator.onLine) syncOfflineScores();
});
