    <footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo-wrapper">
                    <div class="logo-icon" style="width: 65px; height: 65px;"><img src="<?= $base_url ?>/public/images/logo.png" alt="STEM Universe Logo" style="width: 100%; height: 100%; object-fit: contain;"></div>
                    <div class="footer-text-content">
                        <h4 class="footer-title">STEM Universe</h4>
                        <p>Khám phá thế giới STEM đầy sáng tạo. Nền tảng học liệu tương tác cho học sinh tiểu học Việt Nam.</p>
                    </div>
                </div>
            </div>
            <div class="footer-section">
                <h4>Khám phá</h4>
                <a href="#">Tất cả bài học</a>
                <a href="#">Thử thách STEM</a>
                <a href="#">Tài nguyên giáo viên</a>
            </div>
            <div class="footer-section">
                <h4>Kết nối</h4>
                <div class="social-links">
                    <a href="https://www.facebook.com/" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/" class="social-link" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://www.linkedin.com/" class="social-link" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 STEM Universe. Được phát triển với ❤️ dành cho giáo dục STEM Việt Nam.</p>
        </div>
    </div>
</footer>

    <?php if (!isset($base_url)) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $project_path = '/SPNC_HocLieu_STEM_TieuHoc';
        $base_url = $protocol . '://' . $host . $project_path;
    }
    ?>
    <script src="<?= $base_url ?>/public/JS/home.js"></script>
</body>
</html>
