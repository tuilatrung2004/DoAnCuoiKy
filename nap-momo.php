<?php
include_once 'config/config.php';
include_once 'Header.php'; 


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if(isset($_GET['partnerCode'])){
    $code_order = rand(0,9999);
    $partnerCode = $_GET['partnerCode'];
    $orderId = $_GET['orderId'];
    $amount = $_GET['amount'];
    $orderInfo = $_GET['orderInfo'];
    $resultCode = $_GET['resultCode'];
    $orderType = $_GET['orderType'];
    $transId = $_GET['transId'];
    $payType = $_GET['payType'];

    $insert_momo = "INSERT INTO `nap_momo`(`name`, `partner_code`, `order_id`, `amount`, `order_info`,`result_code` , `order_type`, `trans_id`, `pay_type`, `code_cart`) VALUES ('$_SESSION[username]','$partnerCode','$orderId','$amount','$orderInfo','$resultCode','$orderType','$transId','$payType','$code_order')";
    $conn->query($insert_momo);
    if($resultCode == "0"){
        $update_user = "UPDATE `account` SET `money` = `money` + '$amount' WHERE `id` = '$_SESSION[user_id]'";
        $conn->query($update_user);
        echo "<script>alert('Nạp tiền thành công');</script>";
    }else{
        echo "<script>alert('Nạp tiền thất bại');</script>";
    }
}

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="text-center mb-4">
                    <a href="history-momo.php" class="btn btn-light rounded-pill px-4 py-2 shadow bg-warning">
                        <i class="fas fa-hand-point-right me-2"></i>
                         Xem tình trạng nạp tiền
                        <i class="fas fa-hand-point-left ms-2"></i>
                    </a>
                </div>
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0"><i class="bx bx-wallet me-2"></i>Nạp tiền qua MoMo</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="assets/images/momo-logo.png" alt="MoMo Logo" style="width: 100px;">
                    </div>

                    <form action="xulithanhtoan-momo.php" method="post">
                        <div class="mb-4">
                            <label class="form-label">Tài khoản</label>
                            <input type="text" class="form-control" value="<?php echo $_SESSION['username']; ?>" readonly>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Số tiền nạp</label>
                            <select name="amount" class="form-select" required>
                                <option value="">-- Chọn số tiền --</option>
                                <option value="10000">10,000đ</option>
                                <option value="20000">20,000đ</option>
                                <option value="50000">50,000đ</option>
                                <option value="100000">100,000đ</option>
                                <option value="200000">200,000đ</option>
                                <option value="500000">500,000đ</option>
                                <option value="1000000">1,000,000đ</option>
                            </select>
                        </div>

                        <div class="alert alert-info mb-4">
                            <h5 class="alert-heading"><i class="bx bx-info-circle me-2"></i>Lưu ý:</h5>
                            <ul class="mb-0 ps-3">
                                <li>Số tiền sẽ được cộng vào tài khoản sau khi giao dịch thành công</li>
                                <li>Giữ lại mã giao dịch để đối chiếu khi cần thiết</li>
                                <li>Nếu cần hỗ trợ, vui lòng liên hệ Hotline: <strong>+84 398-702-156</strong></li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bx bx-money me-2"></i>Nạp tiền ngay
                        </button>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col">
                            <small class="text-muted">
                                <i class="bx bx-shield me-1"></i>Bảo mật
                            </small>
                        </div>
                        <div class="col">
                            <small class="text-muted">
                                <i class="bx bx-check-shield me-1"></i>An toàn
                            </small>
                        </div>
                        <div class="col">
                            <small class="text-muted">
                                <i class="bx bx-time me-1"></i>Nhanh chóng
                            </small>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
</div>

<?php include_once 'Footer.php'; ?>
