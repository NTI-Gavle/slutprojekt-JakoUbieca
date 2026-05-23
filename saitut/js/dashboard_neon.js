document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('bg-canvas');
    if (!canvas || typeof THREE === 'undefined') return;

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0a0a0a);
    scene.fog = new THREE.FogExp2(0x0a0a0a, 0.02);

    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.set(0, 15, 30);
    camera.lookAt(0, 0, 0);

    const renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const gridSize = 100;
    const gridDivisions = 50;
    const gridColor1 = 0x222222;
    const gridColor2 = 0x111111;

    const gridHelper = new THREE.GridHelper(gridSize, gridDivisions, gridColor1, gridColor2);
    gridHelper.position.y = -5;
    scene.add(gridHelper);

    const particleGeometry = new THREE.BufferGeometry();
    const particleCount = 200;
    const posArray = new Float32Array(particleCount * 3);
    for (let i = 0; i < particleCount * 3; i++) {
        posArray[i] = (Math.random() - 0.5) * 60;
    }
    particleGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
    const particleMaterial = new THREE.PointsMaterial({
        size: 0.1,
        color: 0xff6600,
        transparent: true,
        opacity: 0.5
    });
    const particlesMesh = new THREE.Points(particleGeometry, particleMaterial);
    scene.add(particlesMesh);

    const ambientLight = new THREE.AmbientLight(0xffffff, 0.2);
    scene.add(ambientLight);

    const mouseLight = new THREE.PointLight(0xff6600, 300, 50);
    mouseLight.position.set(0, 0, 0);
    scene.add(mouseLight);

    /**
     * initializes an interactive 3D WebGL background using Three.js
     * 
     * this script sets up a scene featuring:
     *  an infinitely scrolling ground grid with a depth fog effect.
     *  a field of floating, slowly rotating particles.
     *  an interactiv point light that smoothly tracks the users mouse movements 
     *   using raycasting against an invisible geometric plane.
     * a responsive animation loop that handles resizing and continuous movemen.
     */

    const mouse = new THREE.Vector2();
    const raycaster = new THREE.Raycaster();
    const planeGeo = new THREE.PlaneGeometry(200, 200);
    planeGeo.rotateX(-Math.PI / 2);
    const planeMat = new THREE.MeshBasicMaterial({ visible: false });
    const intersectPlane = new THREE.Mesh(planeGeo, planeMat);
    intersectPlane.position.y = -5;
    scene.add(intersectPlane);

    window.addEventListener('mousemove', (event) => {

        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObject(intersectPlane);

        if (intersects.length > 0) {
            const point = intersects[0].point;
            mouseLight.position.x += (point.x - mouseLight.position.x) * 0.1;
            mouseLight.position.z += (point.z - mouseLight.position.z) * 0.1;
            mouseLight.position.y = -4;
        }
    });

    const clock = new THREE.Clock();

    function animate() {
        requestAnimationFrame(animate);

        const elapsedTime = clock.getElapsedTime();

        particlesMesh.rotation.y = elapsedTime * 0.05;

        gridHelper.position.z = (elapsedTime * 2) % (gridSize / gridDivisions);

        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
});
