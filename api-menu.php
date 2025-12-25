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
    case 'add_food':
        if ($request_method === 'POST') {
            addFood($conn);
        }
        break;

    case 'get_weekly_menu':
        if ($request_method === 'GET') {
            getWeeklyMenu($conn);
        }
        break;

    case 'get_daily_menu':
        if ($request_method === 'GET') {
            getDailyMenu($conn);
        }
        break;

    case 'update_food':
        if ($request_method === 'POST') {
            updateFood($conn);
        }
        break;

    case 'delete_food':
        if ($request_method === 'POST') {
            deleteFood($conn);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Action not found']);
        break;
}

function addFood($conn) {
    $food_name = isset($_POST['food_name']) ? htmlspecialchars($_POST['food_name']) : '';
    $food_description = isset($_POST['food_description']) ? htmlspecialchars($_POST['food_description']) : '';
    $menu_date = isset($_POST['menu_date']) ? $_POST['menu_date'] : '';

    if (!$food_name || !$menu_date) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $food_image = '';
    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === 0) {
        $file_tmp = $_FILES['food_image']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['food_image']['name']);
        $upload_dir = __DIR__ . '/uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $food_image = 'uploads/' . $file_name;
        }
    }

    $sql = "INSERT INTO food_menu (food_name, food_description, food_image, menu_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $food_name, $food_description, $food_image, $menu_date);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Food item added', 'food_id' => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to add food: ' . $conn->error]);
    }
}

function getWeeklyMenu($conn) {
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('+6 days'));

    $sql = "SELECT food_id, food_name, food_description, food_image, menu_date FROM food_menu WHERE menu_date BETWEEN ? AND ? ORDER BY menu_date";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $foods = [];
    while ($row = $result->fetch_assoc()) {
        $foods[] = $row;
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $foods]);
}

function getDailyMenu($conn) {
    if (!isset($_GET['date'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Date required']);
        return;
    }

    $date = $_GET['date'];
    $sql = "SELECT food_id, food_name, food_description, food_image, menu_date FROM food_menu WHERE menu_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $foods = [];
    while ($row = $result->fetch_assoc()) {
        $foods[] = $row;
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $foods]);
}

function updateFood($conn) {
    $food_id = isset($_POST['food_id']) ? intval($_POST['food_id']) : 0;
    $food_name = isset($_POST['food_name']) ? htmlspecialchars($_POST['food_name']) : '';
    $food_description = isset($_POST['food_description']) ? htmlspecialchars($_POST['food_description']) : '';

    if (!$food_id || !$food_name) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $sql = "UPDATE food_menu SET food_name = ?, food_description = ? WHERE food_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $food_name, $food_description, $food_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Food item updated']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
    }
}

function deleteFood($conn) {
    $food_id = isset($_POST['food_id']) ? intval($_POST['food_id']) : 0;

    if (!$food_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Food ID required']);
        return;
    }

    // Get image path to delete
    $sql = "SELECT food_image FROM food_menu WHERE food_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $food_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['food_image']) {
            $image_path = __DIR__ . '/' . $row['food_image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }

    $sql = "DELETE FROM food_menu WHERE food_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $food_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Food item deleted']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
    }
}

$conn->close();
?>
