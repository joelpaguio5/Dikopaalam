<?php
// Public landing page for the CCST Portal. No login needed.
// vercel.json already sends "/" to this file through the catch-all route.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCST Portal - Clark College of Science and Technology</title>
    <meta name="description" content="The online portal of Clark College of Science and Technology. Enroll, submit requirements and track your application.">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/landing.css">
    <link rel="stylesheet" href="/css/skeleton.css">
    <script src="/js/ui.js"></script>
</head>
<body class="landing-body">

    <header class="site-nav">
        <a href="/" class="nav-brand">CCST <span class="brand-ph">Portal</span></a>
        <nav class="nav-links">
            <a href="#how-it-works" class="nav-hide-mobile">How it works</a>
            <a href="/login.php">Log in</a>
            <a href="/register.php" class="nav-cta">Student sign up</a>
        </nav>
    </header>

    <main>
        <!-- HERO: login buttons on the left, how it works beside them -->
        <section class="hero" id="how-it-works">
            <div class="hero-copy">
                <p class="eyebrow">Clark College of Science and Technology</p>
                <h1>Enroll and track your records online.</h1>
                <p class="hero-lead">
                    The CCST Portal is where students enroll, submit their requirements and follow
                    their application, and where school staff review and approve it. All in one place.
                </p>
                <div class="hero-actions">
                    <a href="/login.php?as=student" class="btn-hero">Login as Student</a>
                    <a href="/login.php?as=admin" class="btn-hero-ghost">Login as Admin</a>
                </div>
                <p class="hero-note">New student? <a href="/register.php" class="auth-link">Create your account</a></p>
            </div>

            <aside class="how-card" aria-labelledby="how-title">
                <h2 id="how-title">How enrollment works</h2>
                <ol class="steps">
                    <li>
                        <span class="step-num">1</span>
                        <div>
                            <h3>Create your account</h3>
                            <p>Sign up with your name, email and mobile number, then confirm your email.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">2</span>
                        <div>
                            <h3>Fill out your enrollment</h3>
                            <p>Enter your details and choose your course and subjects.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">3</span>
                        <div>
                            <h3>Submit your requirements</h3>
                            <p>Upload your documents, such as your ID photo and records.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">4</span>
                        <div>
                            <h3>Get approved</h3>
                            <p>The school reviews your application. Log in anytime to check its status.</p>
                        </div>
                    </li>
                </ol>
            </aside>
        </section>

        <!-- WHO LOGS IN -->
        <section class="portal-roles">
            <div class="section-head">
                <p class="eyebrow">Two ways to log in</p>
                <h2>Choose how you use the portal</h2>
            </div>

            <div class="grid-cards">
                <article class="card role-card">
                    <h3>Student</h3>
                    <p>Create an account, complete your enrollment, upload your requirements and see whether your application is pending, needs more documents, or is approved.</p>
                    <div class="card-actions">
                        <a href="/login.php?as=student" class="btn-primary">Login as Student</a>
                        <a href="/register.php" class="btn-secondary">Create an account</a>
                    </div>
                </article>

                <article class="card role-card">
                    <h3>Admin</h3>
                    <p>For CCST staff. Review applications and documents, approve enrollments, assign student numbers and manage courses and enrollment dates. Admin accounts are created by the school.</p>
                    <div class="card-actions">
                        <a href="/login.php?as=admin" class="btn-primary">Login as Admin</a>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; <?php echo date('Y'); ?> Clark College of Science and Technology. All rights reserved.</p>
    </footer>

</body>
</html>