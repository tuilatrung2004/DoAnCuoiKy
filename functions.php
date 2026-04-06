<?php
require_once __DIR__ . '/../config/config.php';
?>
<?php
function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    global $conn;
    $user_id = (int)$_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT is_admin FROM account WHERE id = ? AND is_admin = 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

function checkAdmin() {
    if (!isAdmin()) {
        redirect('index.php');
        exit();
    }
}

function redirect($path) {
    if (headers_sent()) {
        echo "<script>window.location.href='$path';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=$path'></noscript>";
        exit();
    } else {
        header("Location: $path");
        exit();
    }
}

function getOrderStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getOrderStatusText($status) {
    switch ($status) {
        case 'pending':
            return 'Chờ xử lý';
        case 'processing':
            return 'Đang xử lý';
        case 'shipped':
            return 'Đang giao';
        case 'delivered':
            return 'Đã giao';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return 'Không xác định';
    }
} 

function escape($conn, $str) {
    return mysqli_real_escape_string($conn, $str);
}

function format_currency($amount) {
    $amount = floatval($amount);
    return number_format($amount, 0, ',', '.') . ' đ';
}

function create_slug($string) {
    $search = array(
        '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
        '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
        '#(ì|í|ị|ỉ|ĩ)#',
        '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
        '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
        '#(ỳ|ý|ỵ|ỷ|ỹ)#',
        '#(đ)#',
        '#[^a-z0-9\-\_]#'
    );
    $replace = array(
        'a',
        'e',
        'i',
        'o',
        'u',
        'y',
        'd',
        '-'
    );
    $string = strtolower($string);
    $string = preg_replace($search, $replace, $string);
    $string = preg_replace('/(-)+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

function uploadFile($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    if ($file['error'] == 0) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($file_extension, $allowed_types)) {
            $file_name = time() . '_' . basename($file['name']);
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                return $file_name;
            }
        }
    }
    return false;
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /pages/login.php');
        exit();
    }
}

function getCurrentUser($conn) {
    if (isset($_SESSION['user_id'])) {
        $user_id = (int)$_SESSION['user_id'];
        $query = "SELECT * FROM account WHERE id = $user_id";
        return $conn->query($query)->fetch_assoc();
    }
    return null;
}
function generateRandomPassword($length = 6)
{
    $characters = '0123456789';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $password;
}

?> 