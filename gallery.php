<?php include "assets/view/header.php"; ?>
<?php if (!isset($_GET['group'])) { ?>
    <style>
        /* Base styles for the gallery grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            width: 95%;
            margin: 5px auto 0 auto;
        }

        /* Styles for the gallery items */
        .gallery-item {
            width: 100%;
            text-align: center;
        }

        /* Styles for the front page images */
        .img-front {
            width: 100%;
            height: 500px; /* Fixed height for all images */
            max-width: 100%;
            object-fit: cover; /* Maintain aspect ratio while filling the container */
            transition: transform 0.3s ease;
        }

        .img-front:hover {
            transform: scale(1.05);
        }

        /* Media query for mobile devices */
        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: 1fr;
            }

            /* Adjust image height for smaller screens */
            .img-front {
                height: 200px; /* Smaller height for mobile devices */
            }
        }
    </style>
    <div class="gallery-grid">
        <div class="gallery-item">
            <a href="http://skelby-forsamlingshus.dk/gallery.php?group=inspiration">
                <?php include_once "functions.php";
                imagepicker('inspiration'); ?>
                <h1>Inspiration</h1>
            </a>
        </div>
        <div class="gallery-item">
            <a href="http://skelby-forsamlingshus.dk/gallery.php?group=gamlebilleder">
                <?php include_once "functions.php";
                imagepicker('gamlebilleder'); ?>
                <h1>Gamle Billeder</h1>
            </a>
        </div>
    </div>
<?php }else{
    $allowedGroups = ['inspiration', 'gamlebilleder'];
    $group = $_GET['group'] ?? null;

    if (!$group || !in_array($group, $allowedGroups, true)) {
        echo "<h1>Gallery not found</h1>";
        exit;
    }

    $files = iterator_to_array(new RecursiveDirectoryIterator('assets/img/skelby/' . $group, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), true);
    ?>
    <div id="gallery-selection" <?php if (empty($files)) {
        echo "style='display:grid;grid-template-columns:1fr';";
    } ?> >
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" type="text/javascript"></script>

<link href="https://cdn.jsdelivr.net/npm/nanogallery2@3/dist/css/nanogallery2.min.css" rel="stylesheet" type="text/css">
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/nanogallery2@3/dist/jquery.nanogallery2.min.js"></script>
    <?php
    ksort($files);
    natsort($files);
    if (empty($files)) {
        echo "<h1 style='margin: 0 auto; padding-top:180px;'>Billederne Kommer Snareligst</h1>";
    }

    foreach ($files as $file) {
        // Skip directories to ensure only files are processed
        if ($file->isDir()) {
            continue;
        }


        // Original file path
        $originalPath = $file->getPathname();

        // Directory where thumbnails are stored, relative to the original file's directory or an absolute path
        $thumbnailDir = "assets/img/skelby/" . $_GET['group'] . "/thumbnails";

        // Extract the filename from the original path
        $filename = basename($originalPath);

        // Remove the 'original_' prefix from the filename (if present) and prepend with 'thumbnail_'
        $thumbnailFilename = 'thumbnail_' . $filename;

        // Construct the full path to the thumbnail
        $thumbnailPath = $thumbnailDir . '/' . $thumbnailFilename;

        // Ensure paths are web-friendly by removing any leading directory separators and using relative paths
        $webThumbnailPath = $thumbnailPath;
        $webOriginalPath = 'assets/img/skelby/' . $_GET['group'] . '/' . $filename;

        // Note: You might need to adjust the path transformation logic based on your actual file structure and naming conventions
        echo "<img class='img' src='" . $webThumbnailPath . "' data-ngsrc='" . $webOriginalPath . "' data-nanogallery2-lightbox alt='ForsamlingsHuset i Skelby'>";
    }
    ?>

<?php }
?>
    <script>
        // Function to update image height based on width
        function updateImageHeight() {
            var gridItems = document.querySelectorAll('.img');
            gridItems.forEach(function (item) {
                var computedStyle = getComputedStyle(item);
                var width = parseFloat(computedStyle.width); // Get computed width as a number
                item.style.height = (width * 3 / 4) + 'px'; // Aspect ratio of 4:3
                var text = item.style.height + ' is 3/4 of ' + width;
                console.log(text);
            });
        }

        // Initial height update
        updateImageHeight();

        // Listen for changes to width and update height accordingly
        window.addEventListener('resize', updateImageHeight);

        // You might also want to watch for other changes that could affect width,
        // such as font size changes or content changes, and trigger updateHeight() accordingly.
    </script>
    </div>
<?php include "assets/view/footer.php"; ?>
