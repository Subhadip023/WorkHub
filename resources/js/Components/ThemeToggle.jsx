import { useEffect, useMemo, useState } from 'react';

const STORAGE_KEY = 'workhub.theme';

function getInitialTheme() {
    if (typeof window === 'undefined') return 'light';

    const saved = window.localStorage.getItem(STORAGE_KEY);
    if (saved === 'light' || saved === 'dark') return saved;

    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches;
    return prefersDark ? 'dark' : 'light';
}

function applyTheme(theme) {
    if (typeof document === 'undefined') return;
    document.documentElement.classList.toggle('dark', theme === 'dark');
}

export default function ThemeToggle({ className = '' }) {
    const [theme, setTheme] = useState(getInitialTheme);

    useEffect(() => {
        applyTheme(theme);
        window.localStorage.setItem(STORAGE_KEY, theme);
    }, [theme]);

    const label = useMemo(
        () => (theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'),
        [theme],
    );

    return (
        <button
            type="button"
            aria-label={label}
            title={label}
            onClick={() => setTheme((t) => (t === 'dark' ? 'light' : 'dark'))}
            className={
                'inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50 ' +
                'dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800 ' +
                className
            }
        >
            <span className="hidden sm:inline">
                {theme === 'dark' ? 'Dark' : 'Light'}
            </span>
            <span className="text-xs font-medium opacity-70">Theme</span>
        </button>
    );
}


