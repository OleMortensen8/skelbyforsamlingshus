<?php

namespace App;

class BookableCell
{
    private $booking;

    private $currentURL;

    private $capCaptcha;
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->currentURL = htmlentities($_SERVER['REQUEST_URI']);
        $this->capCaptcha = new CapCaptcha();

        // Include secure session configuration
        require_once __DIR__ . '/../config/session_config.php';

        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Get the Cap CAPTCHA widget's public API endpoint, for rendering in the form.
     * @return string
     */
    public function getCapApiEndpoint()
    {
        return $this->capCaptcha->getApiEndpoint();
    }

    /**
     * Get the current CSRF token
     * @return string The CSRF token
     */
    public function getCsrfToken()
    {
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify the CSRF token
     * @param string $token The token to verify
     * @return bool True if token is valid, false otherwise
     */
    private function verifyCsrfToken($token)
    {
        if (!isset($_SESSION['csrf_token']) || !isset($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function update(Calendar $cal)
    {
        if ($this->isDateBooked($cal->getCurrentDate()) && $this->isDatePending($cal->getCurrentDate())) {
            return $cal->cellContent =
                $this->pendingCell($cal->getCurrentDate());
        }

        if ($this->isDateBooked($cal->getCurrentDate()) && !$this->isDatePending($cal->getCurrentDate())) {
            return $cal->cellContent =
                $this->bookedCell($cal->getCurrentDate());
        }

        if (!$this->isDateBooked($cal->getCurrentDate())) {
            return $cal->cellContent =
                $this->openCell($cal->getCurrentDate());
        }
    }

    // Validation functions
    private function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function validateDate($date)
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $dt = \DateTime::createFromFormat('Y-m-d', $date);
            return $dt && $dt->format('Y-m-d') === $date;
        }
        return false;
    }

    private function validateInteger($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function sanitizeString($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    private function validateBookingIds($ids)
    {
        if (empty($ids)) {
            return false;
        }

        $idArray = explode(',', $ids);
        foreach ($idArray as $id) {
            if (!$this->validateInteger($id)) {
                return false;
            }
        }

        return $idArray;
    }

    public function routeActions()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ob_start();
            header('Content-Type: application/json');
            $response = ['status' => 'error', 'message' => 'Invalid request.']; // default response
            // Handle the AJAX request    
            // ====Add booking====
            if (isset($_POST['add'])) {
                // Verify CSRF token
                $csrfToken = isset($_POST['csrf_token']) ? $this->sanitizeString($_POST['csrf_token']) : '';
                if (!$this->verifyCsrfToken($csrfToken)) {
                    $response = ['status' => 'error', 'message' => 'Invalid security token. Please refresh the page and try again.'];
                    ob_end_clean();
                    echo json_encode($response);
                    exit;
                }

                // Verify Cap CAPTCHA token (server-side; the widget's client-side
                // solve is not trusted on its own)
                $capResult = $this->capCaptcha->verify($_POST['cap-token'] ?? '');
                if (!$capResult['success']) {
                    $response = ['status' => 'error', 'message' => $capResult['message']];
                    ob_end_clean();
                    echo json_encode($response);
                    exit;
                }

                // Validate and sanitize inputs
                $startDate = isset($_POST['startdate']) ? $this->sanitizeString($_POST['startdate']) : '';
                $endDate = isset($_POST['enddate']) ? $this->sanitizeString($_POST['enddate']) : '';
                $name = isset($_POST['navnet']) ? $this->sanitizeString($_POST['navnet']) : '';
                $email = isset($_POST['mail']) ? $this->sanitizeString($_POST['mail']) : '';
                $tel = isset($_POST['telefon']) ? $this->sanitizeString($_POST['telefon']) : '';
                $adresse = isset($_POST['adresse']) ? $this->sanitizeString($_POST['adresse']) : '';
                $postalCode = isset($_POST['postnr']) ? $this->sanitizeString($_POST['postnr']) : '';
                $town = isset($_POST['by']) ? $this->sanitizeString($_POST['by']) : '';
                $sal = isset($_POST['sal']) ? $this->sanitizeString($_POST['sal']) : '';

                // Validate required fields
                if (empty($name) || empty($adresse) || empty($postalCode) || empty($town) || empty($tel) || empty($startDate)) {
                    $response = ['status' => 'error', 'message' => 'All required fields must be filled out.'];
                    ob_end_clean();
                    echo json_encode($response);
                    exit;
                }

                // Validate email if provided
                if (!empty($email) && !$this->validateEmail($email)) {
                    $response = ['status' => 'error', 'message' => 'Please enter a valid email address.'];
                    ob_end_clean();
                    echo json_encode($response);
                    exit;
                }

                // Validate dates
                if (!$this->validateDate($startDate)) {
                    $response = ['status' => 'error', 'message' => 'Invalid start date format.'];
                    ob_end_clean();
                    echo json_encode($response);
                    exit;
                }

                // Create pending day array
                $pendingDay = [$startDate, $endDate];

                // Create customer and booking
                $customer = new Customer($name, $email); // New customer creation
                $bookingObj = new Booking($customer);
                $response = $bookingObj->createBooking($pendingDay, 0);  // capture the booking ID

                // Decode the response
                $decodedResponse = json_decode($response, true);
                if ($decodedResponse['status'] === 'success') {
                    // Use PHPMailer to send email with booking IDs
                    $bookingIds = $decodedResponse['bookingIds'];
                    include('phpmailer.php');
                    include('phpmailer_2.php');
                    // You might need to modify phpmailer.php to handle the bookingIds array
                }
                ob_end_clean();
                echo $response; // Echo the JSON response
                exit;
            }
        }
        // ====Delete booking==== 
        if (isset($_GET['delete'])) {
            // Validate booking IDs first
            $bookingIds = isset($_GET['ids']) ? $this->validateBookingIds($_GET['ids']) : false;

            if (!$bookingIds) {
                // Handle invalid booking IDs
                echo "Invalid booking IDs.";
                return;
            }

            // Verify CSRF token if provided
            $csrfToken = isset($_GET['csrf_token']) ? $this->sanitizeString($_GET['csrf_token']) : '';

            // If CSRF token is provided but invalid, show error
            if ($csrfToken !== '' && !$this->verifyCsrfToken($csrfToken)) {
                echo "Invalid security token. Please refresh the page and try again.";
                return;
            }

            // If we get here, either the token is valid or we're allowing the action from an email link
            $customerId = $this->booking->deleteBooking($bookingIds);
            $customer = new Customer();
            if (!$customer->hasBookings($customerId)) {
                $customer->deleteCustomer($customerId);
            }
        }

        if (isset($_GET['book'])) {
            // Validate booking IDs first
            $bookingIds = isset($_GET['ids']) ? $this->validateBookingIds($_GET['ids']) : false;

            if (!$bookingIds) {
                // Handle invalid booking IDs
                echo "Invalid booking IDs.";
                return;
            }

            // Verify CSRF token if provided
            $csrfToken = isset($_GET['csrf_token']) ? $this->sanitizeString($_GET['csrf_token']) : '';

            // If CSRF token is provided but invalid, show error
            if ($csrfToken !== '' && !$this->verifyCsrfToken($csrfToken)) {
                echo "Invalid security token. Please refresh the page and try again.";
                return;
            }

            // If we get here, either the token is valid or we're allowing the action from an email link
            $this->booking->approveBooking($bookingIds);
        }
    }

    private function openCell($date)
    {
        $today = date('Y-m-d', strtotime('now'));
        if ($date >= $today) {
            return '<div class="open" value="' . date('Y-m-d', strtotime($date)) . '">' . date('j', strtotime($date)) . '</div>';
        } else {
            return '<div class="booked" style="background-color:white;">' . date('j', strtotime($date)) . '</div>';
        }
    }

    private function pendingCell($date)
    {
        $today = date('Y-m-d', strtotime('now'));
        if ($date >= $today) {
            return '<div class="pending">' . date('j', strtotime($date)) . '</div>';
        } else {
            return '<div class="pending" style="background-color:white;">' . date('j', strtotime($date)) . '</div>';
        }
    }


    private function isDatePending($date)
    {
        return in_array($date, $this->pendingDates());
    }

    private function pendingDates()
    {
        return array_map(function ($record) {
            if ($record['approved'] == false) {
                return $record['booking_date'];
            }
        }, $this->booking->index());
    }


    private function isDateBooked($date)
    {
        return in_array($date, $this->bookedDates());
    }

    private function bookedDates()
    {
        return array_map(function ($record) {
            return $record['booking_date'];
        }, $this->booking->index());
    }
    private function bookedCell($date)
    {
        $today = date('Y-m-d', strtotime('now'));
        if ($date >= $today) {
            return '<div class="booked">' . date('j', strtotime($date)) . '</div>';
        } else {
            return '<div class="booked" style="color:white;">' . date('j', strtotime($date)) . '</div>';
        }
    }

    public function bookingForm()
    {
        echo '<form id="form1" class="booking-form" method="post" action="">
            <span class="close">x</span>
                <h2 class="form-title">Book Forsamlingshuset</h2>
                <input type="hidden" name="add" />
                <input type="hidden" name="csrf_token" value="' . $this->getCsrfToken() . '" />

                <div class="form-group">
                    <label for="navnet">Navn:</label>
                    <input required type="text" id="navnet" name="navnet" placeholder="Navn" />
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse:</label>
                    <input required type="text" id="adresse" name="adresse" placeholder="Adresse" />
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="postnr">Postnr:</label>
                        <input required type="text" id="postnr" name="postnr" placeholder="Postnr" />
                    </div>
                    <div class="form-group half">
                        <label for="by">By:</label>
                        <input required type="text" id="by" name="by" placeholder="By" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="telefon">Telefon:</label>
                    <input required type="tel" id="telefon" name="telefon" placeholder="Telefon" />
                </div>

                <div class="form-group">
                    <label for="mail">Email:</label>
                    <input type="email" id="mail" name="mail" placeholder="Email"/>
                </div>

                <div class="form-group">
                    <label class="label-header">Hvilken Sal Ønskes at bookes:</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="storesal" name="sal" value="begge sale" />
                            <label for="storesal">Begge sale</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="lillesal" name="sal" value="lillesal" />
                            <label for="lillesal">Lille sal</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <input id="sdate" type="hidden" name="startdate" />
                    <label id="date" class="date-label"></label>
                </div>

                <div class="form-group">
                    <label for="enddate">Periode:</label>
                    <select id="enddate" name="enddate" class="form-select">
                        <option value="0">Idag</option>
                        <option value="1">+1 Dag</option>
                        <option value="2">+2 Dage</option>
                        <option value="3">+3 Dage</option>
                        <option value="4">+4 Dage</option>
                        <option value="5">+5 Dage</option>
                        <option value="6">+6 Dage</option>
                    </select>
                </div>

                <div class="form-group">
                    <cap-widget data-cap-api-endpoint="' . htmlspecialchars($this->getCapApiEndpoint(), ENT_QUOTES, 'UTF-8') . '" required></cap-widget>
                </div>

                <div class="form-group">
                    <input id="sub" class="submit" type="submit" value="Book" />
                </div>
            </form>';

        // Add JavaScript to include CSRF token in AJAX requests
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // Add CSRF token to all AJAX requests
                var originalSend = XMLHttpRequest.prototype.send;
                XMLHttpRequest.prototype.send = function(data) {
                    if (this._csrfSent) {
                        return originalSend.apply(this, arguments);
                    }

                    if (data instanceof FormData) {
                        data.append("csrf_token", "' . $this->getCsrfToken() . '");
                    } else if (typeof data === "string") {
                        if (data.length) {
                            data += "&";
                        }
                        data += "csrf_token=' . $this->getCsrfToken() . '";
                    }

                    this._csrfSent = true;
                    return originalSend.call(this, data);
                };
            });
            </script>';
    }
}
