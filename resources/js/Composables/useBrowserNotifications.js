import { ref } from 'vue';

export function useBrowserNotifications() {
    const isSupported = ref('Notification' in window);
    const permission = ref(isSupported.value ? Notification.permission : 'denied');

    const requestPermission = async () => {
        if (!isSupported.value) {
            alert('⚠️ আপনার ব্রাউজার পুশ নোটিফিকেশন সাপোর্ট করে না।');
            return false;
        }

        try {
            const res = await Notification.requestPermission();
            permission.value = res;
            if (res === 'granted') {
                sendNotification(
                    '🔔 নোটিফিকেশন চালু হয়েছে!',
                    'দৈনিক বিসিএস পড়ার রুটিন ও কুইজ রিমাইন্ডার এখন থেকে আপনার স্ক্রিনে নোটিফিকেশন দেখাবে।'
                );
                return true;
            } else {
                alert('⚠️ ব্রাউজার নোটিফিকেশন পারমিশন দেওয়া হয়নি।');
                return false;
            }
        } catch (e) {
            return false;
        }
    };

    const sendNotification = (title, body, icon = '/favicon.ico') => {
        if (!isSupported.value || Notification.permission !== 'granted') return;

        try {
            new Notification(title, {
                body,
                icon,
                badge: icon,
                vibrate: [200, 100, 200],
            });
        } catch (e) {
            console.error('Notification trigger error:', e);
        }
    };

    const triggerDailyStudyRoutineNotification = () => {
        sendNotification(
            '📚 আজকের বিসিএস প্রস্তুতি রুটিন',
            'আজকের লক্ষ্য: ১টি অধ্যায় সম্পূর্ণ পড়া এবং ১৫টি মডেল টেস্ট কুইজ সমাধান করা। পড়তে ক্লিক করুন!'
        );
    };

    return {
        isSupported,
        permission,
        requestPermission,
        sendNotification,
        triggerDailyStudyRoutineNotification,
    };
}
