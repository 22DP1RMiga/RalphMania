import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

export const useLocaleStore = defineStore('locale', () => {
    const currentLocale = ref(localStorage.getItem('locale') || 'lv');

    const setLocale = (locale) => {
        currentLocale.value = locale;
        localStorage.setItem('locale', locale);
    };

    const toggleLocale = () => {
        const newLocale = currentLocale.value === 'lv' ? 'en' : 'lv';
        setLocale(newLocale);
        return newLocale;
    };

    return {
        currentLocale,
        setLocale,
        toggleLocale,
    };
});
