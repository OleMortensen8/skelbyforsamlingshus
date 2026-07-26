<!DOCTYPE html>
<html lang="da">
<?php
$HereAndNow = date('Y', strtotime('now'));

// Pages with a form protected by the Cap CAPTCHA widget need the (locally
// vendored, CSP-friendly) widget script and its WASM solver.
$capProtectedPages = ['udlejning.php', 'blivMedlem.php'];
$capNeeded = in_array(basename($_SERVER['PHP_SELF']), $capProtectedPages, true);

// Pages can set $pageTitle/$pageDescription/$pageImage before including this
// header for page-specific <title>/meta description/Open Graph content.
// Falls back to sensible site-wide defaults when a page doesn't set them.
$siteName = "Skelby Forsamlingshus";
$defaultDescription = "Sydfalster's Skelby Forsamlingshus - Et mødested for kultur, arrangementer, udlejning og fællesskab i Sydfalster.";
$pageTitle = $pageTitle ?? null;
$pageDescription = $pageDescription ?? $defaultDescription;
$pageImage = $pageImage ?? "/assets/img/skelby/setting3.jpg";
$fullTitle = $pageTitle ? ($pageTitle . " – " . $siteName) : $siteName;
$domain = getenv('APP_DOMAIN') ?: 'skelby-forsamlingshus.dk';
$canonicalUrl = 'https://' . $domain . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
$absoluteImage = 'https://' . $domain . '/' . ltrim($pageImage, '/');
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/css/normalize.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <title><?php echo htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- Open Graph / Twitter Card tags, for link previews on Facebook/Messenger etc. -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $canonicalUrl; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($absoluteImage, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:locale" content="da_DK">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($absoluteImage, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($capNeeded): ?>
        <script>
            // Serve the WASM solver from our own domain instead of the jsdelivr
            // CDN, so the strict CSP doesn't need a third-party connect-src entry.
            window.CAP_CUSTOM_WASM_URL = "assets/js/vendor/cap_wasm_bg.wasm";
        </script>
        <script src="assets/js/vendor/cap-widget.min.js"></script>
    <?php endif; ?>
</head>
<body<?php echo (basename($_SERVER['PHP_SELF']) !== 'index.php') ? ' class="secondary-page"' : ''; ?>>
    <div id="wrapper">
        <header>
            <h1><a href="/">Sydfalster's Skelby Forsamlingshus</a></h1>
            <nav>
                <a href="/">Forside</a>
                <a href="gallery">Galleri</a>
                <a href="arrangementer">Arrangementer</a>
                <a href="udlejning">Udlejning</a>
                <a href="vedtægter">Vedtægter</a>
                <a href="bestyrelse">Bestyrelse</a>
                <a href="blivMedlem">Bliv Medlem</a>
                <a href="kontakt">Kontakt</a>
            </nav>
        </header>