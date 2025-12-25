<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$request_method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'register':
        if ($request_method === 'POST') {
            handleAdminRegister($conn);
        }
        break;

    case 'login':
        if ($request_method === 'POST') {
            handleAdminLogin($conn);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Action not found']);
        break;
}

function handleAdminRegister($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $name = htmlspecialchars($data['name']);
    $email = htmlspecialchars($data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO admin (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Admin registered successfully', 'admin_id' => $conn->insert_id]);
    } else {
        if ($conn->errno === 1062) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $conn->error]);
        }
    }
}

function handleAdminLogin($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $email = htmlspecialchars($data['email']);

    $sql = "SELECT admin_id, name, email, password FROM admin WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if (password_verify($data['password'], $admin['password'])) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'admin' => [
                    'id' => $admin['admin_id'],
                    'name' => $admin['name'],
                    'email' => $admin['email']
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Admin not found']);
    }
}

$conn->close();
?>
