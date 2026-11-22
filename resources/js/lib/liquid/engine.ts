import * as THREE from 'three';

/**
 * The site's single WebGL renderer.
 *
 * Every liquid image and hero blob on the page draws through this one
 * context: the renderer works offscreen, and each item copies its frame into
 * its own 2D <canvas> that sits in normal document flow. One GPU context for
 * the whole site, while captions, badges and stacking order keep working as
 * plain HTML on top of the images.
 *
 * Items only render while on screen. Images redraw only while something is
 * moving (hover, the pointer, scroll velocity); blobs animate continuously
 * while visible. Everything stops when the tab is hidden.
 */

type Kind = 'image' | 'blob';

type Item = {
    kind: Kind;
    host: HTMLElement;
    surface: HTMLCanvasElement;
    context: CanvasRenderingContext2D;
    material: THREE.ShaderMaterial;
    visible: boolean;
    ready: boolean;
    dirty: boolean;
    hover: number;
    hoverTarget: number;
    mouse: THREE.Vector2;
    mouseTarget: THREE.Vector2;
    /** Device pixels per CSS pixel for this item's surface. */
    resolution: number;
    texture?: THREE.Texture;
    onReady: () => void;
    cleanup: (() => void)[];
};

export type LiquidHandle = { destroy: () => void };

const MAX_DPR = 1.5;

const vertexShader = /* glsl */ `
    varying vec2 vUv;

    void main() {
        vUv = uv;
        gl_Position = vec4(position.xy, 0.0, 1.0);
    }
`;

const imageFragment = /* glsl */ `
    uniform sampler2D uTexture;
    uniform vec4 uCover;      // xy scale, zw offset (object-fit: cover)
    uniform vec2 uMouse;
    uniform float uHover;
    uniform float uVelocity;
    uniform float uTime;
    varying vec2 vUv;

    void main() {
        vec2 uv = vUv;

        // Ripple spreading from the pointer while hovered.
        vec2 toMouse = uv - uMouse;
        float d = length(toMouse);
        float ripple = sin(d * 30.0 - uTime * 6.0) * 0.012 * uHover * smoothstep(0.55, 0.0, d);
        uv += normalize(toMouse + 1e-5) * ripple;

        // A liquid wave whose strength follows the scroll speed.
        uv.y += sin(uv.x * 6.2831 + uTime * 2.0) * 0.02 * uVelocity;
        uv.x += sin(uv.y * 4.0 + uTime) * 0.006 * uVelocity;

        // A slow push-in on hover.
        uv = (uv - 0.5) * (1.0 - 0.07 * uHover) + 0.5;

        vec2 cuv = uv * uCover.xy + uCover.zw;
        float shift = 0.004 * uHover + 0.012 * abs(uVelocity);

        float r = texture2D(uTexture, cuv + vec2(shift, 0.0)).r;
        float g = texture2D(uTexture, cuv).g;
        float b = texture2D(uTexture, cuv - vec2(shift, 0.0)).b;

        gl_FragColor = vec4(r, g, b, 1.0);
    }
`;

const blobFragment = /* glsl */ `
    uniform vec3 uColorA;
    uniform vec3 uColorB;
    uniform vec3 uColorC;
    uniform vec2 uAspect;
    uniform vec2 uMouse;
    uniform float uTime;
    varying vec2 vUv;

    // Cheap value noise, enough for a smooth liquid field.
    float hash(vec2 p) { return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }
    float noise(vec2 p) {
        vec2 i = floor(p);
        vec2 f = fract(p);
        vec2 u = f * f * (3.0 - 2.0 * f);
        return mix(mix(hash(i), hash(i + vec2(1.0, 0.0)), u.x),
                   mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), u.x), u.y);
    }

    void main() {
        vec2 p = (vUv - 0.5) * uAspect;
        float t = uTime * 0.25;

        // Domain-warped field so the shape flows like liquid.
        vec2 warp = vec2(noise(p * 1.6 + t), noise(p * 1.6 - t + 4.0)) - 0.5;
        vec2 q = p + warp * 0.55 + (uMouse - 0.5) * 0.08;

        // Three drifting metaballs.
        float field = 0.0;
        field += 0.085 / length(q - vec2(sin(t * 1.3) * 0.18, cos(t * 1.1) * 0.14));
        field += 0.065 / length(q - vec2(cos(t * 0.9) * 0.22 + 0.06, sin(t * 1.7) * 0.16));
        field += 0.055 / length(q - vec2(sin(t * 2.1 + 1.0) * 0.16 - 0.06, cos(t * 1.4) * 0.18));

        float body = smoothstep(1.0, 1.06, field);
        float rim = smoothstep(0.9, 1.0, field) - body;

        // Colour runs across the body with the warp, rim catches light.
        float tone = clamp(0.5 + warp.x * 1.4 + q.y * 0.6, 0.0, 1.0);
        vec3 color = mix(uColorA, uColorB, tone);
        color = mix(color, uColorC, rim * 0.6);

        // Fade out toward every edge so the shape is never clipped flat.
        float edge = smoothstep(0.0, 0.18, vUv.x) * smoothstep(1.0, 0.82, vUv.x)
                   * smoothstep(0.0, 0.18, vUv.y) * smoothstep(1.0, 0.82, vUv.y);
        float alpha = clamp(body + rim * 0.55, 0.0, 1.0) * edge;
        gl_FragColor = vec4(color * alpha, alpha);
    }
`;

let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene;
let camera: THREE.Camera;
let mesh: THREE.Mesh;
let glWidth = 0;
let glHeight = 0;
let frame: number | null = null;
let intersection: IntersectionObserver | null = null;
let themeObserver: MutationObserver | null = null;
const items = new Set<Item>();
const clock = new THREE.Clock();

let lastScroll = 0;
let velocity = 0;

function ensureRenderer(): boolean {
    if (renderer) {
        return true;
    }

    try {
        renderer = new THREE.WebGLRenderer({
            alpha: true,
            antialias: false,
            premultipliedAlpha: true,
            powerPreference: 'low-power',
        });
    } catch {
        return false;
    }

    // The shaders write sRGB values straight through, so three.js must not
    // convert textures or colours to linear space on the way in.
    THREE.ColorManagement.enabled = false;
    renderer.outputColorSpace = THREE.LinearSRGBColorSpace;

    renderer.setPixelRatio(1);
    renderer.setClearColor(0x000000, 0);
    renderer.autoClear = true;

    scene = new THREE.Scene();
    camera = new THREE.Camera();
    mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2, 1, 1));
    mesh.frustumCulled = false;
    scene.add(mesh);

    intersection = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                for (const item of items) {
                    if (item.host === entry.target) {
                        item.visible = entry.isIntersecting;
                        item.dirty = true;
                    }
                }
            }

            wake();
        },
        { rootMargin: '120px 0px' },
    );

    // Blobs follow the theme and the face's accent.
    themeObserver = new MutationObserver(() => {
        for (const item of items) {
            if (item.kind === 'blob') {
                tintBlob(item);
                item.dirty = true;
            }
        }
    });
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
    themeObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['data-site'],
    });

    lastScroll = window.scrollY;
    document.addEventListener('visibilitychange', wake);
    // Scrolling drives the wave, so it restarts the loop (one shared listener).
    window.addEventListener('scroll', wake, { passive: true });

    return true;
}

function wake() {
    if (frame === null && !document.hidden) {
        frame = requestAnimationFrame(tick);
    }
}

function sizeSurface(item: Item) {
    const rect = item.host.getBoundingClientRect();
    const w = Math.max(1, Math.round(rect.width * item.resolution));
    const h = Math.max(1, Math.round(rect.height * item.resolution));

    if (item.surface.width !== w || item.surface.height !== h) {
        item.surface.width = w;
        item.surface.height = h;
        item.dirty = true;
        updateCover(item);

        if (item.kind === 'blob') {
            const aspect = rect.width / Math.max(rect.height, 1);
            item.material.uniforms.uAspect.value.set(
                Math.max(aspect, 1),
                Math.max(1 / aspect, 1),
            );
        }
    }
}

/** object-fit: cover, expressed as a UV scale and offset. */
function updateCover(item: Item) {
    const image = item.texture?.image as HTMLImageElement | undefined;

    if (!image || !image.width) {
        return;
    }

    const boxAspect = item.surface.width / item.surface.height;
    const imageAspect = image.width / image.height;
    const scale =
        boxAspect > imageAspect
            ? new THREE.Vector2(1, imageAspect / boxAspect)
            : new THREE.Vector2(boxAspect / imageAspect, 1);

    item.material.uniforms.uCover.value.set(
        scale.x,
        scale.y,
        (1 - scale.x) / 2,
        (1 - scale.y) / 2,
    );
}

function readColor(host: HTMLElement, name: string, fallback: string) {
    const value = getComputedStyle(host).getPropertyValue(name).trim();
    const color = new THREE.Color();

    try {
        color.set(value || fallback);
    } catch {
        color.set(fallback);
    }

    return color;
}

function tintBlob(item: Item) {
    const u = item.material.uniforms;
    u.uColorA.value = readColor(item.host, '--scene-1', '#2b4bff');
    u.uColorB.value = readColor(item.host, '--scene-2', '#6d83ff');
    u.uColorC.value = readColor(item.host, '--scene-3', '#0b0b0b');
}

function draw(item: Item, time: number) {
    if (!renderer) {
        return;
    }

    const { width: w, height: h } = item.surface;

    if (w > glWidth || h > glHeight) {
        glWidth = Math.max(glWidth, w);
        glHeight = Math.max(glHeight, h);
        renderer.setSize(glWidth, glHeight, false);
    }

    item.material.uniforms.uTime.value = time;
    mesh.material = item.material;
    renderer.setViewport(0, 0, w, h);
    renderer.render(scene, camera);

    // The viewport sits at the bottom-left of the GL canvas.
    item.context.clearRect(0, 0, w, h);
    item.context.drawImage(
        renderer.domElement,
        0,
        glHeight - h,
        w,
        h,
        0,
        0,
        w,
        h,
    );
}

function tick() {
    frame = null;

    if (document.hidden || !renderer) {
        return;
    }

    const time = clock.getElapsedTime();

    // Scroll speed, eased, in roughly -1..1.
    const scroll = window.scrollY;
    const delta = scroll - lastScroll;
    lastScroll = scroll;
    velocity += (Math.max(-1, Math.min(1, delta / 60)) - velocity) * 0.12;

    const moving = Math.abs(velocity) > 0.002;
    let busy = moving;

    for (const item of items) {
        if (!item.visible || !item.ready) {
            continue;
        }

        sizeSurface(item);

        const u = item.material.uniforms;
        item.hover += (item.hoverTarget - item.hover) * 0.08;
        item.mouse.lerp(item.mouseTarget, 0.12);

        const settling =
            Math.abs(item.hoverTarget - item.hover) > 0.002 ||
            item.hover > 0.002;

        if (item.kind === 'blob' || settling || moving || item.dirty) {
            if (u.uHover) {
                u.uHover.value = item.hover;
            }

            if (u.uVelocity) {
                u.uVelocity.value = velocity;
            }

            u.uMouse.value.copy(item.mouse);
            draw(item, time);
            item.dirty = false;
            busy = true;
        }
    }

    if (busy) {
        frame = requestAnimationFrame(tick);
    }
}

function register(item: Item): LiquidHandle {
    items.add(item);
    intersection?.observe(item.host);

    const onEnter = (event: PointerEvent) => {
        item.hoverTarget = 1;
        onMove(event);
    };
    const onLeave = () => {
        item.hoverTarget = 0;
        wake();
    };
    const onMove = (event: PointerEvent) => {
        const rect = item.host.getBoundingClientRect();
        item.mouseTarget.set(
            (event.clientX - rect.left) / rect.width,
            1 - (event.clientY - rect.top) / rect.height,
        );
        wake();
    };

    item.host.addEventListener('pointerenter', onEnter);
    item.host.addEventListener('pointerleave', onLeave);
    item.host.addEventListener('pointermove', onMove, { passive: true });

    item.cleanup.push(() => {
        item.host.removeEventListener('pointerenter', onEnter);
        item.host.removeEventListener('pointerleave', onLeave);
        item.host.removeEventListener('pointermove', onMove);
    });

    return {
        destroy() {
            item.cleanup.forEach((fn) => fn());
            intersection?.unobserve(item.host);
            items.delete(item);
            item.texture?.dispose();
            item.material.dispose();
        },
    };
}

function baseItem(
    kind: Kind,
    host: HTMLElement,
    surface: HTMLCanvasElement,
    material: THREE.ShaderMaterial,
    resolution: number,
    onReady: () => void,
): Item | null {
    const context = surface.getContext('2d');

    if (!context) {
        return null;
    }

    return {
        kind,
        host,
        surface,
        context,
        material,
        visible: false,
        ready: false,
        dirty: true,
        hover: 0,
        hoverTarget: 0,
        mouse: new THREE.Vector2(0.5, 0.5),
        mouseTarget: new THREE.Vector2(0.5, 0.5),
        resolution,
        onReady,
        cleanup: [],
    };
}

/** True when this browser can run the liquid layer at all. */
export function supported(): boolean {
    return ensureRenderer();
}

export function attachImage(
    host: HTMLElement,
    surface: HTMLCanvasElement,
    src: string,
    onReady: () => void,
): LiquidHandle | null {
    if (!ensureRenderer()) {
        return null;
    }

    const material = new THREE.ShaderMaterial({
        vertexShader,
        fragmentShader: imageFragment,
        uniforms: {
            uTexture: { value: null },
            uCover: { value: new THREE.Vector4(1, 1, 0, 0) },
            uMouse: { value: new THREE.Vector2(0.5, 0.5) },
            uHover: { value: 0 },
            uVelocity: { value: 0 },
            uTime: { value: 0 },
        },
    });

    const item = baseItem(
        'image',
        host,
        surface,
        material,
        Math.min(window.devicePixelRatio || 1, MAX_DPR),
        onReady,
    );

    if (!item) {
        material.dispose();

        return null;
    }

    const handle = register(item);
    let destroyed = false;

    new THREE.TextureLoader().load(
        src,
        (texture) => {
            if (destroyed) {
                texture.dispose();

                return;
            }

            texture.colorSpace = THREE.NoColorSpace;
            texture.minFilter = THREE.LinearFilter;
            texture.generateMipmaps = false;
            item.texture = texture;
            material.uniforms.uTexture.value = texture;
            item.ready = true;
            item.dirty = true;
            sizeSurface(item);
            updateCover(item);
            // Draw once now so the swap from <img> to canvas is seamless.
            draw(item, clock.getElapsedTime());
            item.onReady();
            wake();
        },
        undefined,
        () => {
            // Leave the plain <img> in place if the texture cannot load.
        },
    );

    return {
        destroy() {
            destroyed = true;
            handle.destroy();
        },
    };
}

export function attachBlob(
    host: HTMLElement,
    surface: HTMLCanvasElement,
    onReady: () => void,
): LiquidHandle | null {
    if (!ensureRenderer()) {
        return null;
    }

    const material = new THREE.ShaderMaterial({
        vertexShader,
        fragmentShader: blobFragment,
        transparent: true,
        uniforms: {
            uColorA: { value: new THREE.Color() },
            uColorB: { value: new THREE.Color() },
            uColorC: { value: new THREE.Color() },
            uAspect: { value: new THREE.Vector2(1, 1) },
            uMouse: { value: new THREE.Vector2(0.5, 0.5) },
            uTime: { value: 0 },
        },
    });

    // Smooth gradients survive a low resolution; it keeps big heroes cheap.
    const item = baseItem('blob', host, surface, material, 0.8, onReady);

    if (!item) {
        material.dispose();

        return null;
    }

    tintBlob(item);
    item.ready = true;
    const handle = register(item);

    // The blob reacts to the pointer anywhere over the page, not just itself.
    const onWindowMove = (event: PointerEvent) => {
        item.mouseTarget.set(
            event.clientX / window.innerWidth,
            1 - event.clientY / window.innerHeight,
        );
    };
    window.addEventListener('pointermove', onWindowMove, { passive: true });
    item.cleanup.push(() =>
        window.removeEventListener('pointermove', onWindowMove),
    );

    requestAnimationFrame(() => {
        item.onReady();
        wake();
    });

    return handle;
}
