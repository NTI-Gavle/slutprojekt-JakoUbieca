<div id="gdpr-cookie-banner" style="display: none; position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 700px; background: rgba(26, 21, 17, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 123, 0, 0.4); border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 20px rgba(255,123,0,0.2); z-index: 10000; color: #EAE4D9; font-family: 'Montserrat', sans-serif; text-align: center;">
    <h3 style="margin-top: 0; color: #FF7B00; font-size: 1.2rem;">We Value Your Privacy</h3>
    <p style="font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px; opacity: 0.9;">
        We use cookies and local storage to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.
        <a href="gdpr/privacy.php" style="color: #FF9B44; text-decoration: none; font-weight: bold;">Read our Privacy Policy</a>.
    </p>
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <button onclick="handleConsent('accepted')" style="background: #FF7B00; color: #000; border: none; padding: 10px 25px; border-radius: 12px; font-weight: bold; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(255,123,0,0.3);">Accept All</button>
        <button onclick="handleConsent('rejected')" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.3); padding: 10px 25px; border-radius: 12px; font-weight: bold; cursor: pointer; transition: 0.3s;">Reject Non-Essential</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!localStorage.getItem('cookieConsent')) {
        document.getElementById('gdpr-cookie-banner').style.display = 'block';
    }
});

function handleConsent(choice) {
    localStorage.setItem('cookieConsent', choice);
    const banner = document.getElementById('gdpr-cookie-banner');
    banner.style.opacity = '0';
    banner.style.transform = 'translate(-50%, 20px)';
    banner.style.transition = 'all 0.4s ease';
    setTimeout(() => { banner.style.display = 'none'; }, 400);
}
</script>
