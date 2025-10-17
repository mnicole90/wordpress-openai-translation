/**
 * Centralized backend selection manager
 * Syncs backend selection between sidebar and block controls
 */

const STORAGE_KEY = 'wp_translation_selected_backend';

export const BackendManager = {
    // Get currently selected backend
    getSelectedBackend() {
        // Try global state first (set by sidebar)
        if (window.translationSelectedBackend) {
            return window.translationSelectedBackend;
        }

        // Fallback to localStorage
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            return stored;
        }

        // Default to first available backend
        const backends = window.translationConfig?.backends || {};
        const firstBackend = Object.keys(backends)[0];
        return firstBackend || 'google';
    },

    // Set selected backend (syncs across components)
    setSelectedBackend(backend) {
        window.translationSelectedBackend = backend;
        localStorage.setItem(STORAGE_KEY, backend);

        // Dispatch custom event for other components to listen
        window.dispatchEvent(new CustomEvent('translationBackendChanged', {
            detail: { backend }
        }));
    },

    // Get backend display name
    getBackendName(backend) {
        const backends = window.translationConfig?.backends || {};
        return backends[backend] || backend;
    },

    // Subscribe to backend changes
    onBackendChange(callback) {
        const handler = (event) => callback(event.detail.backend);
        window.addEventListener('translationBackendChanged', handler);
        return () => window.removeEventListener('translationBackendChanged', handler);
    }
};