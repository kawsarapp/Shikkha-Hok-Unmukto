import { ref, onMounted, onUnmounted } from 'vue';

export function useExamAntiCheat(onAutoSubmit) {
    const warningCount = ref(0);
    const maxWarnings = 3;
    const isWarningModalOpen = ref(false);

    const handleVisibilityChange = () => {
        if (document.hidden) {
            warningCount.value++;
            if (warningCount.value >= maxWarnings) {
                isWarningModalOpen.value = false;
                if (typeof onAutoSubmit === 'function') {
                    onAutoSubmit('Page visibility violation limit reached (3 tab switches).');
                }
            } else {
                isWarningModalOpen.value = true;
            }
        }
    };

    const closeWarningModal = () => {
        isWarningModalOpen.value = false;
    };

    onMounted(() => {
        document.addEventListener('visibilitychange', handleVisibilityChange);
    });

    onUnmounted(() => {
        document.removeEventListener('visibilitychange', handleVisibilityChange);
    });

    return {
        warningCount,
        maxWarnings,
        isWarningModalOpen,
        closeWarningModal,
    };
}
