<?php
/**
 * Header Component
 * Main navigation and branding
 */
?>
<header>
  <div class="container header-content">
    <a href="/" class="logo">
      <span>🏛️</span>
      <span>Skelby Forsamlinghus</span>
    </a>

    <button class="menu-toggle" aria-label="Toggle menu" aria-expanded="false">
      <span>☰</span>
    </button>

    <nav>
      <ul class="nav-primary">
        <li><a href="/" class="nav-link">Hjem</a></li>
        <li><a href="/arrangementer.php" class="nav-link">Arrangementer</a></li>
        <li><a href="/udlejning.php" class="nav-link">Udlejning</a></li>
        <li><a href="/bestyrelse.php" class="nav-link">Bestyrelse</a></li>
        <li><a href="/gallery.php" class="nav-link">Galeri</a></li>
        <li><a href="/kontakt.php" class="nav-link">Kontakt</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="/medlem.php" class="nav-link">Min Profil</a></li>
          <li><a href="/logout.php" class="nav-link">Log ud</a></li>
        <?php else: ?>
          <li><a href="/login.php" class="nav-link">Log ind</a></li>
          <li><a href="/blivMedlem.php" class="nav-link btn btn-primary">Bliv medlem</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>
