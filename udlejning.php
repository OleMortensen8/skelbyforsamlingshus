<?php
include "bootstrap.php";

use App\Booking;
use App\BookableCell;
use App\Calendar;

$booking = new Booking();

$bookableCell = new BookableCell($booking);
$bookableCell->routeActions();
include "./assets/view/header.php";
?>
<main>
    <div id="centerColumn">
        <div style="position:absolute;z-index:-1;left:0; width:100%;max-width:100%; display:flex;flex-direction:row;justify-content:space-between;">
            <img class="banner" anonymous src="assets/img/signal-2018-09-23-143513.jpg">
            <img class="banner" src="assets/img/signal-2018-09-23-143531.jpg">
            <img class="banner" src="assets/img/signal-2018-09-23-143624.jpg">
        </div>

        <div class="pricing-info">
            <h3>Priser for udlejning</h3>
            <div class="account-details">
                <p><strong>Reg #:</strong> 2650</p>
                <p><strong>Konto #:</strong> 6403019702</p>
            </div>
            <ul class="pricing-list">
                <li>Depositum - 50% af lejen.</li>
                <li>Hele huset - 2000 kr. - Max 150 personer.</li>
                <li>Små Sale - 1000 kr. - Max 30 personer</li>
                <li>El - 5 kr Pr. Kw/timen</li>
                <li>Rengøring - 600 kr.</li>
            </ul>
        </div>
        <?php

        $calendar = new Calendar();
        $calendar->setSundayFirst(false);
        $calendar->attachObserver('showCell', $bookableCell);

        echo $calendar->show();

        ?>
    </div>
    <!-- Trigger/Open The Modal -->
    <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <?php $bookableCell->bookingForm(); ?>
            <p id="submission"></p>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script>
        var modal;
        $(document).ready(function() {
            modal = $("#myModal");
            var btn = $(".open");
            var span = $(".close");
            var sub = $("#sub");
            var sdate = $("#sdate");
            var enddate = $("#enddate");
            var date = $("#date");

            btn.on("click", function() {
                var value = $(this).attr("value");
                sdate.val(value);
                date.html("Fra: " + sdate.val());

                modal.css("display", "block");
            });

            span.on("click", function() {
                modal.css("display", "none");
            });

            sub.on("click", function(event) {
                event.preventDefault();
                submissionMessage();
            });
        });

        function submissionMessage() {
            var form = $('#form1');
            var navn = $("form[name='form1'] input[name='navnet']").val();
            var adresse = $("form[name='form1'] input[name='adresse']").val();
            var telefon = $("form[name='form1'] input[name='telefon']").val();
            var mail = $("form[name='form1'] input[name='mail']").val();
            form.css("display", "none");
            var submissionElement = $("#submission");
            submissionElement.html("<img style='width:80px;' src='assets/img/Spinner-1s-200px.gif'></img>");
            if (navn === "") {
                submissionElement.html("Navne feltet Skal Udfyldes<br/>");
            }
            if (adresse === "") {
                submissionElement.append("Adresse feltet Skal Udfyldes<br/>");
            }
            if (telefon === "") {
                submissionElement.append("Telefon feltet Skal Udfyldes<br/>");
            }
            if (mail === "") {
                submissionElement.append("Mail feltet Skal Udfyldes<br/>");
            }

            if (navn !== "" && adresse !== "" && telefon !== "" && mail !== "") {
                $.ajax({
                    type: "POST",
                    url: "udlejning.php?add",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            submissionElement.html("<div style='text-align:center; padding: 20px;'><p style='color: green; font-weight: bold; font-size: 16px;'>✓ Booking bekræftet!</p><p>" + response.message + "</p><p style='font-size: 14px; margin-top: 10px;'>Vi kontakter dig inden for 5 dage.</p></div>");
                            setTimeout(function() {
                                modal.css("display", "none");
                                $('#submission').html("");
                                form.css('display', 'grid');
                                location.reload();
                            }, 3000);
                        } else {
                            submissionElement.html("<div style='text-align:center; padding: 20px;'><p style='color: red; font-weight: bold;'>Fejl: " + response.message + "</p></div>");
                            form.css('display', 'grid');
                        }
                    },
                    error: function(xhr, status, error) {
                        submissionElement.html("<div style='text-align:center; padding: 20px;'><p style='color: red; font-weight: bold;'>Fejl ved indsendelse. Prøv igen senere.</p></div>");
                        form.css('display', 'grid');
                        console.error('AJAX Error:', status, error, xhr.responseText);
                    }
                });
            }
        }

        // When the user clicks on <span> (x), close the modal
        $('.close').on('click', function() {
            var form = $('#form1');
            modal.css("display", "none");
            $('#submission').html("");
            form.css('display', 'grid');
            window.location.reload();
        });

        // When the user clicks anywhere outside of the modal, close it
        $(window).on('click', function(event) {
            if (event.target == modal[0]) {
                var form = $('#form1');
                modal.css("display", "none");
                $('#submission').html("");
                form.css('display', 'grid');
                window.location.reload();
            }
        });
    </script>
    <script>
        function handleReloadForEitherParameter(parametersToCheck) {
            // Parse the URL search string
            const urlParams = new URLSearchParams(window.location.search);

            // Check if any of the specified parameters are present
            let shouldReload = false;
            parametersToCheck.forEach(param => {
                if (urlParams.get(param)) {
                    shouldReload = true; // Reload if any one parameter is present
                    urlParams.delete(param); // Remove the parameter from the URL

                    // Save success message in localStorage based on the parameter
                    if (param === "book") {
                        localStorage.setItem('successMessage', 'Booking was successful.');
                    } else if (param === "delete") {
                        localStorage.setItem('successMessage', 'Deletion was successful.');
                    }
                }
            });

            if (shouldReload) {
                console.log('Reloading the page because one of the specified parameters is present.');

                // Update the URL without the parameters
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, document.title, newUrl);

                // Show spinner before reloading
                const spinnerOverlay = document.createElement('div');
                spinnerOverlay.id = 'spinner-overlay';
                spinnerOverlay.innerHTML = `
                <div id="spinner-container">
                    <img src="assets/img/Spinner-1s-200px.gif" alt="Loading..." />
                    <p>Processing your request...</p>
                </div>
            `;
                document.body.appendChild(spinnerOverlay);

                // Reload the page after a slight delay to display the spinner clearly
                setTimeout(() => {
                    window.location.reload();
                }, 100); // Allows spinner to render fully
            }
        }

        $(document).ready(function() {
            // Specify the parameters (check for 'book' OR 'delete')
            const parametersToCheck = ['book', 'delete'];
            handleReloadForEitherParameter(parametersToCheck);

            // Show success message after reload (if any)
            const successMessage = localStorage.getItem('successMessage');
            if (successMessage) {
                const messageContainer = document.createElement('div');
                messageContainer.id = 'success-message';
                messageContainer.innerHTML = `
                <div id="message-box">
                    <p>${successMessage}</p>
                    <button id="close-message">OK</button>
                </div>
            `;
                document.body.appendChild(messageContainer);

                // Clear the message from localStorage to avoid showing it again
                localStorage.removeItem('successMessage');

                // Handle message dismissal
                $('#close-message').on('click', function() {
                    $('#success-message').fadeOut(function() {
                        $(this).remove();
                    });
                });
            }
        });
    </script>
</main>
<?php include "assets/view/footer.php"; ?>