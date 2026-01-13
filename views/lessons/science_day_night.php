<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/day_night.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<br><br><br><br>

<div class="lesson-container">
    <div id="theory-section" class="animate-fade-in">
        <div class="lesson-summary" style="margin-top: 0; border-left: none; padding: 40px;">
            <div class="summary-header" style="text-align: center; justify-content: center;">
                <h3 style="font-size: 2em; margin-bottom: 30px;">Lý thuyết bài học</h3>
            </div>
            
            <div class="summary-content">
                <div class="summary-point">
                    <span class="point-icon">☀️</span>
                    <div class="point-content">
                        <h4>Nguyên nhân ngày và đêm</h4>
                        <p>Trái Đất tự quay quanh trục của nó, khiến một nửa được Mặt Trời chiếu sáng (ban ngày) và nửa kia không được chiếu sáng (ban đêm).</p>
                    </div>
                </div>
                <div class="summary-point">
                    <span class="point-icon">🔄</span>
                    <div class="point-content">
                        <h4>Thời gian quay</h4>
                        <p>Trái Đất mất khoảng 24 giờ để hoàn thành một vòng quay quanh trục của nó, tạo ra chu kỳ ngày và đêm.</p>
                    </div>
                </div>
                <div class="summary-point">
                    <span class="point-icon">🌎</span>
                    <div class="point-content">
                        <h4>Hiện tượng liên quan</h4>
                        <p>Do Trái Đất hình cầu và nghiêng trên trục, nên thời gian ngày và đêm thay đổi theo mùa và vĩ độ.</p>
                    </div>
                </div>
            </div>

            <div class="action-area" style="text-align: center; margin-top: 40px;">
                <button class="start-quiz-btn" onclick="startQuiz()">
                    Đã hiểu, làm bài tập ngay!
                </button>
            </div>
        </div>
    </div>

    <div id="quiz-section" style="display: none;" class="animate-fade-in">
        <div class="quiz-card full-width-card">
            <div class="quiz-header">
                <h2>Bài tập củng cố</h2>
                <div class="progress-container">
                    <div class="progress-info">
                        <span class="progress-text">Tiến độ</span>
                        <span class="progress-counter" id="progressCounter">0/5</span>
                    </div>
                    <div class="progress-bar" id="progressBarBox">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>
            </div>
            
            <div class="quiz-content" id="quizContent"></div>
            
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
                        Học lại từ đầu
                    </button>
                    <a href="<?= $base_url ?>/views/lessons/science.php" class="back-btn">
                        <i class="btn-icon">←</i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const quizData = <?php echo json_encode($questions); ?>;

    // Hàm chuyển đổi từ Lý thuyết sang Bài tập
    function startQuiz() {
        document.getElementById('theory-section').style.display = 'none';
        document.getElementById('quiz-section').style.display = 'block';
    }
</script>

<script src="<?= $base_url ?>/public/JS/day_night.js?v=<?= time() ?>"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>