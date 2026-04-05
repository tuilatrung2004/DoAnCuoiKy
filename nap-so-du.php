<?php
include_once 'Header.php';
include_once 'config/config.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-body shadow p-4">
                    <div class="text-center mb-4">
                        <a href="history.php" class="btn btn-light rounded-pill px-4 py-2 shadow bg-warning">
                            <i class="fas fa-hand-point-right me-2"></i>
                            Xem tình trạng nạp thẻ
                            <i class="fas fa-hand-point-left ms-2"></i>
                        </a>
                    </div>
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0"><i class="bx bx-wallet me-2"></i>Nạp tiền bằng thẻ cào</h4>
                    </div>
                    
                    <?php if (isset($_SESSION['username']) === null) { ?>
                        <div class="alert alert-warning">
                            Bạn chưa đăng nhập? Hãy đăng nhập để sử dụng chức năng này
                        </div>
                    <?php } else { ?>
                        <script type="text/javascript">
                            new WOW().init();
                        </script>
                        <form method="POST" action="" id="myform">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tài Khoản:</label>
                                <input type="text" class="form-control bg-light fw-semibold" 
                                       name="username" value="<?php echo $_SESSION['username'] ?>" readonly required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Loại thẻ:</label>
                                <select class="form-select" name="card_type" required>
                                    <option value="">Chọn loại thẻ</option>
                                    <?php
                                    $cdurl = curl_init("https://thesieutoc.net/card_info.php");
                                    curl_setopt($cdurl, CURLOPT_FAILONERROR, true);
                                    curl_setopt($cdurl, CURLOPT_FOLLOWLOCATION, true);
                                    curl_setopt($cdurl, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($cdurl, CURLOPT_CAINFO, __DIR__ . '/api/curl-ca-bundle.crt');
                                    curl_setopt($cdurl, CURLOPT_CAPATH, __DIR__ . '/api/curl-ca-bundle.crt');
                                    $obj = json_decode(curl_exec($cdurl), true);
                                    curl_close($cdurl);
                                    $length = count($obj);
                                    for ($i = 0; $i < $length; $i++) {
                                        if ($obj[$i]['status'] == 1) {
                                            echo '<option value="' . $obj[$i]['name'] . '">' . $obj[$i]['name'] . ' (' . $obj[$i]['chietkhau'] . '%)</option> ';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mệnh giá:</label>
                                <select class="form-select" name="card_amount" required>
                                    <option value="">Chọn mệnh giá</option>
                                    <option value="10000">10.000</option>
                                    <option value="20000">20.000</option>
                                    <option value="30000">30.000</option>
                                    <option value="50000">50.000</option>
                                    <option value="100000">100.000</option>
                                    <option value="200000">200.000</option>
                                    <option value="300000">300.000</option>
                                    <option value="500000">500.000</option>
                                    <option value="1000000">1.000.000</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Số seri:</label>
                                <input type="text" class="form-control" name="serial" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Mã thẻ:</label>
                                <input type="text" class="form-control" name="pin" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 text-uppercase fw-bold py-3" name="submit">
                                Nạp Ngay
                            </button>
                        </form>

                        <script type="text/javascript">
                            $(document).ready(function () {
                                var lastSubmitTime = 0;
                                $("#myform").submit(function (e) {
                                    var now = new Date().getTime();
                                    if (now - lastSubmitTime < 5000) {
                                        Swal.fire({
                                            title: 'Thông báo',
                                            text: 'Vui lòng đợi ít nhất 5 giây trước khi nạp tiếp',
                                            icon: 'error'
                                        });
                                        return false;
                                    }
                                    lastSubmitTime = now;

                                    $("#status").html("");
                                    e.preventDefault();
                                    $.ajax({
                                        url: "./ajax/card.php",
                                        type: 'post',
                                        data: $("#myform").serialize(),
                                        success: function (data) {
                                            $("#status").html(data);
                                            document.getElementById("myform").reset();
                                            $("#load_hs").load("./history.php");
                                        }
                                    });
                                });
                            });
                        </script>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="status"></div>

<?php
include_once 'Footer.php';
?>