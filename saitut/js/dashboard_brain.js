document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('brain-canvas');
    if (!canvas || typeof THREE === 'undefined') return;

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x0a0a0a, 0.02);

    const wrapper = document.getElementById('central-wrapper');
    const width = wrapper.clientWidth;
    const height = wrapper.clientHeight;

    const camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 1000);
    camera.position.z = 35;

    const renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));


    const tunnelGroup = new THREE.Group();              // Anomaly Tunnel Background
    scene.add(tunnelGroup);


    const tunnelLength = 200;
    const tunnelSegments = 40;
    const tunnelGeo = new THREE.CylinderGeometry(40, 40, tunnelLength, 24, tunnelSegments, true);    // Constructed a cylindrical tunel with a solid dark void and a neon wireframe overlay 
    tunnelGeo.rotateX(Math.PI / 2);


    const tunnelMatSolid = new THREE.MeshBasicMaterial({
        color: 0x050505,
        side: THREE.BackSide
    });
    const tunnelSolid = new THREE.Mesh(tunnelGeo, tunnelMatSolid);
    tunnelGroup.add(tunnelSolid);


    const tunnelMatWire = new THREE.MeshBasicMaterial({
        color: 0xff6600,
        wireframe: true,
        transparent: true,
        opacity: 0.15,
        side: THREE.BackSide
    });
    const tunnelWire = new THREE.Mesh(tunnelGeo, tunnelMatWire);
    tunnelGroup.add(tunnelWire);


    const brainGroup = new THREE.Group();               // (3,2) Trefoil Knot   or simple the spining figure 
    scene.add(brainGroup);

    const brainGeo = new THREE.TorusKnotGeometry(9, 2.5, 300, 32, 2, 3);
    const brainMat = new THREE.MeshBasicMaterial({
        color: 0xff6600,
        wireframe: true,
        transparent: true,
        opacity: 0.4
    });
    const brainMesh = new THREE.Mesh(brainGeo, brainMat);
    brainGroup.add(brainMesh);

    const coreMat = new THREE.MeshBasicMaterial({
        color: 0xff3300,
        transparent: true,
        opacity: 0.1,
        blending: THREE.AdditiveBlending
    });
    const coreMesh = new THREE.Mesh(brainGeo, coreMat);
    coreMesh.scale.set(0.98, 0.98, 0.98);
    brainGroup.add(coreMesh);


    const nodes = [];
    const nodeGeo = new THREE.SphereGeometry(1.2, 16, 16);           // Interactive Nodes
    const nodeMat = new THREE.MeshBasicMaterial({ color: 0xffaa00 });

    function createNode(x, y, z, actionId) {
        const node = new THREE.Mesh(nodeGeo, nodeMat);             // Builded the central prop structure using a complex wireframe geometry and a glowing inner core.
        node.position.set(x, y, z);
        node.userData = { actionId: actionId };
        brainGroup.add(node);
        nodes.push(node);

        const nodeLight = new THREE.PointLight(0xffaa00, 2, 10);
        node.add(nodeLight);
    }

    // point 1 top left quizzes
    createNode(-9, 6, 4, 'main');
    // point 2 top right multiplayer
    createNode(9, 6, 4, 'multiplayer');
    // point 3 bottom center third placeholder
    createNode(0, -10, 4, 'third');


    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();                      // raycaster 
    let hoveredNode = null;

    wrapper.addEventListener('mousemove', (event) => {
        const rect = wrapper.getBoundingClientRect();
        mouse.x = ((event.clientX - rect.left) / width) * 2 - 1;
        mouse.y = -((event.clientY - rect.top) / height) * 2 + 1;      // track mouse movements to detect intersections with the nodes updating cursor styles and triggering actions on click.
    });

    wrapper.addEventListener('click', () => {
        if (hoveredNode && window.openPopup) {
            const actionId = hoveredNode.userData.actionId;
            openPopup(actionId);
        }
    });

    const clock = new THREE.Clock();           // animate function 

    function animate() {
        requestAnimationFrame(animate);

        const elapsedTime = clock.getElapsedTime();

        brainGroup.position.y = Math.sin(elapsedTime * 1.5) * 1.2;
        brainGroup.position.x = Math.cos(elapsedTime * 0.8) * 0.8;

        brainGroup.rotation.y = elapsedTime * 0.2;
        brainGroup.rotation.x = Math.sin(elapsedTime * 0.1) * 0.3;

        tunnelGroup.position.z += 0.2;
        if (tunnelGroup.position.z > 5) {
            tunnelGroup.position.z -= 5;
        }

        tunnelGroup.rotation.z = elapsedTime * 0.05;
        // execute the main render cycle apply floating physics rotate the central structure, scroll the tunnel and pulse the nodes

        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(nodes);

        if (intersects.length > 0) {
            if (hoveredNode !== intersects[0].object) {
                hoveredNode = intersects[0].object;
                wrapper.style.cursor = 'pointer';
                hoveredNode.scale.set(1.4, 1.4, 1.4);
            }
        } else {
            if (hoveredNode) {
                hoveredNode.scale.set(1, 1, 1);
                hoveredNode = null;
                wrapper.style.cursor = 'default';
            }
        }


        nodes.forEach(node => {
            if (node !== hoveredNode) {
                const scale = 1 + Math.sin(elapsedTime * 5 + node.position.x) * 0.1;
                node.scale.set(scale, scale, scale);
            }
        });

        renderer.render(scene, camera);
    }

    animate();


    window.addEventListener('resize', () => {               // resize handler
        const newWidth = wrapper.clientWidth;
        const newHeight = wrapper.clientHeight;
        camera.aspect = newWidth / newHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(newWidth, newHeight);
    });
});
