<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
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
    $stmt->bind_param("ssss", $name, $email, $phone, $password);

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
}

function handleLogin($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['email']) || !isset($data['password']) || !isset($data['userType'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $email = htmlspecialchars($data['email']);
    $userType = htmlspecialchars($data['userType']);

    if ($userType === 'admin') {
        $sql = "SELECT admin_id, name, email, password FROM admin WHERE email = ?";
    } else {
        $sql = "SELECT user_id, name, email, password, is_active FROM users WHERE email = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($userType === 'user' && !$user['is_active']) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Your account has been disabled']);
            return;
        }

        if (password_verify($data['password'], $user['password'])) {
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
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
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
