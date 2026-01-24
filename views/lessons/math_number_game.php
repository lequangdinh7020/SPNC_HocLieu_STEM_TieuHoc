<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/number_game.css?v=<?= time() ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/number/count_master.png" alt="Bậc thầy đếm số" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn, mình là Count Master!</h3>
            <p>Chào mừng bạn đến với trò chơi "ĐẾM SỐ THÔNG MINH". Nhiệm vụ của bạn là đếm nhanh và chính xác số lượng của từng loại số trong thời gian ngắn nhất. Bạn sẵn sàng chưa?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper count-game"><br><br><br><br>
    <div class="top-controls">
        <div class="left-controls">
            <button id="giveUpButton" class="control-btn give-up">
                <i class="fas fa-home"></i> Menu
            </button>
            <button id="pauseButton" class="control-btn pause">
                <i class="fas fa-pause"></i> Tạm dừng
            </button>
        </div>

        <div class="timer-display">
            <div class="timer-small">
                <i class="fas fa-clock"></i>
                <span id="timer" class="timer-text">05:00</span>
            </div>
        </div>
    </div>

    <div class="game-header">
        <h1>TRÒ CHƠI ĐẾM SỐ</h1>
        <p class="game-subtitle">Thử thách trí tuệ - Rèn luyện tư duy nhanh nhạy</p>
    </div>


    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> Đếm số lượng của từng số từ 1-10 trong lưới bên dưới và nhập vào ô tương ứng</span>
        </div>
    </div>

    <div class="game-container">
        <!-- Phần Lưới số thử thách - KHÔNG có thanh cuộn, chiều cao tự nhiên -->
        <div class="number-section">        
            <div class="number-section-container">
                <h3 class="number-section-title">LƯỚI SỐ THỬ THÁCH</h3>
                <div class="number-grid" id="numberGrid"></div>
            </div>
        </div>

        <!-- Phần Kết quả đếm số - CÓ thanh cuộn tinh tế -->
        <div class="answer-section">
            <div class="answer-section-container">
                <div class="answer-header">
                    <h3>KẾT QUẢ ĐẾM SỐ</h3>
                    <p>Nhập số lượng tương ứng cho mỗi số</p>
                </div>

                <div class="answer-grid" id="answerGrid"></div>

                <div class="result-stats">
                    <div class="result-stat">
                        <span class="stat-label">ĐÚNG</span>
                        <span id="correctCount" class="stat-value">0</span>
                    </div>
                    <div class="result-stat">
                        <span class="stat-label">SAI</span>
                        <span id="wrongCount" class="stat-value">0</span>
                    </div>
                </div>

                <div class="answer-controls">
                    <button id="checkAnswersButton" class="check-btn">
                        <i class="fas fa-check"></i> Kiểm tra
                    </button>
                    <button id="clearAnswersButton" class="clear-btn">
                        <i class="fas fa-eraser"></i> Xóa hết
                    </button>
                    <button id="resetButton" class="reset-btn">
                        <i class="fas fa-redo"></i> Chơi lại
                    </button>
                </div>

                <div class="result-feedback" id="resultFeedback"></div>
            </div>
        </div>
    </div>


    <div class="game-hints">
        <div class="hint-box">
            <i class="fas fa-trophy"></i>
            <div class="hint-content">
                <h4>Mẹo để đạt điểm cao:</h4>
                <ul>
                    <li>Quan sát nhanh và tìm các nhóm số giống nhau</li>
                    <li>Sử dụng kỹ thuật đếm theo cột hoặc hàng</li>
                    <li>Kiểm tra kết quả trước khi nộp bài</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";
    window.numberData = [
        [3, 7, 1, 10, 5, 8, 2, 6, 4, 9, 3, 7, 1, 10, 5],
        [8, 2, 6, 4, 9, 3, 7, 1, 10, 5, 8, 2, 6, 4, 9],
        [10, 5, 9, 3, 7, 1, 4, 8, 2, 6, 10, 5, 9, 3, 7],
        [6, 4, 8, 2, 1, 10, 5, 9, 3, 7, 6, 4, 8, 2, 1],
        [7, 1, 3, 9, 6, 4, 8, 2, 5, 10, 7, 1, 3, 9, 6],
        [2, 8, 5, 10, 4, 9, 3, 7, 1, 6, 2, 8, 5, 10, 4],
        [9, 3, 7, 1, 2, 6, 10, 5, 8, 4, 9, 3, 7, 1, 2],
        [4, 6, 2, 8, 3, 7, 1, 10, 9, 5, 4, 6, 2, 8, 3]
    ];
</script>

<script src="<?= $base_url ?>/public/JS/number_game.js?v=<?= time() ?>" defer></script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>