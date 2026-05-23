if ('serviceWorker' in navigator) {                   // pwa.js registers service worker and shows install button
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js', { scope: './' })
      .then(reg => console.log('ServiceWorker registered:', reg.scope))
      .catch(err => console.error('ServiceWorker error:', err));
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
  if (!deferredPrompt) {
    alert("App installation is not available right now. You may have already installed the app, or your browser doesn't support this feature.");
    return;
  }
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

window.addEventListener('online', () => {
  const toast = document.getElementById('pwa-offline-toast');
  if (toast) { toast.style.opacity = '0'; toast.style.pointerEvents = 'none'; }
});
