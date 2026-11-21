<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CampusBite helps school canteen staff ring up orders in Philippine pesos and keep stock accurate during the lunch rush.">
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
            </nav>
            <a class="landing-button landing-button-small" href="{{ route('login') }}">Log in</a>
        </div>
    </header>

    <main id="top">
        <section class="landing-hero" aria-labelledby="landing-heading">
            <div class="landing-container landing-hero-inner">
                <h1 id="landing-heading">Built for the school lunch rush.</h1>
                <p class="landing-hero-copy">CampusBite keeps your canteen counter moving. Find menu items fast, ring up walk-in or named-customer orders with peso-accurate totals, and track live stock without slowing down the line.</p>
                <div class="landing-hero-actions">
                    <a class="landing-button" href="{{ route('login') }}">Log in to CampusBite</a>
                    <a class="landing-button landing-button-secondary" href="#how-it-works">See how it works</a>
                </div>
                <ul class="landing-highlights" aria-label="CampusBite highlights">
                    <li><svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg> Philippine Peso (₱) native</li>
                    <li><svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg> Real-time stock updates</li>
                    <li><svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 10 3 3 7-7"/></svg> Staff-managed access</li>
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
                        <h3>Fast Menu Scanning</h3>
                        <p>Search dishes and filter active menu items without digging through a long product list.</p>
                    </article>
                    <article class="feature-card">
                        <h3>Live Stock Visibility</h3>
                        <p>Stock counts update automatically with every order so staff immediately know what is available and what is sold out.</p>
                    </article>
                    <article class="feature-card">
                        <h3>Clear Order History</h3>
                        <p>Every checkout keeps its peso total, selected customer, and staff attribution together for clean end-of-day handoffs.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="landing-workflow" id="how-it-works" aria-labelledby="workflow-heading">
            <div class="landing-container">
                <div class="landing-section-heading">
                    <p>Workflow</p>
                    <h2 id="workflow-heading">From selection to checkout in three steps.</h2>
                </div>
                <ol class="workflow-grid">
                    <li><span class="workflow-label">Start</span><h3>Open the counter</h3><p>Staff log in with their assigned account to access today’s active menu and register.</p></li>
                    <li><span class="workflow-label">02</span><h3>Ring up the order</h3><p>Select items, set quantities, choose walk-in or student customer, and charge the total in ₱.</p></li>
                    <li><span class="workflow-label">Track</span><h3>Stay stocked and synced</h3><p>Inventory deducts automatically and every completed checkout appears in Orders.</p></li>
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
                    <details><summary>Who can access CampusBite?<span aria-hidden="true"></span></summary><p>Only authorized canteen staff with an active account created by another authorized staff member can sign in and take orders.</p></details>
                    <details><summary>How are prices and totals formatted?<span aria-hidden="true"></span></summary><p>All menu prices, subtotals, and order history records are formatted natively in Philippine Pesos (₱).</p></details>
                    <details><summary>Does stock update automatically after an order?<span aria-hidden="true"></span></summary><p>Yes. Whenever an order is completed, the system verifies availability and deducts the quantity from active stock immediately.</p></details>
                    <details><summary>How do we handle sold-out or seasonal menu items?<span aria-hidden="true"></span></summary><p>Staff can update stock counts or archive items from the Products page without deleting past order history.</p></details>
                    <details><summary>Can we track both walk-in and named customers?<span aria-hidden="true"></span></summary><p>Yes. Cashiers can record quick walk-in orders or attach an existing customer profile when needed.</p></details>
                </div>
            </div>
        </section>

        <section class="landing-cta-section" aria-labelledby="cta-heading">
            <div class="landing-container">
                <div class="landing-cta-panel">
                    <h2 id="cta-heading">Ready to make the lunch rush easier?</h2>
                    <p>Sign in with your staff account to open the register and start taking orders.</p>
                    <a class="landing-button landing-button-inverted" href="{{ route('login') }}">Log in to CampusBite</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="landing-container">
            <div class="landing-footer-grid">
                <div class="landing-footer-brand">
                    <a class="landing-brand" href="#top"><img src="{{ asset('images/campusbite-mark.svg') }}" alt="" width="36" height="36"><span>CampusBite</span></a>
                    <p>A focused web-based POS built for school food counters.</p>
                    <small>Prices shown in Philippine pesos (₱).</small>
                </div>
                <div class="landing-footer-column"><h2>Product</h2><a href="#features">Features</a><a href="#how-it-works">How it works</a><a href="#faq">FAQ</a></div>
            </div>
            <div class="landing-footer-bottom"><span>© {{ now()->year }} CampusBite. All rights reserved.</span><span>School food counters, kept moving.</span></div>
        </div>
    </footer>
</body>
</html>
