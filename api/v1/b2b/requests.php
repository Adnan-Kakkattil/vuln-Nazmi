<?php
/**
 * Public B2B Requests API
 * Endpoint: /api/v1/b2b/requests.php
 * Handles B2B request submissions from the website
 * 
 * Methods:
 * - POST: Submit new B2B request
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../../../includes/config.php';

// Database connection
$pdo = getDbConnection();

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== 'POST') {
        sendResponse([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
    }
    
    // Submit new B2B request
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($data['name']) || empty($data['email']) || empty($data['phone']) || empty($data['company']) || empty($data['requirements'])) {
        sendResponse([
            'success' => false,
            'message' => 'Missing required fields: name, email, phone, company, requirements'
        ], 400);
    }
    
    // Prepare data
    $contactPerson = trim($data['name']);
    $companyName = trim($data['company']);
    $email = trim($data['email']);
    $phone = trim($data['phone']);
    $specialRequirements = trim($data['requirements']);
    $notes = !empty($data['notes']) ? trim($data['notes']) : null;
    
    // Extract additional fields if provided
    $businessType = !empty($data['business_type']) ? trim($data['business_type']) : null;
    $gstNumber = !empty($data['gst_number']) ? trim($data['gst_number']) : null;
    $addressLine1 = !empty($data['address_line1']) ? trim($data['address_line1']) : null;
    $addressLine2 = !empty($data['address_line2']) ? trim($data['address_line2']) : null;
    $city = !empty($data['city']) ? trim($data['city']) : null;
    $state = !empty($data['state']) ? trim($data['state']) : null;
    $pincode = !empty($data['pincode']) ? trim($data['pincode']) : null;
    $country = !empty($data['country']) ? trim($data['country']) : 'India';
    $monthlyVolume = !empty($data['monthly_volume']) ? trim($data['monthly_volume']) : null;
    $productCategories = !empty($data['product_categories']) ? (is_array($data['product_categories']) ? json_encode($data['product_categories']) : $data['product_categories']) : null;
    
    // Insert B2B request
    $stmt = $pdo->prepare("
        INSERT INTO b2b_requests (
            company_name,
            contact_person,
            email,
            phone,
            business_type,
            gst_number,
            address_line1,
            address_line2,
            city,
            state,
            pincode,
            country,
            monthly_volume_estimate,
            product_categories,
            special_requirements,
            status,
            notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
    ");
    
    $stmt->execute([
        $companyName,
        $contactPerson,
        $email,
        $phone,
        $businessType,
        $gstNumber,
        $addressLine1,
        $addressLine2,
        $city,
        $state,
        $pincode,
        $country,
        $monthlyVolume,
        $productCategories,
        $specialRequirements,
        $notes
    ]);
    
    $requestId = $pdo->lastInsertId();
    
    sendResponse([
        'success' => true,
        'message' => 'B2B request submitted successfully! Our team will contact you soon.',
        'data' => [
            'id' => $requestId,
            'message' => 'Thank you for your interest. We will get back to you shortly.'
        ]
    ], 201);
    
} catch (PDOException $e) {
    // Check for duplicate email or unique constraint violation
    if ($e->getCode() == 23000) {
        sendResponse([
            'success' => false,
            'message' => 'A request with this email already exists. Please contact us directly or use a different email.'
        ], 400);
    }
    
    sendResponse([
        'success' => false,
        'message' => 'An error occurred while submitting your request. Please try again later.'
    ], 500);
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ], 500);
}
