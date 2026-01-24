<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/day_night.css?v=<?= time() ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/character/earth_mascot.png" alt="Trái Đất" class="intro-avatar-img" onerror="this.src='<?= $base_url ?>/public/images/number/count_master.png'">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn! Mình là Trái Đất!</h3>
            <p>Chào mừng bạn đến với bài học <strong>"NGÀY VÀ ĐÊM"</strong>. Hãy cùng tìm hiểu tại sao có ngày và đêm, cũng như những kiến thức thú vị về sự tự quay của Trái Đất. Bạn sẵn sàng khám phá chưa?</p>
            <button id="startLessonButton" class="start-btn">Bắt đầu học thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper day-night-lesson"><br><br><br>
    <div class="game-header-bar">
        <div class="header-left">
            <a href="<?= $base_url ?>/views/lessons/science.php" class="control-btn back">
                <i class="fas fa-home"></i> Menu
            </a>
            <button id="resetButton" class="control-btn reset" onclick="location.reload()">
                <i class="fas fa-redo"></i> Làm lại
            </button>
        </div>
        <div class="header-center">
            <h1>BÀI HỌC: NGÀY VÀ ĐÊM</h1>
            <p class="game-subtitle">Khám phá bí mật về sự tự quay của Trái Đất</p>
        </div>
        <div class="header-right">
            <div class="progress-display">
                <span class="progress-label">Tiến độ</span>
                <span id="headerProgressCounter" class="progress-count">0/5</span>
            </div>
        </div>
    </div>

    <div id="theory-section" style="display: none;">
        <div class="game-instructions">
            <div class="instruction-box">
                <i class="fas fa-book-open"></i>
                <span><strong>Nội dung bài học:</strong> Tìm hiểu nguyên nhân tạo ra ngày và đêm, thời gian quay của Trái Đất và các hiện tượng liên quan.</span>
            </div>
        </div>

        <div class="theory-content">
            <div class="theory-card">
                <div class="theory-icon">☀️</div>
                <div class="theory-body">
                    <h4>Nguyên nhân ngày và đêm</h4>
                    <p>Trái Đất tự quay quanh trục của nó, khiến một nửa được Mặt Trời chiếu sáng (ban ngày) và nửa kia không được chiếu sáng (ban đêm).</p>
                </div>
            </div>
            <div class="theory-card">
                <div class="theory-icon">🔄</div>
                <div class="theory-body">
                    <h4>Thời gian quay</h4>
                    <p>Trái Đất mất khoảng 24 giờ để hoàn thành một vòng quay quanh trục của nó, tạo ra chu kỳ ngày và đêm.</p>
                </div>
            </div>
            <div class="theory-card">
                <div class="theory-icon">🌎</div>
                <div class="theory-body">
                    <h4>Hiện tượng liên quan</h4>
                    <p>Do Trái Đất hình cầu và nghiêng trên trục, nên thời gian ngày và đêm thay đổi theo mùa và vĩ độ.</p>
                </div>
            </div>
        </div>

        <div class="action-area" style="text-align: center; margin-top: 40px;">
            <button class="start-quiz-btn" onclick="startQuiz()">
                <i class="fas fa-pencil-alt"></i> Đã hiểu, làm bài tập ngay!
            </button>
        </div>
    </div>

    <div id="quiz-section" style="display: none;">

        <div class="game-instructions">
            <div class="instruction-box">
                <i class="fas fa-lightbulb"></i>
                <span><strong>Hướng dẫn:</strong> Đọc kỹ câu hỏi và chọn đáp án đúng nhất. Sau mỗi câu trả lời, bạn sẽ thấy giải thích chi tiết.</span>
            </div>
        </div>

        <div class="quiz-card full-width-card">
            <div class="quiz-header">
                <h2>Bài tập củng cố</h2>
                <div class="progress-container">
                    <div class="progress-bar" id="progressBarBox">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>
            </div>
            
            <div class="quiz-content" id="quizContent"></div>
            
            <div class="result-feedback" id="userFeedback"></div>
            
            <div class="final-result" id="finalResult">
                <h3>Chúc mừng bạn đã hoàn thành!</h3>
                <div class="final-score-container">
                    <div class="score-circle">
                        <span class="score-value" id="finalScoreText">0</span>
                        <span class="score-label">điểm</span>
                    </div>
                    <div class="score-details">
                        <p>Bạn đã hoàn thành <strong>Bài học: Ngày và Đêm</strong></p>
                        <p id="finalMessage" class="final-message"></p>
                    </div>
                </div>
                <div class="result-actions">
                    <button class="restart-btn" onclick="location.reload()">
                        <i class="fas fa-redo"></i> Học lại từ đầu
                    </button>
                    <a href="<?= $base_url ?>/views/lessons/science.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>

        <div class="game-hints">
            <div class="hint-box">
                <i class="fas fa-star"></i>
                <div class="hint-content">
                    <h4>Ghi nhớ kiến thức:</h4>
                    <ul>
                        <li>Trái Đất tự quay quanh trục tạo ra ngày và đêm</li>
                        <li>Một vòng quay hoàn chỉnh mất khoảng 24 giờ</li>
                        <li>Thời gian ngày đêm thay đổi theo mùa và vĩ độ</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const quizData = <?php echo json_encode($questions); ?>;
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";

    // Hàm chuyển đổi từ Modal sang Lý thuyết
    document.addEventListener('DOMContentLoaded', function() {
        const startLessonBtn = document.getElementById('startLessonButton');
        if (startLessonBtn) {
            startLessonBtn.addEventListener('click', function() {
                document.getElementById('intro-modal').classList.remove('active');
                document.getElementById('theory-section').style.display = 'block';
            });
        }
    });

    // Hàm chuyển đổi từ Lý thuyết sang Bài tập
    function startQuiz() {
        document.getElementById('theory-section').style.display = 'none';
        document.getElementById('quiz-section').style.display = 'block';
    }
</script>

<script src="<?= $base_url ?>/public/JS/day_night.js?v=<?= time() ?>"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>