import { Head, Link } from '@inertiajs/react';
import ThemeToggle from '@/Components/ThemeToggle';

function NavLink({ href, children }) {
    return (
        <Link
            href={href}
            className="text-sm font-medium text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white"
        >
            {children}
        </Link>
    );
}

export default function Home({ auth, appName, canLogin, canRegister }) {
    const description =
        'Work Hub is a Laravel learning project to practice building a real-world SaaS: Company → Project → Task, with roles and multi-tenant security.';

    return (
        <>
            <Head title="Home" />

            <div className="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
                <header className="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                        <Link href={route('home')} className="flex items-center gap-3">
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-sm font-semibold text-white">
                                WH
                            </div>
                            <div className="leading-tight">
                                <div className="text-base font-semibold">
                                    {appName ?? 'Work Hub'}
                                </div>
                                <div className="text-xs text-slate-500 dark:text-slate-400">
                                    Classic Laravel learning project
                                </div>
                            </div>
                        </Link>

                        <nav className="hidden items-center gap-4 sm:flex">
                            <NavLink href={route('details')}>Details</NavLink>
                            <ThemeToggle />
                            {auth?.user ? (
                                <NavLink href={route('dashboard')}>Dashboard</NavLink>
                            ) : (
                                <>
                                    {canLogin && (
                                        <NavLink href={route('login')}>Login</NavLink>
                                    )}
                                    {canRegister && (
                                        <NavLink href={route('register')}>
                                            Register
                                        </NavLink>
                                    )}
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                <main>
                    <section className="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
                        <div className="grid items-center gap-10 lg:grid-cols-2">
                            <div>
                                <div className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                                    <span className="h-2 w-2 rounded-full bg-emerald-500" />
                                    Phase-wise roadmap included
                                </div>

                                <h1 className="mt-5 text-4xl font-semibold tracking-tight text-slate-900 dark:text-slate-50 sm:text-5xl">
                                    Build a clean SaaS backend step by step with{' '}
                                    <span className="underline decoration-slate-300 underline-offset-4">
                                        Work Hub
                                    </span>
                                    .
                                </h1>

                                <p className="mt-5 text-base leading-7 text-slate-600 dark:text-slate-300">
                                    {description}
                                </p>

                                <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <Link
                                        href={route('details')}
                                        className="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-white"
                                    >
                                        View details
                                    </Link>

                                    {auth?.user ? (
                                        <Link
                                            href={route('dashboard')}
                                            className="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                                        >
                                            Go to dashboard
                                        </Link>
                                    ) : (
                                        <>
                                            {canLogin && (
                                                <Link
                                                    href={route('login')}
                                                    className="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                                                >
                                                    Login
                                                </Link>
                                            )}
                                        </>
                                    )}
                                </div>

                                <dl className="mt-10 grid grid-cols-2 gap-6 sm:max-w-xl">
                                    <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                                        <dt className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                            Focus
                                        </dt>
                                        <dd className="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-50">
                                            Laravel fundamentals → SaaS architecture
                                        </dd>
                                    </div>
                                    <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                                        <dt className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                            Core model
                                        </dt>
                                        <dd className="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-50">
                                            Company → Project → Task
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <div className="text-sm font-semibold">
                                            Roadmap preview
                                        </div>
                                        <div className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            What you’ll build next
                                        </div>
                                    </div>
                                    <Link
                                        href={route('details')}
                                        className="text-sm font-semibold text-slate-900 underline decoration-slate-300 underline-offset-4 hover:decoration-slate-500 dark:text-slate-50 dark:decoration-slate-600 dark:hover:decoration-slate-400"
                                    >
                                        See full roadmap
                                    </Link>
                                </div>

                                <div className="mt-6 space-y-4">
                                    {[
                                        {
                                            title: 'Phase 1 — Tasks (no users)',
                                            text: 'Form → controller → DB → Blade/Views.',
                                        },
                                        {
                                            title: 'Phase 2 — Auth',
                                            text: 'Users, relationships, per-user tasks.',
                                        },
                                        {
                                            title: 'Phase 3 — Company & Projects',
                                            text: 'Multi-tenant structure: company owns projects.',
                                        },
                                        {
                                            title: 'Phase 4 — Roles & Policies',
                                            text: 'Admin vs member, secure access rules.',
                                        },
                                    ].map((item) => (
                                        <div
                                            key={item.title}
                                            className="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                                        >
                                            <div className="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                                {item.title}
                                            </div>
                                            <div className="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                                {item.text}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </section>

                    <section className="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                            <div className="grid gap-6 md:grid-cols-3">
                                {[
                                    {
                                        title: 'Classic UI',
                                        text: 'Simple typography, clear spacing, and readable components.',
                                    },
                                    {
                                        title: 'Real-world rules',
                                        text: 'Company boundaries, project ownership, and secure queries.',
                                    },
                                    {
                                        title: 'Interview-ready',
                                        text: 'Clean structure, policies, and a clear roadmap.',
                                    },
                                ].map((f) => (
                                    <div
                                        key={f.title}
                                        className="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
                                    >
                                        <div className="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                            {f.title}
                                        </div>
                                        <div className="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                            {f.text}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </section>
                </main>

                <footer className="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950">
                    <div className="mx-auto max-w-6xl px-4 py-8 text-sm text-slate-600 sm:px-6">
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span className="font-semibold text-slate-900 dark:text-slate-50">
                                    {appName ?? 'Work Hub'}
                                </span>{' '}
                                — {description}
                            </div>
                            <div className="flex items-center gap-4">
                                <Link
                                    href={route('details')}
                                    className="underline decoration-slate-300 underline-offset-4 hover:decoration-slate-500 dark:decoration-slate-600 dark:hover:decoration-slate-400"
                                >
                                    Details
                                </Link>
                                <a
                                    href="https://laravel.com/docs"
                                    className="underline decoration-slate-300 underline-offset-4 hover:decoration-slate-500 dark:decoration-slate-600 dark:hover:decoration-slate-400"
                                >
                                    Laravel Docs
                                </a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}


