<?php
session_start();
require_once 'database.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $id = $data['id'] ?? 0;
    $full_name = $data['full_name'] ?? '';
    $phone_number = $data['phone_number'] ?? '';
    $cccd = $data['cccd'] ?? '';
    $license_number = $data['license_number'] ?? '';
    $vehicle_type = $data['vehicle_type'] ?? '';
    $note = $data['note'] ?? '';
    $status = isset($data['status']) ? intval($data['status']) : 1;

    if (
        empty($id) ||
        empty($full_name) ||
        empty($phone_number) ||
        empty($cccd) ||
        empty($license_number) ||
        empty($vehicle_type)
    ) {
        echo json_encode(["success" => false, "message" => "Vui lòng điền đầy đủ thông tin!"]);
        exit();
    }

    if (!preg_match('/^0\d{9}$/', $phone_number)) {
        echo json_encode(["success" => false, "message" => "Số điện thoại không hợp lệ!"]);
        exit();
    }

    if (!preg_match('/^\d{12}$/', $cccd)) {
        echo json_encode(["success" => false, "message" => "Số CCCD không hợp lệ!"]);
        exit();
    }

    if (strlen($full_name) > 100) {
        echo json_encode(["success" => false, "message" => "Tên tài xế quá dài!"]);
        exit();
    }

    $sql = "UPDATE drivers SET full_name=?, phone_number=?, cccd=?, license_number=?, vehicle_type=?, note=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssii", $full_name, $phone_number, $cccd, $license_number, $vehicle_type, $note, $status, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Cập nhật tài xế thành công!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Đã có lỗi xảy ra khi cập nhật."]);
    }

    $stmt->close();
    $conn->close();
}