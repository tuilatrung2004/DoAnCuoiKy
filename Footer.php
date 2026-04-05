<footer class="footer bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <!-- Giới thiệu -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-4">Về chúng tôi</h5>
                <p>Chuyên cung cấp các sản phẩm chính hãng, chất lượng cao</p>
                <div class="contact-info">
                    <p><i class='bx bx-phone'></i> +84-398-702-156</p>
                    <p><i class='bx bx-envelope'></i> nguyentuan834987@gmail.com</p>
                    <p><i class='bx bx-map'></i> Khu Công nghệ cao Xa lộ Hà Nội, Hiệp Phú, Quận 9, Hồ Chí Minh, Việt Nam</p>
                </div>
            </div>

            <!-- Thông tin -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-4">Thông tin</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="about.php" class="text-white">Về chúng tôi</a></li>
                    <li class="mb-2"><a href="shipping.php" class="text-white">Chính sách vận chuyển</a></li>
                    <li class="mb-2"><a href="privacy.php" class="text-white">Chính sách bảo mật</a></li>
                    <li class="mb-2"><a href="terms.php" class="text-white">Điều khoản sử dụng</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white">Liên hệ</a></li>
                </ul>
            </div>

            <!-- Tài khoản -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-4">Tài khoản</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="profile.php" class="text-white">Tài khoản của tôi</a></li>
                    <li class="mb-2"><a href="orders.php" class="text-white">Lịch sử đơn hàng</a></li>
                    <li class="mb-2"><a href="wishlist.php" class="text-white">Sản phẩm yêu thích</a></li>
                    <li class="mb-2"><a href="newsletter.php" class="text-white">Đăng ký nhận tin</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-4">Đăng ký nhận tin</h5>
                <p>Nhận thông tin về sản phẩm mới và khuyến mãi</p>
                <form action="newsletter-subscribe.php" method="POST" class="newsletter-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Email của bạn" required>
                        <button class="btn btn-primary" type="submit">Đăng ký</button>
                    </div>
                </form>

                <!-- Social Media -->
                <div class="social-media mt-4">
                    <h6 class="mb-3">Kết nối với chúng tôi</h6>
                    <a href="#" class="text-white me-3"><i class='bx bxl-facebook fs-4'></i></a>
                    <a href="#" class="text-white me-3"><i class='bx bxl-instagram fs-4'></i></a>
                    <a href="#" class="text-white me-3"><i class='bx bxl-youtube fs-4'></i></a>
                    <a href="#" class="text-white"><i class='bx bxl-tiktok fs-4'></i></a>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="row mt-4 pt-4 border-top">
            <div class="col-md-6 w-10 h-10">
                <h6 class="mb-3">Phương thức thanh toán</h6>
                <div class="payment-methods">
                    <img src="/images/brand/visa.jpg" alt="Visa" class="me-2">
                    <img src="/images/brand/mastercard.jpg" alt="Mastercard" class="me-2">
                    <img src="/images/brand/momo.jpg" alt="Momo" class="me-2">
                    <img src="/images/brand/zalopay.jpg" alt="ZaloPay" class="me-2">
                    <img src="/images/brand/cod.jpg" alt="COD">
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <h6 class="mb-3">Đối tác vận chuyển</h6>
                <div class="shipping-partners">
                    <img src="/images/brand/ghtk.jpg" alt="GHTK" class="me-2">
                    <img src="/images/brand/ghn.jpg" alt="GHN" class="me-2">
                    <img src="/images/brand/vnpost.jpg" alt="VNPost">
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="row mt-4 pt-4 border-top">
            <div class="col-md-6">
                <p class="mb-0">Vui Lòng liên hệ : 0398702156</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">Facebook : <a href="#" class="text-white">Nguyễn Đình Anh Tuan</a></p>
            </div>
        </div>
    </div>
</footer>

<!-- Back to top button -->
<div id="back-to-top" class="position-fixed bottom-0 end-0 m-4 d-none">
    <button class="btn btn-primary rounded-circle p-3" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class='bx bx-up-arrow-alt'></i>
    </button>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
window.onscroll = function() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById('back-to-top').classList.remove('d-none');
    } else {
        document.getElementById('back-to-top').classList.add('d-none');
    }
};
</script>
</body>
</html>