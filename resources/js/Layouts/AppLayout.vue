<script setup>
import { ref, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import WatermarkOverlay from '@/Components/WatermarkOverlay.vue';
import ContentProtection from '@/Components/ContentProtection.vue';
import AudioPlayer from '@/Components/AudioPlayer.vue';
import AiTutorWidget from '@/Components/AiTutorWidget.vue';
import { BookOpen, Trophy, ShieldCheck, Sun, Moon, LogOut, User as UserIcon, Flame, Settings, Coins, LayoutDashboard, Menu, X } from 'lucide-vue-next';

const page = usePage();
const isDarkMode = ref(false);
const isMobileMenuOpen = ref(false);

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const logout = () => {
    router.post('/logout');
};

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }
});
</script>

<template>
    <ContentProtection>
        <div class="min-h-screen bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-slate-100 flex flex-col font-sans">
            <!-- Canvas Watermark -->
            <WatermarkOverlay />

            <!-- Sticky Navigation Bar -->
            <header class="sticky top-0 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-gray-200 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Logo & Brand Name -->
                        <div class="flex items-center space-x-8">
                            <button
                                @click="isMobileMenuOpen = !isMobileMenuOpen"
                                class="md:hidden p-2 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-xl"
                            >
                                <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
                                <X v-else class="w-6 h-6" />
                            </button>

                            <Link href="/dashboard" class="flex items-center space-x-3 group">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-emerald-500 flex items-center justify-center text-white font-bold shadow-md shadow-indigo-500/20 group-hover:scale-105 transition">
                                    <BookOpen class="w-5 h-5" />
                                </div>
                                <div>
                                    <span class="text-lg font-extrabold bg-gradient-to-r from-indigo-600 via-purple-600 to-emerald-500 bg-clip-text text-transparent">
                                        EducationAlwaysFree
                                    </span>
                                    <span class="hidden sm:block text-[10px] text-gray-400 dark:text-slate-400 font-medium uppercase tracking-wider">বিনামূল্যে AI শিক্ষামঞ্চ</span>
                                </div>
                            </Link>

                            <!-- Navigation Menu Links (Desktop) -->
                            <nav v-if="page.props.auth?.user" class="hidden md:flex items-center space-x-1 text-xs font-bold">
                                <Link
                                    href="/dashboard"
                                    class="px-3 py-2 rounded-xl transition flex items-center space-x-1.5"
                                    :class="page.url === '/dashboard' ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800'"
                                >
                                    <LayoutDashboard class="w-4 h-4" />
                                    <span>ড্যাশবোর্ড</span>
                                </Link>

                                <Link
                                    href="/battle"
                                    class="px-3 py-2 rounded-xl transition flex items-center space-x-1.5 text-rose-600 dark:text-rose-400 font-extrabold"
                                    :class="page.url === '/battle' ? 'bg-rose-50 dark:bg-rose-950/60' : 'hover:bg-rose-50 dark:hover:bg-slate-800'"
                                >
                                    <span>⚔️ ১v১ কুইজ ব্যাটল</span>
                                </Link>

                                <Link
                                    href="/store"
                                    class="px-3 py-2 rounded-xl transition flex items-center space-x-1.5 text-amber-600 dark:text-amber-400 font-extrabold"
                                    :class="page.url === '/store' ? 'bg-amber-50 dark:bg-amber-950/60' : 'hover:bg-amber-50 dark:hover:bg-slate-800'"
                                >
                                    <span>🛍️ কয়েন শপ</span>
                                </Link>

                                <Link
                                    href="/analytics"
                                    class="px-3 py-2 rounded-xl transition flex items-center space-x-1.5 text-indigo-600 dark:text-indigo-400 font-extrabold"
                                    :class="page.url === '/analytics' ? 'bg-indigo-50 dark:bg-indigo-950/60' : 'hover:bg-indigo-50 dark:hover:bg-slate-800'"
                                >
                                    <span>📊 দুর্বলতা অ্যানালিটিক্স</span>
                                </Link>

                                <Link
                                    v-if="['admin', 'super_admin', 'teacher'].includes(page.props.auth?.user?.role)"
                                    href="/admin"
                                    class="px-3 py-2 rounded-xl transition flex items-center space-x-1.5 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800"
                                >
                                    <Settings class="w-4 h-4" />
                                    <span>এডমিন প্যানেল</span>
                                </Link>
                            </nav>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex items-center space-x-3">
                            <!-- Coins Badge -->
                            <div v-if="page.props.auth?.user" class="flex items-center space-x-1 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-900/60 rounded-full text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                                <Coins class="w-4 h-4 text-indigo-500 fill-indigo-500" />
                                <span>{{ page.props.auth.user.coins || 0 }} কয়েন</span>
                            </div>

                            <!-- Streak Badge -->
                            <div v-if="page.props.auth?.user" class="hidden sm:flex items-center space-x-1 px-3 py-1.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 rounded-full text-amber-600 dark:text-amber-400 text-xs font-bold">
                                <Flame class="w-4 h-4 text-amber-500 fill-amber-500 animate-pulse" />
                                <span>{{ page.props.auth.user.study_streak }} দিন স্ট্রিক</span>
                            </div>

                            <!-- Theme Toggle -->
                            <button
                                @click="toggleDarkMode"
                                class="p-2 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-xl transition"
                                title="থিম পরিবর্তন করুন"
                            >
                                <Sun v-if="isDarkMode" class="w-5 h-5 text-amber-400" />
                                <Moon v-else class="w-5 h-5 text-indigo-600" />
                            </button>

                            <!-- User Auth Profile & Logout -->
                            <div v-if="page.props.auth?.user" class="flex items-center space-x-2 pl-2 border-l border-gray-200 dark:border-slate-800">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs">
                                    {{ page.props.auth.user.name.charAt(0) }}
                                </div>
                                <button
                                    @click="logout"
                                    class="p-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition"
                                    title="লগআউট"
                                >
                                    <LogOut class="w-4 h-4" />
                                </button>
                            </div>
                            <Link v-else href="/login" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-indigo-600/20">
                                লগইন
                            </Link>
                        </div>
                    </div>

                    <!-- Mobile Drawer Menu Dropdown -->
                    <div v-if="isMobileMenuOpen && page.props.auth?.user" class="md:hidden py-4 border-t border-gray-100 dark:border-slate-800 space-y-2">
                        <Link
                            @click="isMobileMenuOpen = false"
                            href="/dashboard"
                            class="block px-4 py-2.5 rounded-xl font-bold text-xs"
                            :class="page.url === '/dashboard' ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600' : 'text-gray-700 dark:text-slate-300'"
                        >
                            🏠 ড্যাশবোর্ড
                        </Link>
                        <Link
                            @click="isMobileMenuOpen = false"
                            href="/battle"
                            class="block px-4 py-2.5 rounded-xl font-bold text-xs text-rose-600 dark:text-rose-400"
                            :class="page.url === '/battle' ? 'bg-rose-50 dark:bg-rose-950/60' : ''"
                        >
                            ⚔️ ১v১ কুইজ ব্যাটল
                        </Link>
                        <Link
                            @click="isMobileMenuOpen = false"
                            href="/store"
                            class="block px-4 py-2.5 rounded-xl font-bold text-xs text-amber-600 dark:text-amber-400"
                            :class="page.url === '/store' ? 'bg-amber-50 dark:bg-amber-950/60' : ''"
                        >
                            🛍️ কয়েন শপ
                        </Link>
                        <Link
                            @click="isMobileMenuOpen = false"
                            href="/analytics"
                            class="block px-4 py-2.5 rounded-xl font-bold text-xs text-indigo-600 dark:text-indigo-400"
                            :class="page.url === '/analytics' ? 'bg-indigo-50 dark:bg-indigo-950/60' : ''"
                        >
                            📊 দুর্বলতা অ্যানালিটিক্স
                        </Link>
                        <Link
                            v-if="['admin', 'super_admin', 'teacher'].includes(page.props.auth?.user?.role)"
                            @click="isMobileMenuOpen = false"
                            href="/admin"
                            class="block px-4 py-2.5 rounded-xl font-bold text-xs text-purple-600 dark:text-purple-400"
                            :class="page.url.startsWith('/admin') ? 'bg-purple-50 dark:bg-purple-950/60' : ''"
                        >
                            👥 এডমিন ও সিকিউরিটি প্যানেল
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Flash Notification Banner -->
                <div v-if="page.props.flash?.error" class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-2xl text-rose-700 dark:text-rose-300 text-sm font-semibold flex items-center space-x-2">
                    <ShieldCheck class="w-5 h-5 text-rose-500 shrink-0" />
                    <span>{{ page.props.flash.error }}</span>
                </div>
                <div v-if="page.props.flash?.success" class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm font-semibold flex items-center space-x-2">
                    <ShieldCheck class="w-5 h-5 text-emerald-500 shrink-0" />
                    <span>{{ page.props.flash.success }}</span>
                </div>

                <slot />
            </main>

            <!-- Sticky Audio Player (Mounted Globally) -->
            <AudioPlayer />

            <!-- 24/7 AI BCS Tutor Doubt Solver Widget (Mounted Globally) -->
            <AiTutorWidget />
        </div>
    </ContentProtection>
</template>
