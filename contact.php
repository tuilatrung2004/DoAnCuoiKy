<div id="wrapper">
    <?php
    require_once 'config/config.php';
    require_once 'Header.php';
    ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10">
                <!-- Thông tin liên hệ -->
                <div class="row mb-5">
                    <div class="col-12 col-md-4 mb-4">
                        <div class="contact-info p-4 bg-light rounded shadow-sm text-center h-100">
                            <i class='bx bx-map fs-1 text-primary mb-3'></i>
                            <h5>Địa chỉ</h5>
                            <p class="mb-0">Khu Công nghệ cao Xa lộ Hà Nội, Hiệp Phú, Quận 9, Hồ Chí Minh, Việt Nam</p>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-4 mb-4">
                        <div class="contact-info p-4 bg-light rounded shadow-sm text-center h-100">
                            <i class='bx bx-phone fs-1 text-primary mb-3'></i>
                            <h5>Điện thoại</h5>
                            <p class="mb-0">+84-398-702-156</p>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-4 mb-4">
                        <div class="contact-info p-4 bg-light rounded shadow-sm text-center h-100">
                            <i class='bx bx-envelope fs-1 text-primary mb-3'></i>
                            <h5>Email</h5>
                            <p class="mb-0">nguyentuan834987@gmail.com</p>
                        </div>
                    </div>
                </div>

                <!-- Form liên hệ -->
                <div class="card1 shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Gửi Tin Nhắn Cho Chúng Tôi</h2>
                        
                        <form id="contactForm" method="post" action="">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Số điện thoại</label>
                                        <input type="tel" class="form-control" id="phone" name="phone">
                                    </div>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <div class="form-group">
                                        <label for="message" class="form-label">Nội dung <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary px-5">Gửi tin nhắn</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bản đồ -->
                <div class="mt-5">
                    <div class="card1 shadow">
                        <div class="card-body p-0">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15673.674140397383!2d106.77427751628984!3d10.85573711936834!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175276e7ea103df%3A0xb6cf10bb7d719327!2zSFVURUNIIC0gxJDhuqFpIGjhu41jIEPDtG5nIG5naOG7hyBUUC5IQ00gKFRodSBEdWMgQ2FtcHVzKQ!5e0!3m2!1svi!2s!4v1740066139266!5m2!1svi!2s"
                                width="100%" 
                                height="450" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Xử lý form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $message = $_POST['message'] ?? '';
        
        // Kiểm tra và làm sạch dữ liệu
        $name = htmlspecialchars(strip_tags($name));
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $phone = htmlspecialchars(strip_tags($phone));
        $message = htmlspecialchars(strip_tags($message));
        
        if ($name && $email && $message) {
            // Thêm vào database
            $sql = "INSERT INTO contacts (name, email, phone, message) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("ssss", $name, $email, $phone, $message);
                
                if ($stmt->execute()) {
                    echo "<script>
                        swal('Thành công!', 'Tin nhắn của bạn đã được gửi thành công!', 'success');
                    </script>";
                } else {
                    echo "<script>
                        swal('Lỗi!', 'Có lỗi xảy ra khi gửi tin nhắn!', 'error');
                    </script>";
                }
                
                $stmt->close();
            }
        }
    }
    ?>

    <?php require_once 'Footer.php'; ?>
</div>
