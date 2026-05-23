<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$current_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

function getLangUrl($lang) {
    global $current_url;
    $parsed_url = parse_url($current_url);
    parse_str($parsed_url['query'] ?? '', $query_params);
    $query_params['lang'] = $lang;
    $new_query = http_build_query($query_params);
    return $parsed_url['path'] . '?' . $new_query;
}
?>
<style>
.lang-switcher { position: fixed; top: 20px; right: 20px; z-index: 1000; }
.lang-btn { background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 50px; color: white; padding: 8px 15px; font-size: 0.85rem; font-family: 'Segoe UI', Montserrat, sans-serif; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px; transition: 0.3s; text-transform: uppercase; }
.lang-btn:hover { background: rgba(255, 255, 255, 0.3); }
.lang-dropdown { position: absolute; top: 110%; right: 0; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 15px; display: flex; flex-direction: column; overflow: hidden; opacity: 0; pointer-events: none; transform: translateY(-10px); transition: 0.3s; min-width: 130px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
.lang-switcher:hover .lang-dropdown { opacity: 1; pointer-events: auto; transform: translateY(0); }
.lang-dropdown a { color: white; text-decoration: none; padding: 12px 15px; font-size: 0.85rem; font-family: 'Segoe UI', Montserrat, sans-serif; transition: 0.2s; display: flex; align-items: center; gap: 10px; }
.lang-dropdown a:hover { background: rgba(255, 255, 255, 0.2); }
</style>
<div class="lang-switcher">
    <button class="lang-btn">🌍 <?php echo strtoupper($current_lang); ?></button>
    <div class="lang-dropdown">
        <a href="<?php echo getLangUrl('en'); ?>">🇬🇧 English</a>
        <a href="<?php echo getLangUrl('sv'); ?>">🇸🇪 Svenska</a>
    </div>
</div>
