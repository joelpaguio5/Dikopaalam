<?php
// Public landing page. No login needed.
// vercel.json already sends "/" to this file through the catch-all route.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatrikulaPH - Online Enrollment for Schools</title>
    <meta name="description" content="Schools set up once. Students enroll from a single link.">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/landing.css">
</head>
<body class="landing-body">

    <header class="site-nav">
        <a href="/" class="nav-brand">Matrikula<span class="brand-ph">PH</span></a>
        <nav class="nav-links">
            <a href="#direct-link">Student link</a>
            <a href="/login.php">Log in</a>
            <a href="/register.php" class="nav-cta">Student sign up</a>
        </nav>
    </header>

    <main>
        <!-- HERO: button on the left, how it works beside it -->
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">Online enrollment for Philippine schools</p>
                <h1>Enrollment, made simple.</h1>
                <p class="hero-lead">
                    Schools set up once. Students enroll from a single link, with no long lines
                    and no lost paperwork.
                </p>
                <div class="hero-actions">
                    <a href="/register.php?as=school" class="btn-hero">Enroll Your School</a>
                    <a href="/login.php" class="btn-hero-ghost">Log in</a>
                </div>
                <p class="hero-note">Already a student? <a href="/register.php" class="auth-link">Create your account</a></p>
            </div>

            <aside class="how-card" aria-labelledby="how-title">
                <h2 id="how-title">How it works</h2>
                <ol class="steps">
                    <li>
                        <span class="step-num">1</span>
                        <div>
                            <h3>Enroll your school</h3>
                            <p>Click the button and register your school. You'll set up your school's details, courses and enrollment dates.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">2</span>
                        <div>
                            <h3>Get your enrollment link</h3>
                            <p>Your school gets its own link that opens straight to your enrollment form.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">3</span>
                        <div>
                            <h3>Students enroll online</h3>
                            <p>Students fill in their details, upload their requirements and choose their courses.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">4</span>
                        <div>
                            <h3>Review and approve</h3>
                            <p>Your registrar checks each application, approves it and assigns student numbers.</p>
                        </div>
                    </li>
                </ol>
            </aside>
        </section>

        <!-- DIRECT LINK DETAILS -->
        <section class="direct-link" id="direct-link">
            <div class="section-head">
                <p class="eyebrow">For students</p>
                <h2>Get a link that goes straight to the enrollment form</h2>
                <p class="section-lead">
                    No searching, no extra steps. Once your school is set up, share one link and
                    students land right on your enrollment form.
                </p>
            </div>

            <div class="link-demo" aria-label="Example enrollment link">
                <span class="link-demo-label">Example link</span>
                <code class="link-demo-url">yourwebsite.com/enroll/<strong>your-school</strong></code>
            </div>

            <div class="grid-cards link-steps">
                <article class="card">
                    <h3>1. Your school gets its own link</h3>
                    <p>After you enroll your school, we create a link with your school's name in it. Each school has its own.</p>
                </article>
                <article class="card">
                    <h3>2. Share it anywhere</h3>
                    <p>Post it on your school's Facebook page, send it by email or text, or add it to your website.</p>
                </article>
                <article class="card">
                    <h3>3. Students open it and enroll</h3>
                    <p>The link opens your school's enrollment form directly. Students fill it in and submit their requirements.</p>
                </article>
                <article class="card">
                    <h3>4. Track the application</h3>
                    <p>Students can log in anytime to see if their application is pending, needs more documents, or is approved.</p>
                </article>
            </div>
        </section>

        <!-- CLOSING CALL TO ACTION -->
        <section class="closing">
            <h2>Ready to start?</h2>
            <p>Set up your school in minutes and share your link today.</p>
            <a href="/register.php?as=school" class="btn-hero">Enroll Your School</a>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; <?php echo date('Y'); ?> MatrikulaPH. All rights reserved.</p>
    </footer>

</body>
</html>