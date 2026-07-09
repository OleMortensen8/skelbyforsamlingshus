<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Skelby Forsamlinghus - Dit lokale mødested og arrangementssted">
  <meta name="theme-color" content="#2a5caa">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <title><?php echo $page_title ?? 'Skelby Forsamlinghus'; ?> - Skelby Forsamlinghus</title>

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
  <link rel="alternate icon" href="/assets/img/favicon.ico">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="/assets/css/system.css">
  <link rel="stylesheet" href="/assets/css/theme.css">

  <!-- Additional page-specific styles -->
  <?php if (isset($page_css)): ?>
    <link rel="stylesheet" href="/assets/css/pages/<?php echo htmlspecialchars($page_css); ?>">
  <?php endif; ?>

  <!-- Open Graph / Social Media Meta Tags -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? ''); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($page_title ?? 'Skelby Forsamlinghus'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($page_description ?? 'Dit lokale mødested og arrangementssted'); ?>">
  <meta property="og:image" content="/assets/img/og-image.jpg">

  <!-- Twitter Meta Tags -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? ''); ?>">
  <meta property="twitter:title" content="<?php echo htmlspecialchars($page_title ?? 'Skelby Forsamlinghus'); ?>">
  <meta property="twitter:description" content="<?php echo htmlspecialchars($page_description ?? 'Dit lokale mødested og arrangementssted'); ?>">
  <meta property="twitter:image" content="/assets/img/og-image.jpg">

  <!-- Security Headers -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;">
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
  <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
</head>
<body>
  <?php include __DIR__ . '/header.php'; ?>

  <main class="container">
    <?php if (isset($page_breadcrumb) && !empty($page_breadcrumb)): ?>
      <nav aria-label="Breadcrumb">
        <ul class="breadcrumb">
          <li><a href="/">Hjem</a></li>
          <?php foreach ($page_breadcrumb as $link => $label): ?>
            <li>
              <?php if ($link): ?>
                <a href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($label); ?></a>
              <?php else: ?>
                <span><?php echo htmlspecialchars($label); ?></span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
    <?php endif; ?>

    <!-- Flash Messages / Alerts -->
    <?php if (isset($_SESSION['alerts'])): ?>
      <?php foreach ($_SESSION['alerts'] as $alert): ?>
        <div class="alert alert-<?php echo htmlspecialchars($alert['type'] ?? 'info'); ?> alert-dismissible" role="alert">
          <?php if (!empty($alert['heading'])): ?>
            <h4 class="alert-heading"><?php echo htmlspecialchars($alert['heading']); ?></h4>
          <?php endif; ?>
          <?php echo htmlspecialchars($alert['message'] ?? ''); ?>
          <button type="button" class="alert-close" aria-label="Close"></button>
        </div>
      <?php endforeach; ?>
      <?php unset($_SESSION['alerts']); ?>
    <?php endif; ?>

    <!-- Page Content -->
    <?php
    if (isset($content_file) && file_exists($content_file)) {
      include $content_file;
    }
    ?>
  </main>

  <?php include __DIR__ . '/footer.php'; ?>

  <!-- Additional page-specific scripts -->
  <?php if (isset($page_js)): ?>
    <script src="/assets/js/pages/<?php echo htmlspecialchars($page_js); ?>"></script>
  <?php endif; ?>

  <!-- Inline debug info (only in development) -->
  <?php if (getenv('ENVIRONMENT') === 'development' && isset($_GET['debug'])): ?>
    <div class="debug-info" style="display: none;">
      <details>
        <summary>Debug Info</summary>
        <pre><?php var_dump($_SESSION); ?></pre>
      </details>
    </div>
  <?php endif; ?>
</body>
</html>
