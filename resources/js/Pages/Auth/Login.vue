<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import ContentProtection from '@/Components/ContentProtection.vue';
import WatermarkOverlay from '@/Components/WatermarkOverlay.vue';
import { BookOpen, LogIn, Sparkles, ShieldCheck } from 'lucide-vue-next';

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <ContentProtection>
        <div class="min-h-screen bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-950 text-white flex items-center justify-center p-4 font-sans relative overflow-hidden">
            <WatermarkOverlay />

            <div class="max-w-md w-full bg-slate-900/80 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl space-y-6 relative z-10">
                <!-- Logo Header -->
                <div class="text-center">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-emerald-500 flex items-center justify-center text-white mx-auto shadow-lg shadow-indigo-500/30 mb-3">
                        <BookOpen class="w-6 h-6" />
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight">EducationAlwaysFree</h2>
                    <p class="text-xs text-slate-400 mt-1">বিনামূল্যে এআই-চালিত অ্যাডভান্সড লার্নিং প্ল্যাটফর্ম</p>
                </div>

                <!-- Error Messages -->
                <div v-if="form.errors.email" class="p-3 bg-rose-950/60 border border-rose-900 text-rose-300 rounded-xl text-xs font-semibold">
                    {{ form.errors.email }}
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">ইমেইল ঠিকানা</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="student@educationfree.com"
                            class="w-full px-4 py-3 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">পাসওয়ার্ড</label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            placeholder="••••••••"
                            class="w-full px-4 py-3 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        />
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center space-x-2 text-slate-400 cursor-pointer">
                            <input type="checkbox" v-model="form.remember" class="rounded text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-slate-700" />
                            <span>পাসওয়ার্ড মনে রাখুন</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-lg shadow-indigo-600/30 flex items-center justify-center space-x-2"
                    >
                        <LogIn class="w-4 h-4" />
                        <span>লগইন করুন</span>
                    </button>
                </form>

                <!-- Demo Login Shortcut Box -->
                <div class="p-4 bg-slate-800/50 rounded-2xl border border-slate-700/60 text-xs text-slate-400 space-y-2">
                    <p class="font-bold text-slate-300">🔑 ডেমো লগইন ক্রেডেনশিয়াল:</p>
                    <p>👨‍🎓 শিক্ষার্থী: <code class="text-indigo-300">student@educationfree.com</code> / <code class="text-indigo-300">password123</code></p>
                    <p>👨‍💼 এডমিন: <code class="text-indigo-300">admin@educationfree.com</code> / <code class="text-indigo-300">password123</code></p>
                </div>
            </div>
        </div>
    </ContentProtection>
</template>
