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

<div class="game-wrapper count-game"><br><br><br>
    <div class="game-header">
        <h1>TRÒ CHƠI ĐẾM SỐ</h1>
        <p class="game-subtitle">Thử thách trí tuệ - Rèn luyện tư duy nhanh nhạy</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box correct">
            <span class="stat-label">ĐÚNG</span>
            <span id="correctCount" class="stat-value">0</span>
        </div>
        <div class="stat-box wrong">
            <span class="stat-label">SAI</span>
            <span id="wrongCount" class="stat-value">0</span>
        </div>
        <div class="stat-box remaining">
            <span class="stat-label">CÒN LẠI</span>
            <span id="questionsRemaining" class="stat-value">197</span>
        </div>
        <div class="stat-box timer">
            <span class="stat-label">THỜI GIAN</span>
            <span id="timer" class="stat-value">05:00</span>
        </div>
        <div class="stat-box progress">
            <span class="stat-label">TIẾN ĐỘ</span>
            <span id="progressValue" class="stat-value">3/200</span>
        </div>
    </div>
    
    <div class="game-controls">
        <button id="giveUpButton" class="control-btn give-up">
            <i class="fas fa-flag"></i> Bỏ cuộc
        </button>
        <button id="resetButton" class="control-btn reset">
            <i class="fas fa-redo"></i> Chơi lại
        </button>
        <button id="pauseButton" class="control-btn pause">
            <i class="fas fa-pause"></i> Tạm dừng
        </button>
        <button id="completeButton" class="control-btn complete">
            <i class="fas fa-check-circle"></i> Hoàn thành
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> Đếm số lượng của từng số từ 1-20 trong lưới bên dưới và nhập vào ô tương ứng</span>
        </div>
    </div>
    
    <div class="game-container">
        <div class="number-grid" id="numberGrid"></div>
        
        <div class="answer-section">
            <div class="answer-header">
                <h3>KẾT QUẢ ĐẾM SỐ</h3>
                <p>Nhập số lượng tương ứng cho mỗi số</p>
            </div>
            
            <div class="answer-grid" id="answerGrid"></div>
            
            <div class="answer-controls">
                <button id="checkAnswersButton" class="check-btn">
                    <i class="fas fa-check"></i> Kiểm tra
                </button>
                <button id="clearAnswersButton" class="clear-btn">
                    <i class="fas fa-eraser"></i> Xóa hết
                </button>
            </div>
            
            <div class="result-feedback" id="resultFeedback"></div>
        </div>
    </div>
    
    <div class="navigation-controls">
        <button id="prevButton" class="nav-btn prev-btn">
            <i class="fas fa-chevron-left"></i> TRƯỚC
        </button>
        <button id="nextButton" class="nav-btn next-btn">
            TIẾP THEO <i class="fas fa-chevron-right"></i>
        </button>
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
        [16, 2, 18, 19, 1, 3, 8, 13, 7, 10, 2, 16, 4, 12, 15, 2, 15],
        [14, 3, 8, 11, 9, 3, 8, 15, 14, 4, 10, 11, 9, 15, 2, 4, 11],
        [6, 5, 19, 11, 12, 12, 4, 7, 16, 7, 5, 13, 6, 14, 8, 9, 3],
        [5, 1, 20, 20, 12, 7, 11, 19, 20, 16, 6, 10, 15, 20, 4, 14],
        [5, 1, 17, 8, 4, 14, 12, 17, 13, 6, 11, 10, 18, 3, 2, 17, 18],
        [17, 5, 10, 9, 9, 9, 8, 14, 2, 20, 9, 3, 16, 2, 1, 1, 17],
        [4, 14, 2, 12, 20, 1, 11, 18, 10, 16, 11, 11, 9, 6, 19, 7],
        [18, 1, 5, 5, 17, 18, 6, 18, 6, 11, 17, 18, 3, 3, 5, 1, 10],
        [20, 9, 12, 15, 20, 16, 16, 19, 10, 15, 15, 15, 13, 7, 17, 19],
        [19, 7, 8, 2, 13, 19, 3, 1, 20, 6, 7, 10, 4, 12, 7, 13, 5],
        [2, 16, 12, 5, 12, 10, 3, 20, 14, 8, 1, 13, 8, 8, 4, 17, 19],
        [18, 16, 6, 15, 17, 13, 14, 14, 9, 19, 13, 7, 13, 18, 4, 6]
    ];
</script>

<script src="<?= $base_url ?>/public/JS/number_game.js?v=<?= time() ?>" defer></script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>