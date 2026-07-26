<script setup>
import { useAudioStore } from '@/Stores/useAudioStore';
import { Play, Pause, SkipBack, SkipForward, X, Volume2 } from 'lucide-vue-next';

const audioStore = useAudioStore();
</script>

<template>
    <div
        v-if="audioStore.textChunks.length > 0"
        class="fixed bottom-4 right-4 left-4 md:left-auto md:w-96 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md border border-indigo-100 dark:border-slate-700 shadow-2xl rounded-2xl p-4 z-[9990] transition-all duration-300 transform translate-y-0"
    >
        <!-- Header Info -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-2 truncate">
                <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-lg">
                    <Volume2 class="w-4 h-4 animate-pulse" />
                </div>
                <div class="truncate">
                    <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">AI ভয়েস প্লেয়ার</p>
                    <h4 class="text-sm font-bold text-gray-800 dark:text-slate-100 truncate">{{ audioStore.currentTitle }}</h4>
                </div>
            </div>
            <button
                @click="audioStore.stop"
                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700"
            >
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden mb-3">
            <div
                class="bg-indigo-600 dark:bg-indigo-500 h-full transition-all duration-300"
                :style="{ width: audioStore.progressPercent + '%' }"
            ></div>
        </div>

        <!-- Control Buttons -->
        <div class="flex items-center justify-between">
            <!-- Speed Switcher -->
            <div class="flex items-center space-x-1">
                <button
                    v-for="rate in [1.0, 1.25, 1.5, 2.0]"
                    :key="rate"
                    @click="audioStore.setPlaybackRate(rate)"
                    class="px-2 py-0.5 text-xs font-medium rounded-md transition"
                    :class="audioStore.playbackRate === rate ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700'"
                >
                    {{ rate }}x
                </button>
            </div>

            <!-- Play / Pause / Skip -->
            <div class="flex items-center space-x-2">
                <button
                    @click="audioStore.prevChunk"
                    class="p-2 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-full"
                >
                    <SkipBack class="w-4 h-4" />
                </button>

                <button
                    @click="audioStore.togglePlay"
                    class="p-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg transition transform active:scale-95"
                >
                    <Play v-if="!audioStore.isPlaying" class="w-5 h-5 fill-current" />
                    <Pause v-else class="w-5 h-5 fill-current" />
                </button>

                <button
                    @click="audioStore.nextChunk"
                    class="p-2 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-full"
                >
                    <SkipForward class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>
