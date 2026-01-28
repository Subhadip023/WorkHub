import { Head, Link } from '@inertiajs/react';
import ThemeToggle from '@/Components/ThemeToggle';

function Pill({ children }) {
    return (
        <span className="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
            {children}
        </span>
    );
}

function SectionTitle({ title, subtitle }) {
    return (
        <div>
            <h2 className="text-lg font-semibold text-slate-900 dark:text-slate-50">{title}</h2>
            {subtitle ? (
                <p className="mt-1 text-sm text-slate-600 dark:text-slate-300">{subtitle}</p>
            ) : null}
        </div>
    );
}

export default function Details({ auth, appName }) {
    const name = appName ?? 'Work Hub';
    const desc =
        'A phase-wise Laravel learning project to build a multi-tenant SaaS backend: Company → Project → Task, with roles, policies, and secure data access.';

    return (
        <>
            <Head title="Details" />

            <div className="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
                <header className="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                        <div className="flex items-center gap-3">
                            <Link
                                href={route('home')}
                                className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-sm font-semibold text-white"
                            >
                                WH
                            </Link>
                            <div className="leading-tight">
                                <div className="text-base font-semibold">{name}</div>
                                <div className="text-xs text-slate-500 dark:text-slate-400">
                                    Details & roadmap
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <ThemeToggle />
                            <Link
                                href={route('home')}
                                className="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                            >
                                Home
                            </Link>
                            {auth?.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-white"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <Link
                                    href={route('login')}
                                    className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-white"
                                >
                                    Login
                                </Link>
                            )}
                        </div>
                    </div>
                </header>

                <main className="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-14">
                    <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-10">
                        <div className="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">
                                    {name}
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                                    {desc}
                                </p>
                                <div className="mt-5 flex flex-wrap gap-2">
                                    <Pill>Laravel</Pill>
                                    <Pill>Inertia + React</Pill>
                                    <Pill>Tailwind</Pill>
                                    <Pill>Multi-tenant SaaS</Pill>
                                </div>
                            </div>

                            <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    Core data chain (target)
                                </div>
                                <div className="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    Company → Project → Task
                                </div>
                                <div className="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                    Every request must validate the chain to prevent
                                    cross-company access.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mt-10 grid gap-6 lg:grid-cols-2">
                        <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                            <SectionTitle
                                title="What this project teaches"
                                subtitle="The goal is confidence in Laravel backend fundamentals and real-world patterns."
                            />
                            <ul className="mt-5 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                                <li>
                                    <span className="font-semibold text-slate-900">
                                        Request → Controller → DB → UI:
                                    </span>{' '}
                                    how data flows end-to-end.
                                </li>
                                <li>
                                    <span className="font-semibold text-slate-900">
                                        Relationships:
                                    </span>{' '}
                                    one-to-many and multi-tenant boundaries.
                                </li>
                                <li>
                                    <span className="font-semibold text-slate-900">
                                        Authorization:
                                    </span>{' '}
                                    middleware + policies (clean, secure access rules).
                                </li>
                                <li>
                                    <span className="font-semibold text-slate-900">
                                        Security mindset:
                                    </span>{' '}
                                    never trust IDs, always scope by company.
                                </li>
                            </ul>
                        </div>

                        <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                            <SectionTitle
                                title="Phase-wise roadmap"
                                subtitle="A practical path from basics to a proper SaaS backend."
                            />
                            <div className="mt-6 space-y-4">
                                {[
                                    {
                                        phase: 'Phase 1',
                                        title: 'Basic Task System',
                                        bullets: [
                                            'Form submission → controller → DB → views',
                                            'Basic CRUD, validation, Blade loops/conditions',
                                        ],
                                    },
                                    {
                                        phase: 'Phase 2',
                                        title: 'User Authentication',
                                        bullets: [
                                            'Breeze auth + protecting routes',
                                            'User → Tasks relationship; filter by logged-in user',
                                        ],
                                    },
                                    {
                                        phase: 'Phase 3',
                                        title: 'Company & Project System',
                                        bullets: [
                                            'User belongs to one company',
                                            'Company has projects; projects have tasks',
                                            'Validate project → company → user chain',
                                        ],
                                    },
                                    {
                                        phase: 'Phase 4',
                                        title: 'Roles & Authorization',
                                        bullets: [
                                            'Admin vs member rules',
                                            'Policies for Project/Task; block cross-company access',
                                        ],
                                    },
                                ].map((p) => (
                                    <div
                                        key={p.phase}
                                        className="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950"
                                    >
                                        <div className="flex items-center justify-between gap-3">
                                            <div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                                    {p.phase}
                                                </div>
                                                <div className="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-50">
                                                    {p.title}
                                                </div>
                                            </div>
                                        </div>
                                        <ul className="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-700 dark:text-slate-200">
                                            {p.bullets.map((b) => (
                                                <li key={b}>{b}</li>
                                            ))}
                                        </ul>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                        <SectionTitle
                            title="Next steps"
                            subtitle="When you’re ready, we can implement Phase 1 without jumping into the full SaaS complexity."
                        />
                        <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <Link
                                href={route('home')}
                                className="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                            >
                                Back to Home
                            </Link>
                            <a
                                href="https://laravel.com/docs"
                                className="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-white"
                            >
                                Read Laravel Docs
                            </a>
                        </div>
                    </div>
                </main>

                <footer className="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950">
                    <div className="mx-auto max-w-6xl px-4 py-8 text-sm text-slate-600 sm:px-6">
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span className="font-semibold text-slate-900 dark:text-slate-50">{name}</span>{' '}
                                — {desc}
                            </div>
                            <div className="flex items-center gap-4">
                                <Link
                                    href={route('home')}
                                    className="underline decoration-slate-300 underline-offset-4 hover:decoration-slate-500 dark:decoration-slate-600 dark:hover:decoration-slate-400"
                                >
                                    Home
                                </Link>
                                <Link
                                    href={route('details')}
                                    className="underline decoration-slate-300 underline-offset-4 hover:decoration-slate-500 dark:decoration-slate-600 dark:hover:decoration-slate-400"
                                >
                                    Details
                                </Link>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}


