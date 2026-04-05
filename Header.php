<?php
require_once 'config/config.php';   
require_once './functions/functions.php';
// Lấy danh sách danh mục cho menu
$menu_query = "SELECT * FROM product_categories WHERE parent_id IS NULL ORDER BY ten_theloai";
$menu_categories = $conn->query($menu_query);

// Lấy danh sách thương hiệu
$brand_query = "SELECT * FROM brands ORDER BY name";
$brands = $conn->query($brand_query);

// Kiểm tra giỏ hàng
$cart_count = 0;
$cart_total = 0;
if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
    foreach($_SESSION['cart'] as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng Nước Hoa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/assets/css/swiper-custom.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://cdn.rawgit.com/daneden/animate.css/v3.1.0/animate.min.css">
    <script src='https://cdn.rawgit.com/matthieua/WOW/1.0.1/dist/wow.min.js'></script>
    <link rel="stylesheet" href="https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous">
        </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"
        integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous">
        </script>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/main.js"></script>
</head>
<body>
    <!-- Top Header -->
    <div class="navbar-wrapper container-wrapper" style="background-color: #333;">
        <div class="container navbar py-2">
            <p class="navbar-left text-white mb-0">
                Bạn là Học Sinh/Sinh Viên? <span class="text-danger">GIẢM NGAY 20%</span>!
                <a href="index.php?action=cuahang" class="text-white text-decoration-underline">Xem thêm</a>
            </p>
            <ul class="navbar__links d-flex align-items-center list-unstyled mb-0">
                <li>
                    <a href="#" class="text-white" data-bs-toggle="modal" data-bs-target="#storeLocations">
                        <i class='bx bx-map'></i> Địa Chỉ Cửa Hàng
                    </a>
                </li>
                <li class="ms-4">
                    <a href="#" class="text-white">
                        <i class='bx bx-help-circle'></i> Trợ giúp
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Header -->
    <div class="container-wrapper header-search-wrapper py-3">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-md-2">
                    <a href="index.php" class="header-search__logo-section">
                    <img src="/images/logo.png" alt="Logo" class="img-fluid">
                    </a>
                </div>

                <!-- Search Bar -->
                <!-- <div class="col-md-7">
                    <form action="index.php" method="GET" class="d-flex">
                        <input type="hidden" name="action" value="search">
                        <div class="input-group">
                            <select name="category" class="form-select" style="max-width: 200px;">
                                <option value="">Tất cả danh mục</option>
                                <?php while($cat = $menu_categories->fetch_assoc()): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['ten_theloai'] ?></option>
                                <?php endwhile; ?>
                            </select>
                            <input type="text" name="keyword" class="form-control" 
                                   placeholder="Tìm kiếm sản phẩm...">
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-search'></i> Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div> -->
                <div class="col-md-7 text-center text-danger">
                </div>

                <!-- User Actions -->
                <div class="col-md-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <!-- Account -->
                        <div class="dropdown me-3">
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <button type="button" class="btn-user-dropdown dropdown-toggle d-flex align-items-center" 
                                        data-bs-toggle="dropdown">
                                    <i class='bx bx-user fs-4'></i>
                                    <span class="ms-2"><?= $_SESSION['fullname'] ?></span>   
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <div class="dropdown-item">
                                            <i class='bx bx-money text-success'></i>
                                            <span>Số dư: <?php echo format_currency(isset($_SESSION["money"]) ? $_SESSION["money"] : 0) ?></span>
                                        </div>
                                    </li>
                                    <li><a class="dropdown-item" href="profile.php">Tài khoản của tôi</a></li>
                                    <li><a class="dropdown-item" href="cart.php">Giỏ hàng</a></li>
                                    <li><a class="dropdown-item" href="purchase-history.php">Lịch sử mua hàng</a></li>
                                    <li class="dropdown-submenu">
                                        <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                            Phương thức nạp tiền
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="nap-so-du.php">Nạp bằng thẻ cào</a></li>
                                            <li><a class="dropdown-item" href="nap-momo.php">Nạp bằng MoMo</a></li>
                                            <li><a class="dropdown-item" href="nap-bank.php">Nạp bằng Bank</a></li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="change_password.php">Đổi mật khẩu</a></li>
                                    <?php if(isAdmin()): ?>
                                        <li><a class="dropdown-item" href="admin/">Quản trị</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                                </ul>
                            <?php else: ?>
                                <div class="d-flex flex-column">
                                    <a href="login.php" class="text-decoration-none"><i class='bx bx-user-circle fs-4'></i></a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Cart -->
                        <a href="cart.php" class="position-relative">
                            <i class='bx bx-cart fs-4'></i>
                            <?php if($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $cart_count ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            Danh mục sản phẩm
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            $menu_categories->data_seek(0);
                            while($cat = $menu_categories->fetch_assoc()): 
                            ?>
                                <li>
                                    <a class="dropdown-item" href="products.php?category=<?= $cat['id'] ?>">
                                        <?= $cat['ten_theloai'] ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            Thương hiệu
                        </a>
                        <ul class="dropdown-menu">
                            <?php while($brand = $brands->fetch_assoc()): ?>
                                <li>
                                    <a class="dropdown-item" href="products.php?brand=<?= $brand['id'] ?>">
                                        <?= $brand['name'] ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Liên hệ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">Giới thiệu</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Store Locations Modal -->
    <div class="modal fade" id="storeLocations" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Địa chỉ cửa hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Trường Đại học Công nghệ TP.HCM (HUTECH)</h6>
                            <p>Khu Công nghệ cao Xa lộ Hà Nội, Hiệp Phú, Quận 9, Hồ Chí Minh, Việt Nam</p>
                            <p>Điện thoại: +84</p>
                        </div>
                        <div class="col-md-6">
                                <div class="map-container">
                                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15673.674140397383!2d106.77427751628984!3d10.85573711936834!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175276e7ea103df%3A0xb6cf10bb7d719327!2zSFVURUNIIC0gxJDhuqFpIGjhu41jIEPDtG5nIG5naOG7hyBUUC5IQ00gKFRodSBEdWMgQ2FtcHVzKQ!5e0!3m2!1svi!2s!4v1740066139266!5m2!1svi!2s"
                                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Bubble -->
    <div class="chat-bubble">
        <div class="chat-icon" id="chatIcon">
            <i class='bx bx-message-dots'></i>
        </div>
        
        <div class="chat-box" id="chatBox">
            <div class="chat-header">
                <h5>AI Hỗ trợ</h5>
                <button class="close-chat" id="closeChat">
                    <i class='bx bx-x'></i>
                </button>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="message ai">
                    <div class="message-content">
                        Xin chào! Tôi là trợ lý AI. Tôi có thể giúp gì cho bạn?
                    </div>
                </div>
            </div>
            
            <div class="chat-input">
                <input type="text" id="userInput" placeholder="Nhập tin nhắn...">
                <button id="sendMessage">
                    <i class='bx bx-send'></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Chat Script -->
    <script>
        const chatIcon = document.getElementById('chatIcon');
        const chatBox = document.getElementById('chatBox');
        const closeChat = document.getElementById('closeChat');
        const userInput = document.getElementById('userInput');
        const sendMessage = document.getElementById('sendMessage');
        const chatMessages = document.getElementById('chatMessages');

        // Check if elements exist to prevent errors
        if (!chatIcon || !chatBox || !closeChat || !userInput || !sendMessage || !chatMessages) {
            console.error('Chat elements not found. Please check HTML elements.');
        }

        // Toggle chat box
        chatIcon.addEventListener('click', () => {
            chatBox.classList.toggle('show');
        });

        closeChat.addEventListener('click', () => {
            chatBox.classList.remove('show');
        });

        // Send message
        function addMessage(message, isUser = true) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;
            
            // For AI messages, we want to preserve HTML content if it exists
            messageDiv.innerHTML = `
                <div class="message-content">
                ${message}
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Thêm hàm showLoading
        function showLoading() {
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'message loading ai';
            loadingDiv.innerHTML = `
                <div class="message-content">
                    Đang xử lý
                    <div class="loading-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            `;
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return loadingDiv;
        }

        async function sendToAI(message) {
            const loadingMessage = showLoading();
            
            try {
                // Gửi request tới API
                const apiUrl = '/api/chat.php';
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message })
                });
                
                const data = await response.json();
                
                // Xóa tin nhắn loading
                loadingMessage.remove();
                
                if (data.success) {
                    // Hiển thị phản hồi từ AI - giữ nguyên HTML nếu có
                    addMessage(data.response, false);
                } else {
                    // Hiển thị lỗi
                    addMessage(`Xin lỗi, tôi không thể xử lý yêu cầu của bạn: ${data.error}`, false);
                }
            } catch (error) {
                console.error('Error calling AI API:', error);
                loadingMessage.remove();
                addMessage('Xin lỗi, có lỗi xảy ra khi kết nối với AI! Vui lòng thử lại sau.', false);
            }
        }

        sendMessage.addEventListener('click', () => {
            const message = userInput.value.trim();
            if (message) {
                addMessage(message);
                userInput.value = '';
                sendToAI(message);
            }
        });

        userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage.click();
            }
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Khởi tạo dropdown menu
        var dropdowns = document.querySelectorAll('.dropdown-toggle');
        dropdowns.forEach(function(dropdown) {
            dropdown.addEventListener('click', function(event) {
                event.preventDefault();
                var dropdownMenu = this.nextElementSibling;
                dropdownMenu.classList.toggle('show');
            });

            // Đóng dropdown khi click ra ngoài
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.nextElementSibling.classList.remove('show');
                }
            });
        });

        // Handle submenu dropdowns
        document.querySelectorAll('.dropdown-submenu > a').forEach(function(element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other submenus
                let submenus = document.querySelectorAll('.dropdown-submenu .dropdown-menu');
                submenus.forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.remove('show');
                    }
                });
                
                // Toggle current submenu
                if (this.nextElementSibling) {
                    this.nextElementSibling.classList.toggle('show');
                }
            });
        });

        // Close submenus when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-submenu')) {
                document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>