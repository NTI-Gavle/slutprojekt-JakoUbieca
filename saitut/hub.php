<?php
session_start();
include "php/lang_config.php";
include "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

$menu_items = [
    ["title" => $lang['hub_quizzes'], "url" => "dashboard.php", "color" => 0xFF7B00],
    ["title" => $lang['hub_chat'], "url" => "chat_page.php", "color" => 0xB0986C],
    ["title" => $lang['hub_forum'], "url" => "forum/index.php", "color" => 0xFF7B00],
    ["title" => $lang['hub_profile'], "url" => "profile.php", "color" => 0xB0986C]
];

if ($is_admin == 1) {
    $menu_items[] = ["title" => $lang['hub_admin'], "url" => "admin/panel.php", "color" => 0xff4757];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freaky Quiz - WebGL Portal</title>
    <link rel="manifest" href="PWA/manifest.json">
    <meta name="theme-color" content="#FF7B00">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@900&display=swap" rel="stylesheet">
    <style>
        body, html { margin: 0; padding: 0; overflow: hidden; background-color: #050505; font-family: 'Inter', sans-serif; }
        #webgl-canvas { display: block; width: 100vw; height: 100vh; position: absolute; top: 0; left: 0; z-index: 10; cursor: grab; }
        #webgl-canvas:active { cursor: grabbing; }
        
        .top-bar {
            position: absolute; top: 0; left: 0; width: 100%; padding: 30px 50px;
            display: flex; justify-content: space-between; align-items: center; z-index: 50; box-sizing: border-box; pointer-events: none;
        }
        .logo { font-size: 1.5rem; font-weight: 900; letter-spacing: 3px; color: #FF7B00; pointer-events: auto; }
        .user-info { display: flex; gap: 20px; pointer-events: auto; color: #fff; font-size: 0.9rem; align-items: center; }
        .logout-btn { text-decoration: none; color: #B0986C; border: 1px solid #B0986C; padding: 5px 15px; border-radius: 50px; transition: 0.3s; }
        .logout-btn:hover { background: #B0986C; color: #000; }
        
        .bg-text-wrapper {
            position: absolute; top: 50%; left: 0; transform: translateY(-50%);
            width: 200%; display: flex; z-index: 1; pointer-events: none; opacity: 0.05;
        }
        .bg-text {
            font-size: 20vw; font-weight: 900; font-style: italic; color: #B0986C;
            white-space: nowrap; animation: scrollText 30s linear infinite; letter-spacing: -3px;
        }
        @keyframes scrollText { 0% { transform: translateX(0); } 100% { transform: translateX(-100%); } }

        .cursor-hint { position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); color: #fff; opacity: 0.5; z-index: 20; font-size: 0.8rem; letter-spacing: 2px; text-transform: uppercase; pointer-events: none; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="logo">FREAKYQUIZ</div>
        <div class="user-info">
            <?php echo strtoupper(htmlspecialchars($_SESSION['username'] ?? 'USER')); ?>
            <a href="php/logout.php" class="logout-btn"><?php echo htmlspecialchars($lang['logout']); ?></a>
        </div>
    </div>

    <div class="bg-text-wrapper">
        <div class="bg-text">FREAKY QUIZ • FREAKY FORUM • FREAKY MEDIA • </div>
        <div class="bg-text">FREAKY QUIZ • FREAKY FORUM • FREAKY MEDIA • </div>
    </div>

    <div class="cursor-hint"><?php echo htmlspecialchars($lang['hub_scroll_hint']); ?></div>
    
    <canvas id="webgl-canvas"></canvas>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/Observer.min.js"></script>

    <script>
        gsap.registerPlugin(Observer);

        const menuItems = <?php echo json_encode($menuItems = $menu_items); ?>;

        const config = {
            boxWidth: 5,
            boxHeight: 2,
            boxDepth: 5,
            spacing: 3.5,
            twistAngle: Math.PI / 3, 
            scrollDuration: 1.2,
            ease: "expo.out"
        };

        let activeIndex = 0;
        let isAnimating = false;

        const canvas = document.getElementById('webgl-canvas');
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(35, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.set(0, 0, 20);

        const renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;         // good shadows and highlights

        const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
        scene.add(ambientLight);

        const dirLight = new THREE.DirectionalLight(0xffffff, 1);
        dirLight.position.set(10, 10, 10);
        dirLight.castShadow = true;
        dirLight.shadow.mapSize.width = 1024;
        dirLight.shadow.mapSize.height = 1024;
        scene.add(dirLight);

        const fillLight = new THREE.PointLight(0xFF7B00, 0.8, 50);
        fillLight.position.set(-10, 0, 10);
        scene.add(fillLight);

        const rimLight = new THREE.PointLight(0xB0986C, 1, 50);
        rimLight.position.set(0, 10, -10);
        scene.add(rimLight);

        const totem = new THREE.Group();    // totem = group of blocks
        scene.add(totem);
        
        const blocks = [];
                                                     // array to store boxes for physics wave effect
        function createTextTexture(text, colorHex) {
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');
            
            ctx.fillStyle = 'rgba(0,0,0,0)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            ctx.fillStyle = '#' + colorHex.toString(16).padStart(6, '0');
            ctx.font = '900 120px Inter';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.shadowColor = 'rgba(0,0,0,0.5)';
            ctx.shadowBlur = 10;
            ctx.shadowOffsetY = 5;
            ctx.fillText(text, canvas.width / 2, canvas.height / 2);

            const texture = new THREE.CanvasTexture(canvas);
            texture.anisotropy = renderer.capabilities.getMaxAnisotropy();
            return texture;
        }

        const geometry = new THREE.BoxGeometry(config.boxWidth, config.boxHeight, config.boxDepth);  // blocks builds

        menuItems.forEach((item, i) => {
            const glassMaterial = new THREE.MeshPhysicalMaterial({
                color: item.color,
                metalness: 0.1,
                roughness: 0.15,
                transmission: 0.8, 
                thickness: 1.5,
                transparent: true,
                opacity: 0.9,
                side: THREE.DoubleSide
            });

            const blockGroup = new THREE.Group();
            const box = new THREE.Mesh(geometry, glassMaterial);
            box.castShadow = true;
            box.receiveShadow = true;
            blockGroup.add(box);

            const textGeo = new THREE.PlaneGeometry(config.boxWidth - 0.5, config.boxHeight - 0.5);
            const textMat = new THREE.MeshBasicMaterial({
                map: createTextTexture(item.title, 0xffffff),
                transparent: true,
                depthWrite: false
            });
            const textPlane = new THREE.Mesh(textGeo, textMat);
            textPlane.position.z = config.boxDepth / 2 + 0.02; 
            blockGroup.add(textPlane);
            blockGroup.position.y = -i * config.spacing;
            blockGroup.rotation.y = i * config.twistAngle;
            blockGroup.userData = { url: item.url, index: i };
            
            totem.add(blockGroup);
            blocks.push({ group: blockGroup, baseRotationY: blockGroup.rotation.y });
        });

        function updateTotemTransform() {                       // drag scroll navigate
            const targetY = activeIndex * config.spacing;
            const targetRotY = -activeIndex * config.twistAngle;

            gsap.to(totem.position, {
                y: targetY,
                duration: config.scrollDuration,
                ease: config.ease
            });

            gsap.to(totem.rotation, {
                y: targetRotY,
                duration: config.scrollDuration,
                ease: config.ease
            });
        }

        updateTotemTransform();

        function handleMove(direction) {
            if (direction > 0 && activeIndex < menuItems.length - 1) {
                activeIndex++;
                updateTotemTransform();
            } else if (direction < 0 && activeIndex > 0) {
                activeIndex--;
                updateTotemTransform();
            }
        }

       
        Observer.create({
            target: canvas,
            type: "wheel,touch,pointer",       //observer for Wheel/Touch/Drag
            wheelSpeed: -1,
            onDown: () => handleMove(-1),
            onUp: () => handleMove(1),
            tolerance: 30,
            preventDefault: true
        });

        const raycaster = new THREE.Raycaster();    // raycast for clicking 
        const mouse = new THREE.Vector2();

        window.addEventListener('click', (e) => {
            mouse.x = (e.clientX / window.innerWidth) * 2 - 1;
            mouse.y = -(e.clientY / window.innerHeight) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);
            const intersects = raycaster.intersectObjects(totem.children, true);
            
            if (intersects.length > 0) {
                let object = intersects[0].object;
                while (object.parent && object.parent !== totem) {
                    object = object.parent;
                }
                
                if (object.userData && object.userData.url) {
                    gsap.to(object.scale, { x: 0.9, y: 0.9, z: 0.9, duration: 0.1, yoyo: true, repeat: 1, onComplete: () => {
                        window.location.href = object.userData.url;
                    }});
                }
            }
        });

        window.addEventListener('mousemove', (e) => {        // hover effect
            mouse.x = (e.clientX / window.innerWidth) * 2 - 1;
            mouse.y = -(e.clientY / window.innerHeight) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);
            const intersects = raycaster.intersectObjects(totem.children, true);
            
            if (intersects.length > 0) {
                document.body.style.cursor = 'pointer';
            } else {
                document.body.style.cursor = 'default';
            }
        });

        const clock = new THREE.Clock(); 

        function animate() {                  // render the loops with fluid wave effects on individual blocks
            requestAnimationFrame(animate); 
            const time = clock.getElapsedTime();

            blocks.forEach((block, i) => {
                const waveOffset = Math.sin(time * 2 + i) * 0.05;
                const tiltOffset = Math.cos(time * 1.5 + i) * 0.02;
                
                block.group.rotation.x = tiltOffset;
                block.group.rotation.z = waveOffset;
            });

            totem.position.x = Math.sin(time * 0.5) * 0.2;

            renderer.render(scene, camera);
        }

        animate();
        
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

    </script>

    <button id="pwa-install-btn" onclick="promptInstall()" style="display:flex;position:fixed;bottom:20px;left:20px;z-index:9999;padding:12px 20px;background:#FF7B00;color:#000;border:none;border-radius:25px;font-size:0.95rem;font-weight:700;cursor:pointer;box-shadow:0 4px 20px rgba(255,102,0,0.5);">
        📲 Install App
    </button>
    <div id="pwa-offline-toast" style="display:flex;align-items:center;gap:10px;position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;background:rgba(0,0,0,0.8);backdrop-filter:blur(10px);color:#fff;padding:12px 24px;border-radius:20px;border:1px solid rgba(255,100,100,0.4);opacity:0;pointer-events:none;transition:opacity 0.4s;">
        ⚠️ No internet connection
    </div>

    <script src="js/pwa.js"></script>
    <?php include_once __DIR__ . "/Rapport/ui.php"; ?>
</body>
</html>
