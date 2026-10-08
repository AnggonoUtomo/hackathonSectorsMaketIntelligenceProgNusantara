import { useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

function savedAppearance(): Appearance {
    try {
        const value = localStorage.getItem('appearance');
        if (value === 'light' || value === 'dark' || value === 'system') return value;
    } catch {
        // Private browsers can block storage; the default still works.
    }
    return 'dark';
}

const applyTheme = (appearance: Appearance) => {
    const isDark = appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    document.documentElement.classList.toggle('dark', isDark);
};

export function initializeTheme() {
    applyTheme(savedAppearance());
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => applyTheme(savedAppearance()));
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>('dark');

    const updateAppearance = (mode: Appearance) => {
        setAppearance(mode);
        try {
            localStorage.setItem('appearance', mode);
        } catch {
            // Apply the selection even when it cannot be persisted.
        }
        applyTheme(mode);
    };

    useEffect(() => {
        setAppearance(savedAppearance());
    }, []);

    return { appearance, updateAppearance };
}
