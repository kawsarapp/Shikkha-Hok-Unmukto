import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAudioStore = defineStore('audio', () => {
    const isPlaying = ref(false);
    const currentTitle = ref('');
    const textChunks = ref([]);
    const currentChunkIndex = ref(0);
    const playbackRate = ref(1.0);
    const synth = typeof window !== 'undefined' && 'speechSynthesis' in window ? window.speechSynthesis : null;
    let currentUtterance = null;

    const progressPercent = computed(() => {
        if (!textChunks.value.length) return 0;
        return Math.round(((currentChunkIndex.value + 1) / textChunks.value.length) * 100);
    });

    const setupMediaSession = () => {
        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: currentTitle.value || 'শিক্ষা অডিও টিউটোরিয়াল',
                artist: 'EducationAlwaysFree',
                album: 'AI Voice Engine',
            });

            navigator.mediaSession.setActionHandler('play', () => resume());
            navigator.mediaSession.setActionHandler('pause', () => pause());
            navigator.mediaSession.setActionHandler('seekbackward', () => prevChunk());
            navigator.mediaSession.setActionHandler('seekforward', () => nextChunk());
        }
    };

    const loadText = (title, fullText) => {
        stop();
        currentTitle.value = title;
        // Chunk by Bengali Dári (।) or English period (.)
        const rawChunks = fullText
            .split(/(?<=[।.\n])\s+/)
            .map(s => s.trim())
            .filter(s => s.length > 0);

        textChunks.value = rawChunks.length ? rawChunks : [fullText];
        currentChunkIndex.value = 0;
        setupMediaSession();
    };

    const playChunk = (index) => {
        if (!synth || index >= textChunks.value.length) {
            isPlaying.value = false;
            return;
        }

        synth.cancel();
        currentChunkIndex.value = index;
        const text = textChunks.value[index];

        currentUtterance = new SpeechSynthesisUtterance(text);
        currentUtterance.rate = playbackRate.value;
        currentUtterance.lang = 'bn-BD'; // Bengali language code

        currentUtterance.onend = () => {
            if (currentChunkIndex.value + 1 < textChunks.value.length && isPlaying.value) {
                playChunk(currentChunkIndex.value + 1);
            } else {
                isPlaying.value = false;
            }
        };

        currentUtterance.onerror = (err) => {
            console.warn('Speech synth error:', err);
            isPlaying.value = false;
        };

        synth.speak(currentUtterance);
        isPlaying.value = true;
    };

    const play = (title, text) => {
        if (title && text) {
            loadText(title, text);
        }
        playChunk(currentChunkIndex.value);
    };

    const pause = () => {
        if (synth) {
            synth.pause();
            isPlaying.value = false;
        }
    };

    const resume = () => {
        if (synth && synth.paused) {
            synth.resume();
            isPlaying.value = true;
        } else {
            playChunk(currentChunkIndex.value);
        }
    };

    const stop = () => {
        if (synth) {
            synth.cancel();
        }
        isPlaying.value = false;
        currentChunkIndex.value = 0;
    };

    const togglePlay = () => {
        if (isPlaying.value) {
            pause();
        } else {
            resume();
        }
    };

    const setPlaybackRate = (rate) => {
        playbackRate.value = rate;
        if (isPlaying.value) {
            playChunk(currentChunkIndex.value);
        }
    };

    const nextChunk = () => {
        if (currentChunkIndex.value + 1 < textChunks.value.length) {
            playChunk(currentChunkIndex.value + 1);
        }
    };

    const prevChunk = () => {
        if (currentChunkIndex.value > 0) {
            playChunk(currentChunkIndex.value - 1);
        }
    };

    return {
        isPlaying,
        currentTitle,
        textChunks,
        currentChunkIndex,
        playbackRate,
        progressPercent,
        play,
        pause,
        resume,
        stop,
        togglePlay,
        setPlaybackRate,
        nextChunk,
        prevChunk,
    };
});
