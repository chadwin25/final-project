<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CampusBite helps school canteen staff manage orders and lets customers browse the menu and place orders online.">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#F6F7F5">
    <title>CampusBite · Built for the school lunch rush</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body class="landing-page">
    <header class="landing-nav">
        <div class="landing-container landing-nav-inner">
            <a class="landing-brand" href="#top" aria-label="CampusBite home">
                <img src="{{ asset('images/campusbite-mark.svg') }}" alt="" width="36" height="36">
                <span>CampusBite</span>
            </a>

            <nav class="landing-nav-links" aria-label="Landing page">
                <a href="#features">Features</a>
                <a href="#how-it-works">How it works</a>
                <a href="#faq">FAQ</a>
                <a href="{{ route('customer.login') }}">Customer log in</a>
            </nav>

            <a class="landing-button landing-button-small" href="{{ route('login') }}">
                Staff log in
            </a>
        </div>
    </header>

    <main id="top">
        <section class="landing-hero" aria-labelledby="landing-heading">
            <div class="landing-container landing-hero-inner">
                <h1 id="landing-heading">Built for the school lunch rush.</h1>

                <p class="landing-hero-copy">
                    CampusBite keeps your canteen counter moving. Staff can manage products,
                    orders, and stock, while customers can browse the menu and place orders online.
                </p>

                <div class="landing-hero-actions">
                    <a class="landing-button" href="{{ route('login') }}">Staff log in</a>
                    <a class="landing-button landing-button-secondary" href="{{ route('customer.login') }}">
                        Customer log in
                    </a>
                    <a class="landing-button landing-button-secondary" href="{{ route('customer.signup') }}">
                        Create customer account
                    </a>
                </div>

                <ul class="landing-highlights" aria-label="CampusBite highlights">
                    <li>
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg>
                        Philippine Peso (₱) native
                    </li>
                    <li>
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg>
                        Real-time stock updates
                    </li>
                    <li>
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg>
                        Separate staff and customer access
                    </li>
                </ul>
            </div>
        </section>

        <section class="landing-section" id="features" aria-labelledby="features-heading">
            <div class="landing-container">
                <div class="landing-section-heading">
                    <p>Features</p>
                    <h2 id="features-heading">Everything your counter needs, nothing it doesn’t.</h2>
                </div>

                <div class="feature-grid">
                    <article class="feature-card">
                        <h3>Fast Menu Browsing</h3>
                        <p>Customers can search available menu items and check prices before placing an order.</p>
                    </article>

                    <article class="feature-card">
                        <h3>Live Stock Visibility</h3>
                        <p>Staff can monitor product stock as orders are placed and processed.</p>
                    </article>

                    <article class="feature-card">
                        <h3>Order Tracking</h3>
                        <p>Customers can review their orders while staff manage payment and preparation status.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="landing-workflow" id="how-it-works" aria-labelledby="workflow-heading">
            <div class="landing-container">
                <div class="landing-section-heading">
                    <p>Workflow</p>
                    <h2 id="workflow-heading">A simple flow for staff and customers.</h2>
                </div>

                <ol class="workflow-grid">
                    <li>
                        <span class="workflow-label">Staff</span>
                        <h3>Manage the counter</h3>
                        <p>Staff sign in to manage the menu, inventory, and customer orders.</p>
                    </li>
                    <li>
                        <span class="workflow-label">Customers</span>
                        <h3>Create an account</h3>
                        <p>Customers sign up, browse available items, and choose a payment method.</p>
                    </li>
                    <li>
                        <span class="workflow-label">Orders</span>
                        <h3>Track order progress</h3>
                        <p>Customers can review order status, while staff confirm payment and prepare orders.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section class="landing-section landing-faq" id="faq" aria-labelledby="faq-heading">
            <div class="landing-container landing-faq-inner">
                <div class="landing-section-heading">
                    <p>FAQ</p>
                    <h2 id="faq-heading">Common questions</h2>
                </div>

                <div class="faq-list">
                    <details>
                        <summary>Who can access CampusBite?<span aria-hidden="true"></span></summary>
                        <p>Staff use their staff account. Customers can create their own account to place and track orders.</p>
                    </details>

                    <details>
                        <summary>How are prices and totals formatted?<span aria-hidden="true"></span></summary>
                        <p>Menu prices and order totals are displayed in Philippine Pesos (₱).</p>
                    </details>

                    <details>
                        <summary>Does stock update after an order is placed?<span aria-hidden="true"></span></summary>
                        <p>Available stock is checked when an order is placed. Reserved stock is returned if staff cancel an unpaid order.</p>
                    </details>

                    <details>
                        <summary>Which payment methods can customers choose?<span aria-hidden="true"></span></summary>
                        <p>Customers can select Cash or GCash. Staff manually confirm received cash or verify the GCash reference.</p>
                    </details>

                    <details>
                        <summary>Can customers track their orders?<span aria-hidden="true"></span></summary>
                        <p>Yes. Customers can sign in to view their order history and current order status.</p>
                    </details>
                </div>
            </div>
        </section>

        <section class="landing-cta-section" aria-labelledby="cta-heading">
            <div class="landing-container">
                <div class="landing-cta-panel">
                    <h2 id="cta-heading">Ready to use CampusBite?</h2>
                    <p>Choose the sign-in option that matches your account.</p>

                    <div class="landing-hero-actions">
                        <a class="landing-button landing-button-inverted" href="{{ route('login') }}">
                            Staff log in
                        </a>
                        <a class="landing-button landing-button-inverted" href="{{ route('customer.login') }}">
                            Customer log in
                        </a>
                        <a class="landing-button landing-button-inverted" href="{{ route('customer.signup') }}">
                            Create customer account
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="landing-container">
            <div class="landing-footer-grid">
                <div class="landing-footer-brand">
                    <a class="landing-brand" href="#top">
                        <img src="{{ asset('images/campusbite-mark.svg') }}" alt="" width="36" height="36">
                        <span>CampusBite</span>
                    </a>
                    <p>A focused web-based POS built for school food counters.</p>
                    <small>Prices shown in Philippine Pesos (₱).</small>
                </div>

                <div class="landing-footer-column">
                    <h2>Product</h2>
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="#faq">FAQ</a>
                </div>

                <div class="landing-footer-column">
                    <h2>Account</h2>
                    <a href="{{ route('login') }}">Staff log in</a>
                    <a href="{{ route('customer.login') }}">Customer log in</a>
                    <a href="{{ route('customer.signup') }}">Create customer account</a>
                </div>
            </div>

            <div class="landing-footer-bottom">
                <span>© {{ now()->year }} CampusBite. All rights reserved.</span>
                <span>School food counters, kept moving.</span>
            </div>
        </div>
    </footer>
</body>
</html>