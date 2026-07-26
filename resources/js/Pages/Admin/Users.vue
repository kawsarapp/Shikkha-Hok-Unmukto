<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import { ShieldAlert, UserPlus, ShieldCheck, Edit, Trash2, Key, Users, ArrowLeft, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
    users: Array,
});

const isModalOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    role: 'admin',
    password: '',
    permissions: ['manage_courses', 'manage_questions'],
});

const availablePermissions = [
    { key: 'manage_courses', label: 'কোর্স ও বিষয়ভিত্তিক অধ্যায় পরিচালনা' },
    { key: 'manage_questions', label: 'প্রশ্ন তৈরি, এডিট ও AI প্রশ্ন জেনারেটর' },
    { key: 'manage_settings', label: 'গ্লোবাল সেটিংস ও Gemini API Key কনফিগারেশন' },
    { key: 'manage_users', label: 'সুপার এডমিন: ইউজার তৈরি ও পারমিশন কন্ট্রোল' },
];

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    form.permissions = ['manage_courses', 'manage_questions'];
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.phone = user.phone || '';
    form.role = user.role;
    form.password = '';
    form.permissions = user.permissions || ['manage_courses', 'manage_questions'];
    isModalOpen.value = true;
};

const togglePermission = (permKey) => {
    const idx = form.permissions.indexOf(permKey);
    if (idx > -1) {
        form.permissions.splice(idx, 1);
    } else {
        form.permissions.push(permKey);
    }
};

const saveUser = () => {
    if (editingUser.value) {
        form.post(`/admin/users/${editingUser.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    } else {
        form.post('/admin/users', {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    }
};

const deleteUser = (userId) => {
    if (confirm('আপনি কি নিশ্চিত যে এই ইউজার অ্যাকাউন্টটি মুছে ফেলতে চান?')) {
        router.delete(`/admin/users/${userId}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-8">
            <!-- Header Banner -->
            <div class="p-8 bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 text-white rounded-3xl shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2">
                        <Link href="/admin" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold flex items-center space-x-1">
                            <ArrowLeft class="w-3.5 h-3.5" />
                            <span>এডমিন ড্যাশবোর্ড</span>
                        </Link>
                        <span class="px-3 py-1 bg-purple-500/30 border border-purple-400/30 rounded-full text-purple-200 text-xs font-semibold">
                            সুপার এডমিন সিকিউরিটি প্যানেল
                        </span>
                    </div>
                    <h1 class="text-3xl font-extrabold mt-3">User & Permission Management</h1>
                    <p class="text-xs text-purple-200 mt-1">সুপার এডমিন প্যানেল থেকে নতুন এডমিন/টিচার তৈরি করুন এবং নির্দিষ্ট পারমিশন বরাদ্দ দিন।</p>
                </div>

                <button
                    @click="openCreateModal"
                    class="px-5 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-2xl font-bold text-xs flex items-center space-x-2 shadow-lg shadow-purple-600/30 transition transform hover:scale-105"
                >
                    <UserPlus class="w-4 h-4" />
                    <span>+ নতুন ইউজার তৈরি করুন</span>
                </button>
            </div>

            <!-- Users Table Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <Users class="w-5 h-5 text-purple-600" />
                        <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100">নিবন্ধিত সকল ইউজার তালিকা ({{ users.length }}জন)</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-slate-900/80 text-gray-500 uppercase tracking-wider font-bold border-b border-gray-100 dark:border-slate-700">
                            <tr>
                                <th class="p-4">নাম ও ইমেইল</th>
                                <th class="p-4">ফোন নাম্বার</th>
                                <th class="p-4">রোল (Role)</th>
                                <th class="p-4">বরাদ্দকৃত পারমিশনসমূহ</th>
                                <th class="p-4 text-right">একশন</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700 font-sans">
                            <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50/50 dark:hover:bg-slate-900/40 transition">
                                <td class="p-4">
                                    <div class="font-bold text-gray-900 dark:text-slate-100 text-sm">{{ u.name }}</div>
                                    <div class="text-gray-400 text-[11px]">{{ u.email }}</div>
                                </td>
                                <td class="p-4 text-gray-600 dark:text-slate-300 font-mono">
                                    {{ u.phone || 'N/A' }}
                                </td>
                                <td class="p-4">
                                    <span
                                        class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider"
                                        :class="[
                                            u.role === 'super_admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 border border-purple-300' :
                                            u.role === 'admin' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-300' :
                                            u.role === 'teacher' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300' :
                                            'bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-300'
                                        ]"
                                    >
                                        {{ u.role }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="p in u.permissions"
                                            :key="p"
                                            class="px-2 py-0.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 rounded-md text-[10px] font-semibold"
                                        >
                                            {{ p }}
                                        </span>
                                        <span v-if="!u.permissions?.length" class="text-gray-400 font-italic">কোনো বিশেষ পারমিশন নেই</span>
                                    </div>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button
                                            @click="openEditModal(u)"
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-700 rounded-xl"
                                            title="পারমিশন এডিট"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button
                                            v-if="u.id !== $page.props.auth.user.id"
                                            @click="deleteUser(u.id)"
                                            class="p-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-700 rounded-xl"
                                            title="ডিলিট"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create / Edit User Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl max-w-lg w-full border border-gray-200 dark:border-slate-700 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-slate-700">
                        <h3 class="text-xl font-bold">
                            {{ editingUser ? 'ইউজার পারমিশন এডিট করুন' : 'নতুন ইউজার তৈরি করুন' }}
                        </h3>
                        <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <form @submit.prevent="saveUser" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold mb-1">পূর্ণ নাম:</label>
                            <input v-model="form.name" type="text" required class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold mb-1">ইমেইল এড্রেস:</label>
                            <input v-model="form.email" type="email" :disabled="!!editingUser" required class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs disabled:opacity-60" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold mb-1">ফোন নাম্বার:</label>
                            <input v-model="form.phone" type="text" placeholder="017xxxxxxxx" class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold mb-1">রোল সিলেক্ট করুন:</label>
                            <select v-model="form.role" class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs font-bold text-purple-600">
                                <option value="super_admin">Super Admin (সম্পূর্ণ অ্যাক্সেস)</option>
                                <option value="admin">Admin (কোর্স ও এক্সাম ম্যানেজমেন্ট)</option>
                                <option value="teacher">Teacher (কোর্স ও কনটেন্ট রাইটার)</option>
                                <option value="student">Student (সাধারণ শিক্ষার্থী)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold mb-1">পাসওয়ার্ড {{ editingUser ? '(পরিবর্তন করতে চাইলে লিখুন)' : '' }}:</label>
                            <input v-model="form.password" type="password" :required="!editingUser" placeholder="******" class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs" />
                        </div>

                        <!-- Permissions Checkboxes -->
                        <div class="pt-2">
                            <label class="block text-xs font-bold mb-2 text-indigo-600">অনুমোদিত পারমিশনসমূহ (Permissions):</label>
                            <div class="space-y-2 bg-gray-50 dark:bg-slate-900 p-3 rounded-2xl border">
                                <div v-for="p in availablePermissions" :key="p.key" class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        :id="p.key"
                                        :checked="form.permissions.includes(p.key)"
                                        @change="togglePermission(p.key)"
                                        class="rounded text-purple-600 focus:ring-purple-500"
                                    />
                                    <label :for="p.key" class="text-xs font-medium cursor-pointer">{{ p.label }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-4">
                            <button @click="isModalOpen = false" type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-xs font-bold">বাতিল</button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-purple-600 text-white rounded-xl text-xs font-bold shadow-md shadow-purple-600/30">
                                {{ editingUser ? 'পারমিশন আপডেট করুন' : 'ইউজার তৈরি করুন' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
