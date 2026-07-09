<!DOCTYPE html>
<html lang="en">
<?php $HereAndNow = date('Y', strtotime('now'));?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/css/normalize.min.css">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display|Roboto:300i&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="assets/css/main.css">
    <title>Skelby Forsamlingshus</title>
    <meta name="description" content="Sydfalster's Skelby Forsamlingshus - Et mødested for kultur, arrangementer, udlejning og fællesskab i Sydfalster.">
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