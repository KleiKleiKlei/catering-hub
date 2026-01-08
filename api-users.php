<?php

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);


ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'backend/config.php';

$request_method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'register':
        if ($request_method === 'POST') {
            handleRegister($conn);
        }
        break;
    
    case 'login':
        if ($request_method === 'POST') {
            handleLogin($conn);
        }
        break;

    case 'get_users':
        if ($request_method === 'GET') {
            getUsers($conn);
        }
        break;

    case 'update_user_status':
        if ($request_method === 'POST') {
            updateUserStatus($conn);
        }
        break;

    case 'get_user_profile':
        if ($request_method === 'GET') {
            getUserProfile($conn);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Action not found']);
        break;
}

function handleRegister($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['phone']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $name = htmlspecialchars($data['name']);
    $email = htmlspecialchars($data['email']);
    $phone = htmlspecialchars($data['phone']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database prepare error: ' . $conn->error]);
        return;
    }
    
    if (!$stmt->bind_param("ssss", $name, $email, $phone, $password)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Bind parameter error: ' . $stmt->error]);
        return;
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully', 'user_id' => $conn->insert_id]);
    } else {
        if ($conn->errno === 1062) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $conn->error]);
        }
    }
    
    $stmt->close();
}

function handleLogin($conn) {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (
        !$data ||
        !isset($data['email']) ||
        !isset($data['password']) ||
        !isset($data['userType'])
    ) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
        return;
    }

    $identifier = $data['email'];
    $password   = $data['password'];
    $userType   = $data['userType'];

    /* ================= USER LOGIN ================= */
    if ($userType === 'user') {

        $sql = "
            SELECT user_id, name, email, password, is_active
            FROM users
            WHERE email = ? OR name = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $identifier, $identifier);
    }

    /* ================= ADMIN LOGIN ================= */
    else if ($userType === 'admin') {

        $sql = "
            SELECT admin_id, name, email, password
            FROM admins
            WHERE email = ? OR name = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $identifier, $identifier);
    }

    else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid user type']);
        return;
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        return;
    }

    $user = $result->fetch_assoc();

    if ($userType === 'user' && !$user['is_active']) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Account disabled']);
        return;
    }

    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        return;
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => [
            'id' => $userType === 'admin' ? $user['admin_id'] : $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'userType' => $userType
        ]
    ]);
}
function getUsers($conn) {
    $sql = "SELECT user_id, name, email, phone, is_active FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $users]);
}

function updateUserStatus($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['user_id']) || !isset($data['is_active'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $user_id = intval($data['user_id']);
    $is_active = intval($data['is_active']);

    $sql = "UPDATE users SET is_active = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $is_active, $user_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'User status updated']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
    }
}

function getUserProfile($conn) {
    if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'User ID required']);
        return;
    }

    $user_id = intval($_GET['user_id']);
    $sql = "SELECT user_id, name, email, phone FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $result->fetch_assoc()]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
}

$conn->close();
?>
