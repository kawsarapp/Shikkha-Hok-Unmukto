<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const canvasRef = ref(null);
let animationFrameId = null;
let posX = 0;
let posY = 0;
let speedX = 0.3;
let speedY = 0.2;

const updateWatermark = () => {
    const canvas = canvasRef.value;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const user = page.props.auth?.user || { name: 'EducationAlwaysFree', phone: 'Protected Content' };
    const text = `${user.name} • ${user.phone || 'Member'} • ${page.props.user_ip || 'IP Encrypted'}`;

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.font = '15px "Hind Siliguri", "Inter", sans-serif';
    ctx.fillStyle = 'rgba(148, 163, 184, 0.18)'; // Slate subtle transparent opacity
    ctx.rotate((-18 * Math.PI) / 180);

    // Draw repeating pattern grid
    const stepX = 320;
    const stepY = 160;

    posX += speedX;
    posY += speedY;

    if (posX > stepX) posX = 0;
    if (posY > stepY) posY = 0;

    for (let x = -canvas.width + posX; x < canvas.width * 2; x += stepX) {
        for (let y = -canvas.height + posY; y < canvas.height * 2; y += stepY) {
            ctx.fillText(text, x, y);
        }
    }

    animationFrameId = requestAnimationFrame(updateWatermark);
};

const handleResize = () => {
    if (canvasRef.value) {
        canvasRef.value.width = window.innerWidth;
        canvasRef.value.height = window.innerHeight;
    }
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
    updateWatermark();
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
});
</script>

<template>
    <canvas
        ref="canvasRef"
        class="fixed inset-0 pointer-events-none z-[9999] select-none no-select"
        style="user-select: none; -webkit-user-select: none;"
    ></canvas>
</template>
