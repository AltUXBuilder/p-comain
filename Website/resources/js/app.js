import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

Alpine.plugin(focus);
Alpine.plugin(collapse);

window.Alpine = Alpine;
Alpine.start();

// ── Device fingerprint utility ────────────────────────────────────────────────
window.PandoFingerprint = {
    generate() {
        const nav = window.navigator;
        const raw = [
            nav.userAgent,
            nav.language,
            screen.colorDepth,
            screen.width + 'x' + screen.height,
            new Date().getTimezoneOffset(),
            nav.hardwareConcurrency || '',
            nav.platform || '',
        ].join('|');

        // Simple hash
        let hash = 0;
        for (let i = 0; i < raw.length; i++) {
            const char = raw.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash).toString(36);
    },

    store() {
        const fp = this.generate();
        sessionStorage.setItem('pando_fp', fp);
        // Set a cookie for server-side access
        document.cookie = `pando_fp=${fp}; path=/; SameSite=Strict; Secure`;
        return fp;
    },

    get() {
        return sessionStorage.getItem('pando_fp') || this.store();
    }
};

// Generate fingerprint on load
document.addEventListener('DOMContentLoaded', () => {
    PandoFingerprint.store();
});
