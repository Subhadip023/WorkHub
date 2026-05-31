<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- SEO Optimization -->
    <title>WorkHub - Collaborative Project Tracking & Team Management Platform</title>
    <meta name="description" content="WorkHub is a premium collaborative workspace for modern teams to organize tasks, track project completion in real-time, switch between company profiles, and collaborate effortlessly.">
    <meta name="keywords" content="project tracking, task manager, team collaboration, company workspaces, developer tools, project management software, rich text editor, task planner">
    <meta name="author" content="WorkHub Team">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="WorkHub - Collaborative Project Tracking & Team Management Platform">
    <meta property="og:description" content="WorkHub is a premium collaborative workspace for modern teams to organize tasks, track project completion in real-time, switch between company profiles, and collaborate effortlessly.">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="WorkHub - Collaborative Project Tracking & Team Management Platform">
    <meta property="twitter:description" content="WorkHub is a premium collaborative workspace for modern teams to organize tasks, track project completion in real-time, switch between company profiles, and collaborate effortlessly.">
    
    <!-- Fonts & Icons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('asset/img/logo.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('asset/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    
    <!-- Custom Vanilla CSS -->
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #2e59d9;
            --primary-light: #f0f3fc;
            --secondary: #6c757d;
            --dark: #0f172a;
            --light: #f8fafc;
            --card-bg: rgba(255, 255, 255, 0.85);
            --gradient: linear-gradient(135deg, #4e73df 0%, #8f6cf0 100%);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --font-main: 'Outfit', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-main);
            color: #334155;
            background-color: var(--light);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Utility Classes */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Header Navigation */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(248, 250, 252, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            transition: var(--transition);
        }

        .nav-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            color: #475569;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }

        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: 0 4px 14px rgba(78, 115, 223, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.45);
        }

        /* Hero Section */
        .hero {
            padding: 180px 0 100px;
            background: radial-gradient(circle at 80% 20%, rgba(143, 108, 240, 0.08) 0%, rgba(248, 250, 252, 0) 50%);
            position: relative;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 60px;
            align-items: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: 56px;
            font-weight: 800;
            line-height: 1.15;
            color: var(--dark);
            margin-bottom: 24px;
        }

        .hero-title span {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 18px;
            color: #475569;
            margin-bottom: 40px;
            max-width: 540px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .hero-image-wrapper {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-image-wrapper img {
            width: 100%;
            max-width: 480px;
            filter: drop-shadow(0 20px 40px rgba(15, 23, 42, 0.08));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background-color: white;
            position: relative;
        }

        .section-header {
            text-align: center;
            max-width: 650px;
            margin: 0 auto 70px;
        }

        .section-header h2 {
            font-size: 40px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 16px;
        }

        .section-header p {
            font-size: 18px;
            color: #64748b;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: var(--light);
            border: 1px solid rgba(226, 232, 240, 0.7);
            border-radius: 20px;
            padding: 40px 30px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient);
            opacity: 0;
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.05);
            background: white;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 24px;
            transition: var(--transition);
        }

        .feature-card:hover .feature-icon {
            background: var(--primary);
            color: white;
        }

        .feature-card h3 {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .feature-card p {
            color: #64748b;
            font-size: 15px;
        }

        /* Workflows Section */
        .workflow {
            padding: 100px 0;
            background: radial-gradient(circle at 10% 80%, rgba(78, 115, 223, 0.05) 0%, rgba(248, 250, 252, 0) 50%);
        }

        .workflow-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            position: relative;
            margin-top: 40px;
        }

        .step-item {
            text-align: center;
            position: relative;
        }

        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient);
            color: white;
            font-weight: 700;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 4px 10px rgba(78, 115, 223, 0.25);
        }

        .step-item h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .step-item p {
            color: #64748b;
            font-size: 15px;
            max-width: 280px;
            margin: 0 auto;
        }

        /* CTA Section */
        .cta {
            padding: 100px 0;
            background-color: white;
        }

        .cta-box {
            background: var(--gradient);
            border-radius: 30px;
            padding: 70px 40px;
            text-align: center;
            color: white;
            box-shadow: 0 20px 40px rgba(78, 115, 223, 0.2);
            position: relative;
            overflow: hidden;
        }

        .cta-box::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            top: -150px;
            right: -150px;
        }

        .cta-box h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .cta-box p {
            font-size: 18px;
            margin-bottom: 36px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.9;
        }

        .cta-box .btn {
            background: white;
            color: var(--primary-dark);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }

        .cta-box .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: #94a3b8;
            padding: 60px 0 30px;
            border-top: 1px solid #1e293b;
        }

        .footer-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #1e293b;
            padding-bottom: 40px;
            margin-bottom: 30px;
        }

        .footer-logo {
            font-size: 24px;
            font-weight: 800;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-copyright {
            font-size: 14px;
        }

        /* Responsiveness */
        @media (max-width: 991px) {
            .hero-grid {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 50px;
            }
            .hero-title {
                font-size: 46px;
            }
            .hero-subtitle {
                margin-left: auto;
                margin-right: auto;
            }
            .hero-actions {
                justify-content: center;
            }
            .workflow-steps {
                grid-template-columns: 1fr;
                gap: 50px;
            }
            .footer-grid {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .nav-wrapper {
                flex-direction: column;
                height: auto;
                padding: 15px 0;
                gap: 15px;
            }
            .nav-links {
                display: none;
            }
            .hero {
                padding: 140px 0 60px;
            }
            .hero-title {
                font-size: 36px;
            }
            .hero-actions {
                flex-direction: column;
                width: 100%;
                gap: 12px;
            }
            .hero-actions .btn {
                width: 100%;
            }
            .cta-box h2 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="container nav-wrapper">
            <a href="{{ url('/') }}" class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" style="width: 32px; height: 32px; filter: drop-shadow(0 2px 4px rgba(78, 115, 223, 0.25));">
                    <defs>
                        <linearGradient id="nav-logo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#4e73df" />
                            <stop offset="100%" stop-color="#8f6cf0" />
                        </linearGradient>
                    </defs>
                    <path d="M16 2 L28 9 L28 23 L16 30 L4 23 L4 9 Z" fill="none" stroke="url(#nav-logo-grad)" stroke-width="2.5" stroke-linejoin="round" />
                    <path d="M16 8 L23 12 L23 20 L16 24 L9 20 L9 12 Z" fill="url(#nav-logo-grad)" opacity="0.9" />
                    <circle cx="16" cy="16" r="3.5" fill="#ffffff" />
                </svg>
                WorkHub
            </a>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#workflow">Workflow</a></li>
                </ul>
            </nav>
            
            <div class="auth-buttons">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="fas fa-star"></i>
                        Next-Gen Project Coordination
                    </div>
                    <h1 class="hero-title">Unleash Team Productivity with <span>WorkHub</span></h1>
                    <p class="hero-subtitle">
                        The ultimate collaborative platform to organize tasks, coordinate projects with visual indicators, switch company contexts dynamically, and accelerate your workflows.
                    </p>
                    <div class="hero-actions">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-columns mr-2" style="margin-right: 8px;"></i> Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-primary">
                                Get Started Free <i class="fas fa-chevron-right" style="margin-left: 8px;"></i>
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline">
                                Access Account
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="hero-image-wrapper">
                    <img src="{{ asset('asset/img/undraw_posting_photo.svg') }}" alt="WorkHub Workspace Illustration">
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features" id="features">
            <div class="container">
                <div class="section-header">
                    <h2>Everything you need to collaborate</h2>
                    <p>WorkHub brings all your files, company workspaces, custom project designs, and tasks together into one search-optimized tool.</p>
                </div>
                
                <div class="features-grid">
                    <!-- Feature 1 -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3>Multi-Company Workspaces</h3>
                        <p>Segment projects by client or organization. Create companies, switch active contexts seamlessly, and invite members with unique, secure access codes.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Real-time Progress Tracking</h3>
                        <p>Monitor your project's task completion progress. Visual indicators show completed counts and percentage completion charts so you are always updated.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-pen-nib"></i>
                        </div>
                        <h3>Quill Rich Documentation</h3>
                        <p>Write detailed project scopes and task explanations. The integrated Quill editor allows formatting headers, strong text, and lists with clear visual styling.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-paperclip"></i>
                        </div>
                        <h3>Task Attachments</h3>
                        <p>Upload files and images directly to task items. Keep your screenshots, wireframes, and documents contextually linked to the tasks themselves.</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-import"></i>
                        </div>
                        <h3>JSON Structure Imports</h3>
                        <p>Have an existing list of tasks? Directly paste and import JSON formats to quickly initialize tasks for a project in seconds.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3>Custom Color Themes</h3>
                        <p>Tailor your workspace. Set specific theme colors for projects to personalize layouts and categorize work streams visually.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Workflow Section -->
        <section class="workflow" id="workflow">
            <div class="container">
                <div class="section-header">
                    <h2>Simple, Streamlined Workflow</h2>
                    <p>Get your team synchronized and ready to execute in three simple steps.</p>
                </div>
                
                <div class="workflow-steps">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <h3>Build Your Team</h3>
                        <p>Register a company context or join an existing company by entering a code shared by your admin.</p>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <h3>Design a Project</h3>
                        <p>Create a project, choose a styling color, and specify rich goals via the Quill text editor.</p>
                    </div>
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <h3>Delegate and Launch</h3>
                        <p>Add task items, assign due dates, assign team members, and check off items as they complete.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta">
            <div class="container">
                <div class="cta-box">
                    <h2>Ready to organize your team?</h2>
                    <p>Create a free account today and start tracking projects like a pro with WorkHub's collaboration features.</p>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn">Go to Dashboard <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></a>
                    @else
                        <a href="{{ route('register') }}" class="btn">Join WorkHub Now <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></a>
                    @endauth
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                 <a href="{{ url('/') }}" class="footer-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" style="width: 32px; height: 32px; filter: drop-shadow(0 2px 4px rgba(255, 255, 255, 0.15));">
                        <defs>
                            <linearGradient id="footer-logo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ffffff" />
                                <stop offset="100%" stop-color="#cbd5e1" />
                            </linearGradient>
                        </defs>
                        <path d="M16 2 L28 9 L28 23 L16 30 L4 23 L4 9 Z" fill="none" stroke="url(#footer-logo-grad)" stroke-width="2.5" stroke-linejoin="round" />
                        <path d="M16 8 L23 12 L23 20 L16 24 L9 20 L9 12 Z" fill="url(#footer-logo-grad)" opacity="0.9" />
                        <circle cx="16" cy="16" r="3.5" fill="#0f172a" />
                    </svg>
                    WorkHub
                </a>
                <div class="footer-copyright">
                    &copy; {{ date('Y') }} WorkHub. All rights reserved. Made for premium project management.
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
