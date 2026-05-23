<?php
session_start();
include "../php/lang_config.php";
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

$categories = [];      // load categories with thread and post count
$cat_sql = "SELECT fc.*, 
    (SELECT COUNT(*) FROM forum_threads ft WHERE ft.category_id = fc.id) AS thread_count,
    (SELECT COUNT(*) FROM forum_posts fp JOIN forum_threads ft2 ON fp.thread_id = ft2.id WHERE ft2.category_id = fc.id) AS post_count
    FROM forum_categories fc ORDER BY fc.sort_order ASC";
$cat_res = $conn->query($cat_sql);
while ($row = $cat_res->fetch_assoc()) {
    $categories[] = $row;
}

$notif_stmt = $conn->prepare("SELECT COUNT(*) FROM forum_notifications WHERE user_id = ? AND is_read = 0");  //notf
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_stmt->bind_result($unread_count);
$notif_stmt->fetch();
$notif_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Forum - Freaky Quiz</title>
    <meta name="theme-color" content="#FF7B00">
    <link rel="stylesheet" href="css/forum.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/intercom.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sky-bg"></div>
    <div class="grass-floor"></div>

    <div class="forum-container">

        <div class="forum-header" style="margin-bottom: 20px;">
            <h1><?php echo htmlspecialchars($lang['forum_community']); ?></h1>
            <div class="forum-nav">
                <a href="../hub.php" class="btn-forum" style="background: #111; color: white;"><?php echo htmlspecialchars($lang['forum_hub']); ?></a>
                <a href="../dashboard.php" class="btn-forum"><?php echo htmlspecialchars($lang['forum_dashboard']); ?></a>
                
                <div class="notif-wrapper">
                    <button class="btn-forum" id="notif-btn" onclick="toggleNotifications()">
                        <?php echo htmlspecialchars($lang['forum_notifications']); ?>
                    </button>
                    <?php if ($unread_count > 0): ?>
                        <div class="notif-badge" id="notif-badge"><?php echo $unread_count; ?></div>
                    <?php endif; ?>
                    <div class="notif-dropdown" id="notif-dropdown">
                        <div class="notif-empty">Loading...</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="three-globe-container" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1;"></div>

        <div class="intercom-hero" style="position: relative; min-height: 400px; background: transparent; border: none; box-shadow: none;">
            <div class="intercom-hero-content" style="position: relative; z-index: 2; pointer-events: none;">
                <h1><?php echo htmlspecialchars($lang['forum_hero_title']); ?></h1>
                <p><?php echo htmlspecialchars($lang['forum_hero_desc']); ?></p>
                <div class="intercom-search" style="pointer-events: auto;">
                    <input type="text" id="forum-search" placeholder="<?php echo htmlspecialchars($lang['forum_search_placeholder']); ?>" onkeyup="forumSearch(this.value)">
                    <div class="search-results" id="search-results" style="display: none; position: absolute; top: 100%; left: 0; width: 100%; background: var(--glass); backdrop-filter: blur(20px); z-index: 100; text-align: left; padding: 15px; border-radius: 15px; margin-top: 10px;"></div>
                </div>
            </div>
        </div>

        <div class="intercom-layout">
            <div class="intercom-sidebar">
                <div class="intercom-sidebar-title"><?php echo htmlspecialchars($lang['forum_quick_links']); ?></div>
                <a href="request_category.php" class="intercom-nav-link"><?php echo htmlspecialchars($lang['forum_request_category']); ?></a>
                <?php if ($is_admin == 1): ?>
                    <a href="admin.php" class="intercom-nav-link" style="color: var(--danger);"><?php echo htmlspecialchars($lang['forum_admin_panel']); ?></a>
                <?php endif; ?>
                
                <div class="intercom-sidebar-title" style="margin-top: 30px;"><?php echo htmlspecialchars($lang['forum_categories']); ?></div>
                <?php foreach ($categories as $cat): ?>
                    <a href="category.php?id=<?php echo $cat['id']; ?>" class="intercom-nav-link"><?php echo htmlspecialchars($cat['icon'] . ' ' . $cat['name']); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="intercom-main">
                <h2 style="color: #fff; margin-top: 0; margin-bottom: 20px; font-weight: 700;">Explore Categories</h2>
                
                <?php if (count($categories) > 0): ?>
                    <div class="category-grid">
                        <?php foreach ($categories as $cat): ?>
                            <a href="category.php?id=<?php echo $cat['id']; ?>" class="category-card" style="flex-direction: column; align-items: flex-start; text-align: left;">
                                <div class="category-icon" style="margin-bottom: 15px;"><?php echo htmlspecialchars($cat['icon']); ?></div>
                                <div class="category-info">
                                    <h3 style="font-size: 1.3rem; margin-bottom: 10px;"><?php echo htmlspecialchars($cat['name']); ?></h3>
                                    <p style="margin-bottom: 15px; min-height: 40px;"><?php echo htmlspecialchars($cat['description']); ?></p>
                                </div>
                                <div class="category-stats" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; width: 100%;">
                                    <span><?php echo $cat['thread_count']; ?> <?php echo htmlspecialchars($lang['forum_threads_count']); ?></span>
                                    <span><?php echo $cat['post_count']; ?> <?php echo htmlspecialchars($lang['forum_posts_count']); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="glass-panel" style="text-align: center;"><?php echo htmlspecialchars($lang['forum_no_categories']); ?></div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="js/forum.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        const container = document.getElementById('three-globe-container');
        if (container) {
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
            const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
            
            renderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(renderer.domElement);

            const geometry = new THREE.SphereGeometry(15, 32, 32);

            const material = new THREE.MeshBasicMaterial({ 
                color: 0xFF7B00, 
                wireframe: true,
                transparent: true,
                opacity: 0.15
            });
            
            const globe = new THREE.Mesh(geometry, material);
            scene.add(globe);

            const particlesGeometry = new THREE.BufferGeometry();
            const particlesCount = 300;
            const posArray = new Float32Array(particlesCount * 3);
            
            for(let i = 0; i < particlesCount * 3; i++) {
                posArray[i] = (Math.random() - 0.5) * 50;
            }
            
            particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
            const particlesMaterial = new THREE.PointsMaterial({
                size: 0.1,
                color: 0xFF7B00,
                transparent: true,
                opacity: 0.4
            });
            
            const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
            scene.add(particlesMesh);
           
            const innerGeo = new THREE.SphereGeometry(14.5, 32, 32);         // secondry sphere 
            const innerMat = new THREE.MeshBasicMaterial({ color: 0x26211C });
            const innerGlobe = new THREE.Mesh(innerGeo, innerMat);
            scene.add(innerGlobe);

            camera.position.z = 40;
            
            let mouseX = 0;
            let mouseY = 0;
            
            document.addEventListener('mousemove', (event) => {
                mouseX = (event.clientX / window.innerWidth) * 2 - 1;
                mouseY = -(event.clientY / window.innerHeight) * 2 + 1;
            });

            const animate = function () {
                requestAnimationFrame(animate);

                globe.rotation.y += 0.002;
                globe.rotation.x += 0.001;
                
                particlesMesh.rotation.y -= 0.001;

                camera.position.x += (mouseX * 5 - camera.position.x) * 0.05;  // mouse interaction
                camera.position.y += (mouseY * 5 - camera.position.y) * 0.05;
                camera.lookAt(scene.position);

                renderer.render(scene, camera);
            };

            animate();

            window.addEventListener('resize', () => {                      // handle window resize
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            });
        }
    </script>
    <?php include_once __DIR__ . "/../Rapport/ui.php"; ?>
</body>
</html>
