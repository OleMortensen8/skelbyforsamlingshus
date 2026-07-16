<?php
require_once "bootstrap.php";

use App\CapCaptcha;

$capCaptcha = new CapCaptcha();
include "assets/view/header.php";
?>
<link rel="stylesheet" href="assets/css/form.css" />
<main>
    <div id="centerColumn">
        <h1>Bliv Medlem</h1>
        <div class="intro">
            <p class="lead">Bliv medlem — få adgang og støt det lokale forsamlingshus.</p>
            <p>Som medlem får du ret ret til udlejning og et medlemsbevis som bevis på din støtte.</p>
        </div>
        <form id="form1" action="medlem.php" method="post">
            <?php if (!isset($_GET['status'])) { ?>
                <div class="large-group">
                    <div class="small-group">
                        <label for="name">Navn</label>
                        <input id="name" name="name" type="text" required>
                    </div>
                    <div class="small-group">
                        <label for="lastname">EfterNavn</label>
                        <input id="lastname" name="lastname" type="text" required>
                    </div>
                </div>

                <label for="adresse">Adresse</label>
                <input id="adresse" name="adresse" type="text" required>

                <div class="large-group">
                    <div class="small-group">
                        <label for="post">Post Nr.</label>
                        <input id="post" name="post" type="number" required>
                    </div>
                    <div class="small-group">
                        <label for="town">By</label>
                        <input id="town" name="town" type="text" required>
                    </div>
                </div>

                <label for="mail">Email</label>
                <input id="mail" name="mail" type="email" required>

                <label for="tlf">Telefon nr.</label>
                <input id="tlf" name="tlf" type="tel" required>

                <cap-widget data-cap-api-endpoint="<?php echo htmlspecialchars($capCaptcha->getApiEndpoint(), ENT_QUOTES, 'UTF-8'); ?>" required></cap-widget>

                <input type="submit" class="btn" value="Send Forepørgsel">
            <?php } else { ?>
                <div class="confirmation-message">
                    <h2>Din anmodning om medlemskab af Skelby Forsamlingshus er modtaget.</h2>
                    <h3>Betal 100 kr. pr. år til Denne Konto:</h3>
                    <div class="account-info">
                        <p>reg #: 2650</p>
                        <p>Konto #: 6403019702</p>
                    </div>
                    <p class="confirmation-note">De vil modtage et medlemsbevis når inbetallingen er bekræftet.</p>
                    <p>Du kan holde dig orienteret om arrangementer og nyheder på vores <a
                            href="https://www.facebook.com/Skelbysydfalsterforsamlingshus/">Facebook-side</a></p>
                </div>
            <?php } ?>
        </form>

    </div>
    </div>
</main>

<?php include "assets/view/footer.php"; ?>