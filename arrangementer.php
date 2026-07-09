<?php include "bootstrap.php";
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
                <?php echo $event->getArangementer($xml); ?>
            </div>
        </div>
    </main>
<?php include "assets/view/footer.php"; ?>