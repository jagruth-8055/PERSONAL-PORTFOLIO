<?php
// 1. Set CORS Headers (Crucial for AJAX/Fetch submissions from the front-end)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// 2. Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method Not Allowed. Only POST requests are accepted."]);
    exit();
}

// --- Data Collection and Sanitization ---
try {
    // Check if data is coming from the traditional POST (application/x-www-form-urlencoded)
    if (!empty($_POST)) {
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $message = htmlspecialchars(trim($_POST['message']));
    } 
    // Otherwise, check for JSON input (common with modern JS fetch)
    else {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null) {
             throw new Exception("Invalid JSON input.");
        }
        $name = htmlspecialchars(trim($data['name'] ?? ''));
        $email = htmlspecialchars(trim($data['email'] ?? ''));
        $message = htmlspecialchars(trim($data['message'] ?? ''));
    }

    // --- Basic Validation ---
    if (empty($name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        echo json_encode(["success" => false, "message" => "Please fill all required fields and ensure the email is valid."]);
        exit();
    }

    // --- 3. Primary Action (Simulated Email/DB Save) ---

    // Replace this section with your actual logic, such as:
    // 1. Emailing the details (using PHP's mail() or a library like PHPMailer)
    $to = "your_email@example.com"; // Your email address
    $subject = "New Portfolio Contact from: " . $name;
    $body = "Name: " . $name . "\n";
    $body .= "Email: " . $email . "\n";
    $body .= "Message: " . $message . "\n";
    $headers = "From: " . $email; // Set the sender to the user's email

    // 2. Saving to a database (MySQL, PostgreSQL, etc.)
    // Example: $db->query("INSERT INTO inquiries (name, email, message) VALUES ('$name', '$email', '$message')");

    if (mail($to, $subject, $body, $headers)) {
        // 4. Success Response
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Message sent successfully! Thank you for contacting me."]);
    } else {
        // 5. Error Response (If mail function fails)
        http_response_code(500); // Internal Server Error
        echo json_encode(["success" => false, "message" => "Failed to send message. Please try again later."]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An unexpected error occurred: " . $e->getMessage()]);
}
?>