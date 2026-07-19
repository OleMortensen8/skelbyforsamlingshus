<!DOCTYPE html>
<html lang="en">
<?php
$HereAndNow = date('Y', strtotime('now'));

// Pages with a form protected by the Cap CAPTCHA widget need the (locally
// vendored, CSP-friendly) widget script and its WASM solver.
$capProtectedPages = ['udlejning.php', 'blivMedlem.php'];
$capNeeded = in_array(basename($_SERVER['PHP_SELF']), $capProtectedPages, true);
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/css/normalize.min.css">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display|Roboto:814i&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <title>Skelby Forsamlingshus</title>
    <meta name="description" content="Sydfalster's Skelby Forsamlingshus - Et mødested for kultur, arrangementer, udlejning og fællesskab i Sydfalster.">
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