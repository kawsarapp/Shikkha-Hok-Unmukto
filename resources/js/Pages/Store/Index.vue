<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Coins, ShoppingBag, CheckCircle, Sparkles, Award, ShieldCheck } from 'lucide-vue-next';

const props = defineProps({
    items: Array,
    user: Object,
});

const form = useForm({
    item_id: '',
    cost: 0,
});

const redeemItem = (item) => {
    if (props.user.coins < item.cost) {
        alert('আপনার পর্যাপ্ত কয়েন নেই! কুইজ খেলে বা পড়া সম্পন্ন করে কয়েন অর্জন করুন।');
        return;
    }

    if (confirm(`আপনি কি ${item.cost} কয়েন দিয়ে "${item.title}" রিডিম করতে চান?`)) {
        form.item_id = item.id;
        form.cost = item.cost;
        form.post('/store/redeem', {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-8">
            <!-- Header Banner -->
            <div class="p-8 bg-gradient-to-r from-amber-600 via-purple-900 to-slate-900 text-white rounded-3xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <span class="px-3 py-1 bg-amber-400/20 border border-amber-400/30 rounded-full text-amber-200 text-xs font-semibold flex items-center space-x-1 w-fit">
                        <Sparkles class="w-3.5 h-3.5" />
                        <span>কয়েন রিওয়ার্ড শপ</span>
                    </span>
                    <h1 class="text-3xl font-extrabold mt-2">অর্জিত কয়েন দিয়ে উপহার আনলক করুন</h1>
                    <p class="text-xs text-amber-100 mt-1">পড়াশোনা করুন, প্রতিদিন কুইজ জিতুন এবং অর্জিত কয়েন রিডিম করে স্পেশাল পাস পান।</p>
                </div>

                <!-- User Coin Balance Card -->
                <div class="flex items-center space-x-3 bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20 shrink-0">
                    <div class="w-12 h-12 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center font-black shadow-lg">
                        <Coins class="w-7 h-7 fill-slate-950 text-slate-950" />
                    </div>
                    <div>
                        <span class="text-xs text-amber-200 font-bold block">আপনার বর্তমান ব্যালেন্স</span>
                        <span class="text-2xl font-black text-amber-300">{{ user.coins || 0 }} কয়েন</span>
                    </div>
                </div>
            </div>

            <!-- Items Store Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex flex-col justify-between space-y-6 relative overflow-hidden"
                >
                    <div class="space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-900 flex items-center justify-center text-3xl shadow-inner">
                            {{ item.icon }}
                        </div>
                        <h3 class="text-lg font-extrabold text-gray-900 dark:text-slate-100 leading-snug">{{ item.title }}</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400 font-bengali leading-relaxed">{{ item.description }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center space-x-1 text-amber-600 font-extrabold text-sm">
                            <Coins class="w-4 h-4 fill-amber-500 text-amber-500" />
                            <span>{{ item.cost }} কয়েন</span>
                        </div>

                        <button
                            @click="redeemItem(item)"
                            :disabled="user.badges?.includes(item.id) || user.coins < item.cost"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition shadow-md"
                            :class="[
                                user.badges?.includes(item.id)
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 cursor-default'
                                    : user.coins >= item.cost
                                        ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-amber-500/20'
                                        : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            ]"
                        >
                            {{ user.badges?.includes(item.id) ? '✓ অনলকড' : 'রিডিম করুন' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
