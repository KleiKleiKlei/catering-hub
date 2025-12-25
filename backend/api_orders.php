<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$request_method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'create_order':
        if ($request_method === 'POST') {
            createOrder($conn);
        }
        break;

    case 'get_user_orders':
        if ($request_method === 'GET') {
            getUserOrders($conn);
        }
        break;

    case 'get_order_details':
        if ($request_method === 'GET') {
            getOrderDetails($conn);
        }
        break;

    case 'update_order_status':
        if ($request_method === 'POST') {
            updateOrderStatus($conn);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Action not found']);
        break;
}

function createOrder($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['user_id']) || !isset($data['items']) || !isset($data['order_date'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $user_id = intval($data['user_id']);
    $order_date = $data['order_date'];
    $total_amount = 0;

    // Calculate total amount
    foreach ($data['items'] as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    $conn->begin_transaction();

    try {
        $sql = "INSERT INTO orders (user_id, order_date, total_amount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isd", $user_id, $order_date, $total_amount);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Add items to order
        $item_sql = "INSERT INTO order_items (order_id, food_id, quantity, price) VALUES (?, ?, ?, ?)";
        foreach ($data['items'] as $item) {
            $food_id = intval($item['food_id']);
            $quantity = intval($item['quantity']);
            $price = floatval($item['price']);

            $item_stmt = $conn->prepare($item_sql);
            $item_stmt->bind_param("iiii", $order_id, $food_id, $quantity, $price);
            $item_stmt->execute();
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Order created', 'order_id' => $order_id]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Order creation failed: ' . $e->getMessage()]);
    }
}

function getUserOrders($conn) {
    if (!isset($_GET['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User ID required']);
        return;
    }

    $user_id = intval($_GET['user_id']);
    $sql = "SELECT order_id, order_date, total_amount, status FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $orders]);
}

function getOrderDetails($conn) {
    if (!isset($_GET['order_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Order ID required']);
        return;
    }

    $order_id = intval($_GET['order_id']);
    $sql = "SELECT oi.item_id, oi.food_id, fm.food_name, oi.quantity, oi.price FROM order_items oi JOIN food_menu fm ON oi.food_id = fm.food_id WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $items]);
}

function updateOrderStatus($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['order_id']) || !isset($data['status'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }

    $order_id = intval($data['order_id']);
    $status = htmlspecialchars($data['status']);

    $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Order status updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
    }
}

$conn->close();
?>
