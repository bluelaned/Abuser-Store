<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | ABUSER STORE</title>
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
        <h1>Privacy Policy</h1>
        <p><em>Last updated: {{ date('F j, Y') }}</em></p>

        <h2>1. Information We Collect</h2>
        <p>When you visit ABUSER STORE and make a purchase, we collect certain information necessary to process your order and provide you with a smooth experience. This includes:</p>
        <ul>
            <li>Your Email Address (for order delivery and communication)</li>
            <li>Your Discord Account ID and Username (when you authenticate via Discord)</li>
            <li>Transaction history and payment metadata</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <p>We use the collected information for the following purposes:</p>
        <ul>
            <li>To process your transactions and deliver the purchased digital goods.</li>
            <li>To send order confirmations and invoices via Discord Direct Messages.</li>
            <li>To prevent fraud and enforce our Terms of Service (e.g., limiting promo code usage).</li>
        </ul>

        <h2>3. Data Protection and Sharing</h2>
        <p>We do not sell, trade, or otherwise transfer your personally identifiable information to outside parties, except trusted third parties who assist us in operating our website, conducting our business, or servicing you (such as Payment Gateways like Midtrans and Stripe), so long as those parties agree to keep this information confidential.</p>

        <h2>4. Data Retention</h2>
        <p>We retain your personal information only for as long as necessary to provide you with our services and for legitimate and essential business purposes, such as maintaining the performance of the store, making data-driven business decisions, complying with our legal obligations, and resolving disputes.</p>

        <h2>5. Your Rights</h2>
        <p>If you wish to delete your account or have your data removed from our systems, please contact our support team via our Discord server.</p>
    </div>

    <footer>
        &copy; {{ date('Y') }} ABUSER STORE. All Rights Reserved.
    </footer>
</body>
</html>
