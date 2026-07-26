<?php include "bootstrap.php";
$pageTitle = "Arrangementer";
$pageDescription = "Se kommende arrangementer i Skelby Forsamlingshus.";
include "assets/view/header.php";
// Define current date for the heading
$HereAndNow = date('Y');
?>
<main>
    <div id="centerColumn">
        <div>
            <h1><?php echo $HereAndNow; ?>'s Arrangementer</h1>
        </div>
        <div>
            <?php
            $eventJsonLd = [];
            foreach ($xml->children() as $arrangement) {
                $title = trim((string)$arrangement->title);
                $date = trim((string)$arrangement->date);
                $time = trim((string)$arrangement->time);
                if ($title === '' || $date === '') {
                    continue;
                }
                $startDate = $date . ($time !== '' ? 'T' . $time . ':00' : '');
                $eventJsonLd[] = [
                    "@context" => "https://schema.org",
                    "@type" => "Event",
                    "name" => $title,
                    "startDate" => $startDate,
                    "location" => [
                        "@type" => "Place",
                        "name" => trim((string)$arrangement->location) ?: "Skelby Forsamlingshus",
                        "address" => "Gl. Landevej 66, 4874 Gedser"
                    ],
                    "description" => trim((string)$arrangement->description)
                ];
            }
            ?>
            <?php if (!empty($eventJsonLd)): ?>
                <script type="application/ld+json">
                    <?php echo json_encode(count($eventJsonLd) === 1 ? $eventJsonLd[0] : $eventJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
                </script>
            <?php endif; ?>
            <?php echo $event->getArangementer($xml); ?>
        </div>
    </div>
</main>
<?php include "assets/view/footer.php"; ?>