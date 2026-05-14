<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | ABUSER STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        .page-content { max-width: 800px; margin: 60px auto; padding: 40px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .page-content h1 { font-size: 2.5rem; margin-bottom: 24px; color: var(--text-main); }
        .page-content h2 { font-size: 1.5rem; margin-top: 32px; margin-bottom: 16px; color: var(--text-main); }
        .page-content p, .page-content li { color: var(--text-muted); line-height: 1.7; margin-bottom: 16px; font-size: 1rem; }
        .page-content ul { margin-left: 20px; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary); text-decoration: none; font-weight: 600; margin-bottom: 20px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <script>if (localStorage.getItem('abuser_theme') !== 'light') document.documentElement.classList.add('dark-mode');</script>

    <div class="page-content">
        <a href="/" class="back-link">← Back to Home</a>
        <h1>Terms of Service</h1>
        <p><em>Last updated: {{ date('F j, Y') }}</em></p>

        <h2>1. Acceptance of Terms</h2>
        <p>By accessing and using ABUSER STORE, you accept and agree to be bound by the terms and provision of this agreement. In addition, when using these particular services, you shall be subject to any posted guidelines or rules applicable to such services.</p>

        <h2>2. Digital Products</h2>
        <p>All sales of digital products are final. Because digital goods cannot be returned, we do not offer refunds once the product has been delivered to you via email or Discord.</p>
        <ul>
            <li>You are responsible for ensuring that you are purchasing the correct product.</li>
            <li>Any issues with the product must be reported to our support team within 24 hours of purchase.</li>
        </ul>

        <h2>3. Promo Codes and Vouchers</h2>
        <p>Promo codes are limited to one use per user account unless explicitly stated otherwise. We reserve the right to cancel any transaction that abuses the promo code system or circumvents our limitations.</p>

        <h2>4. User Conduct</h2>
        <p>You agree to use our services only for lawful purposes. You must not use our services in any way that causes, or may cause, damage to the website or impairment of the availability or accessibility of the store.</p>

        <h2>5. Modifications</h2>
        <p>We reserve the right to modify these terms at any time. Your continued use of the site following any such modification constitutes your agreement to follow and be bound by the terms as modified.</p>
    </div>

    <footer>
        &copy; {{ date('Y') }} ABUSER STORE. All Rights Reserved.
    </footer>
</body>
</html>
