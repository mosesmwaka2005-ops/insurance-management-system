<?php
require_once 'includes/config.php';

// If user is logged in, redirect to their dashboard
if (isLoggedIn()) {
    $role = getUserRole();
    redirect($role . '-dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techna Print - Professional Print Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow-x: hidden;
            background: #0f0f23;
            color: #fff;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveGrid 20s linear infinite;
        }

        @keyframes moveGrid {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(15, 15, 35, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            padding: 8px 16px;
            border-radius: 8px;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.1);
        }

        .btn {
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .btn-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.5);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 100px 20px 50px;
        }

        .hero-content {
            max-width: 900px;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
            background: linear-gradient(135deg, #fff 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.5rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 40px;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Features Section */
        .features {
            padding: 100px 50px;
            background: rgba(15, 15, 35, 0.5);
        }

        .section-title {
            text-align: center;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 60px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: rgba(255,255,255,0.7);
            line-height: 1.6;
        }

        /* Portals Section */
        .portals {
            padding: 100px 50px;
        }

        .portals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .portal-card {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border: 2px solid rgba(102, 126, 234, 0.3);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: white;
            display: block;
        }

        .portal-card:hover {
            transform: scale(1.05);
            border-color: rgba(102, 126, 234, 0.8);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
        }

        .portal-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .portal-card h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .portal-card p {
            color: rgba(255,255,255,0.7);
            margin-bottom: 20px;
        }

        /* Stats Section */
        .stats {
            padding: 80px 50px;
            background: rgba(255,255,255,0.05);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1000px;
            margin: 0 auto;
            text-align: center;
        }

        .stat-item h2 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .stat-item p {
            color: rgba(255,255,255,0.7);
            font-size: 1.1rem;
        }

        /* Footer */
        footer {
            padding: 40px 50px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(15, 15, 35, 0.8);
        }

        footer p {
            color: rgba(255,255,255,0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            nav {
                padding: 15px 20px;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .features, .portals, .stats {
                padding: 60px 20px;
            }
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>

    <!-- Navigation -->
    <nav>
        <div class="logo">
            <i class="fas fa-print"></i>
            Techna Print
        </div>
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#portals">Portals</a>
            <a href="login.php" class="btn btn-outline">Login</a>
            <a href="signup.php" class="btn btn-primary">Get Started</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content" style="display: flex; align-items: center; gap: 60px; flex-wrap: wrap; max-width: 1200px;">
            <!-- Text Content -->
            <div style="flex: 1; min-width: 300px; text-align: left;">
                <h1 class="float">Professional Print Management</h1>
                <p>Streamline your printing workflow with our cutting-edge management system. Fast, secure, and collaborative.</p>
                <div class="hero-buttons" style="justify-content: flex-start;">
                    <a href="signup.php" class="btn btn-primary">
                        <i class="fas fa-rocket"></i> Start Free Trial
                    </a>
                    <a href="#portals" class="btn btn-outline">
                        <i class="fas fa-play-circle"></i> Explore Portals
                    </a>
                </div>
            </div>
            
            <!-- Image -->
            <div style="flex: 1; min-width: 300px;">
                <img src="assets/hero-image.jpg" 
                     alt="Techna Print - Professional Printing Services" 
                     style="width: 100%; max-width: 500px; border-radius: 20px; 
                            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.5);
                            animation: float 3s ease-in-out infinite;">
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <h2>500+</h2>
                <p>Active Users</p>
            </div>
            <div class="stat-item">
                <h2>10K+</h2>
                <p>Jobs Completed</p>
            </div>
            <div class="stat-item">
                <h2>99.9%</h2>
                <p>Uptime</p>
            </div>
            <div class="stat-item">
                <h2>24/7</h2>
                <p>Support</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <h2 class="section-title">Why Choose Techna Print?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>Lightning Fast</h3>
                <p>Process jobs in seconds with our optimized workflow engine and real-time updates.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Bank-Level Security</h3>
                <p>Enterprise-grade encryption protects your files and sensitive business data.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Team Collaboration</h3>
                <p>Seamless communication between customers, designers, and managers.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Advanced Analytics</h3>
                <p>Track performance, costs, and productivity with detailed insights.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Ready</h3>
                <p>Access your projects anywhere, anytime from any device.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Real-Time Tracking</h3>
                <p>Monitor job progress with live updates and instant notifications.</p>
            </div>
        </div>
    </section>

    <!-- Portals Section -->
    <section id="portals" class="portals">
        <h2 class="section-title">Choose Your Portal</h2>
        <div class="portals-grid">
            <a href="login.php?portal=customer" class="portal-card">
                <div class="portal-icon">👤</div>
                <h3>Customer Portal</h3>
                <p>Submit and track your print jobs with ease. Get instant quotes and real-time updates.</p>
                <span class="btn btn-primary">Access Portal</span>
            </a>
            <a href="login.php?portal=designer" class="portal-card">
                <div class="portal-icon">🎨</div>
                <h3>Designer Portal</h3>
                <p>Manage design projects efficiently with powerful tools and collaboration features.</p>
                <span class="btn btn-primary">Access Portal</span>
            </a>
            <a href="login.php?portal=manager" class="portal-card">
                <div class="portal-icon">📊</div>
                <h3>Manager Portal</h3>
                <p>Oversee operations, analytics, and team performance in one centralized dashboard.</p>
                <span class="btn btn-primary">Access Portal</span>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Techna Print. All rights reserved. | Professional Print Management System</p>
    </footer>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .portal-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    </script>
</body>
</html>
