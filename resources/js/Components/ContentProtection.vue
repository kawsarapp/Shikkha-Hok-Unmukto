<script setup>
import { onMounted, onUnmounted } from 'vue';

const preventContextMenu = (e) => {
    e.preventDefault();
    return false;
};

const preventKeyShortcuts = (e) => {
    // Prevent Ctrl+C, Ctrl+P, Ctrl+U, Ctrl+Shift+I, F12, PrintScreen
    if (
        e.keyCode === 123 || // F12
        (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) || // Ctrl+Shift+I/J/C
        (e.ctrlKey && (e.keyCode === 67 || e.keyCode === 80 || e.keyCode === 85 || e.keyCode === 83)) // Ctrl+C/P/U/S
    ) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
};

onMounted(() => {
    document.addEventListener('contextmenu', preventContextMenu);
    document.addEventListener('keydown', preventKeyShortcuts);
    document.body.classList.add('no-select');
});

onUnmounted(() => {
    document.removeEventListener('contextmenu', preventContextMenu);
    document.removeEventListener('keydown', preventKeyShortcuts);
});
</script>

<template>
    <div class="contents no-select">
        <slot />
    </div>
</template>
