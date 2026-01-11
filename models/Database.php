<?php
class Database {
    private $host = "localhost";
    private $db_name = "learning_app";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Ensure user table columns are as expected (remove status/last_active, add notes)
            $this->ensureUserNotes();
           
            
           
        } catch(PDOException $exception) {
            // Nếu database chưa tồn tại, tạo mới
            if ($exception->getCode() == '1049') {
                return $this->createDatabase();
            } else {
                echo "Lỗi kết nối database: " . $exception->getMessage();
            }
        }
        return $this->conn;
    }

    // Ensure users table schema: remove status/last_active (if present) and add notes column
    private function ensureUserNotes() {
        if (!$this->conn) return;
        try {
            // Drop status column if exists
            $c = $this->conn->query("SHOW COLUMNS FROM users LIKE 'status'")->fetch();
            if ($c) {
                try { $this->conn->exec("ALTER TABLE users DROP COLUMN `status`"); } catch (Exception $e) { error_log('drop status failed: '.$e->getMessage()); }
            }
        } catch (Exception $e) {
            // ignore
        }

        try {
            // Drop last_active column if exists
            $c2 = $this->conn->query("SHOW COLUMNS FROM users LIKE 'last_active'")->fetch();
            if ($c2) {
                try { $this->conn->exec("ALTER TABLE users DROP COLUMN `last_active`"); } catch (Exception $e) { error_log('drop last_active failed: '.$e->getMessage()); }
            }
        } catch (Exception $e) {
            // ignore
        }

        try {
            // Add notes column if missing
            $c3 = $this->conn->query("SHOW COLUMNS FROM users LIKE 'notes'")->fetch();
            if (!$c3) {
                $this->conn->exec("ALTER TABLE users ADD COLUMN `notes` TEXT NULL DEFAULT ''");
            }
        } catch (Exception $e) {
            try {
                $c3b = $this->conn->query("SHOW COLUMNS FROM users LIKE 'notes'")->fetch();
                if (!$c3b) {
                    $this->conn->exec("ALTER TABLE users ADD COLUMN `notes` TEXT NULL DEFAULT ''");
                }
            } catch (Exception $e2) {
                error_log(' ensureUserNotes notes error: ' . $e2->getMessage());
            }
        }
        // Ensure phone column exists
        try {
            $c4 = $this->conn->query("SHOW COLUMNS FROM users LIKE 'phone'")->fetch();
            if (!$c4) {
                $this->conn->exec("ALTER TABLE users ADD COLUMN `phone` VARCHAR(25) DEFAULT NULL");
            }
        } catch (Exception $e) {
            try {
                $c4b = $this->conn->query("SHOW COLUMNS FROM users LIKE 'phone'")->fetch();
                if (!$c4b) {
                    $this->conn->exec("ALTER TABLE users ADD COLUMN `phone` VARCHAR(25) DEFAULT NULL");
                }
            } catch (Exception $e3) {
                error_log(' ensureUserNotes phone error: ' . $e3->getMessage());
            }
        }

        // Ensure users.xp column exists
        try {
            $cXp = $this->conn->query("SHOW COLUMNS FROM users LIKE 'xp'")->fetch();
            if (!$cXp) {
                $this->conn->exec("ALTER TABLE users ADD COLUMN `xp` INT DEFAULT 0");
            }
        } catch (Exception $e) {
            try {
                $cXp2 = $this->conn->query("SHOW COLUMNS FROM users LIKE 'xp'")->fetch();
                if (!$cXp2) {
                    $this->conn->exec("ALTER TABLE users ADD COLUMN `xp` INT DEFAULT 0");
                }
            } catch (Exception $e4) {
                error_log(' ensureUserNotes xp user error: ' . $e4->getMessage());
            }
        }

        // Ensure games.xp exists
        try {
            $cGxp = $this->conn->query("SHOW COLUMNS FROM games LIKE 'xp'")->fetch();
            if (!$cGxp) {
                $this->conn->exec("ALTER TABLE games ADD COLUMN `xp` INT DEFAULT 20");
            }
        } catch (Exception $e) {
            try {
                $cGxp2 = $this->conn->query("SHOW COLUMNS FROM games LIKE 'xp'")->fetch();
                if (!$cGxp2) {
                    $this->conn->exec("ALTER TABLE games ADD COLUMN `xp` INT DEFAULT 20");
                }
            } catch (Exception $e5) {
                error_log(' ensureUserNotes xp games error: ' . $e5->getMessage());
            }
        }

        // Ensure scores.xp_awarded exists
        try {
            $cSxp = $this->conn->query("SHOW COLUMNS FROM scores LIKE 'xp_awarded'")->fetch();
            if (!$cSxp) {
                $this->conn->exec("ALTER TABLE scores ADD COLUMN `xp_awarded` INT DEFAULT 0");
            }
        } catch (Exception $e) {
            try {
                $cSxp2 = $this->conn->query("SHOW COLUMNS FROM scores LIKE 'xp_awarded'")->fetch();
                if (!$cSxp2) {
                    $this->conn->exec("ALTER TABLE scores ADD COLUMN `xp_awarded` INT DEFAULT 0");
                }
            } catch (Exception $e6) {
                error_log(' ensureUserNotes xp scores error: ' . $e6->getMessage());
            }
        }
    }

    // Tạo database mới
    private function createDatabase() {
        try {
            // Kết nối tới MySQL mà không chọn database
            $temp_conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $temp_conn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8 COLLATE utf8_unicode_ci");
            
            // Đóng kết nối tạm
            $temp_conn = null;
            
            // Kết nối lại tới database mới tạo
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Tạo các bảng
            $this->createTables();
            // Ensure columns exist after initial creation
            $this->ensureUserNotes();
            
            echo "✅ Đã tạo database '" . $this->db_name . "' thành công!";
            
            return $this->conn;
        } catch(PDOException $exception) {
            echo "Lỗi tạo database: " . $exception->getMessage();
            return null;
        }
    }

    // Tạo các bảng theo sơ đồ
    private function createTables() {
        $sql = [
            // Bảng người dùng
            "CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(100),
                last_name VARCHAR(100),
                class VARCHAR(50),
                phone VARCHAR(25) DEFAULT NULL,
                xp INT DEFAULT 0,
                notes TEXT NULL DEFAULT '',
                role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
                avatar VARCHAR(255),
                email_verified TINYINT(1) DEFAULT 0,
                verification_code VARCHAR(10),
                verification_expires DATETIME,
                reset_code VARCHAR(6),
                reset_code_expires DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",

            // Bảng chủ đề (Chỉ đề) - ĐỔI TÊN THÀNH stem_fields để index.php không lỗi
            "CREATE TABLE IF NOT EXISTS stem_fields (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL,
                description TEXT,
                icon VARCHAR(100),
                color VARCHAR(7),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",

            // Bảng trò chơi (catalog) - mỗi game có thể thuộc một chủ đề (topic)
            "CREATE TABLE IF NOT EXISTS games (
                id INT PRIMARY KEY AUTO_INCREMENT,
                topic_id INT DEFAULT NULL,
                game_name VARCHAR(255) NOT NULL,
                description TEXT,
                passing_score INT DEFAULT NULL,
                xp INT DEFAULT 20,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (topic_id) REFERENCES stem_fields(id) ON DELETE SET NULL
            )",

            // Bảng tác phẩm
            "CREATE TABLE IF NOT EXISTS works (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
              content TEXT,
                image_url VARCHAR(255),
                video_url VARCHAR(255),
                work_time INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",

            // Bảng điểm
            "CREATE TABLE IF NOT EXISTS scores (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                game_id INT,
                score_percentage INT NOT NULL,
                xp_awarded INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
            )",

            // Note: `user_game_completions` removed; completion state will be
            // derived from `scores` combined with `games.passing_score`.
            // Table to store awarded certificates per topic (stem_field)
            "CREATE TABLE IF NOT EXISTS certificates (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                topic_id INT NOT NULL,
                issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_user_topic (user_id, topic_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (topic_id) REFERENCES stem_fields(id) ON DELETE CASCADE
            )",

            // Bảng xếp hạng từng trò chơi
            "CREATE TABLE IF NOT EXISTS game_leaderboards (
                id INT PRIMARY KEY AUTO_INCREMENT,
                game_id INT,
                user_id INT,
                total_score INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",

            // Bảng xếp hạng hoạt động tuần
            "CREATE TABLE IF NOT EXISTS weekly_leaderboards (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                total_score INT,
                week_number INT,
                year INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",

            // Bảng lưu token ghi nhớ đăng nhập với cơ chế selector/validator
            "CREATE TABLE IF NOT EXISTS remember_tokens (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                selector VARCHAR(24) UNIQUE NOT NULL,
                hashed_validator VARCHAR(128) NOT NULL,
                user_agent VARCHAR(255) NULL,
                ip VARCHAR(45) NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_selector (selector)
            )"
        ];

        // Thực thi từng câu lệnh SQL
        foreach ($sql as $statement) {
            try {
                $this->conn->exec($statement);
            } catch(PDOException $e) {
                echo "Lỗi khi tạo bảng: " . $e->getMessage() . "<br>";
            }
        }

        // Chèn dữ liệu mẫu
        $this->insertSampleData();
    }

    // Chèn dữ liệu mẫu
    private function insertSampleData() {
        try {
            // Kiểm tra xem đã có dữ liệu chưa
            $check = $this->conn->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC);
            
            if ($check['count'] == 0) {
                // Tạo tài khoản admin và user mặc định (mật khẩu: 123456)
                $hashed_password = password_hash('123456', PASSWORD_DEFAULT);
                $this->conn->exec("INSERT INTO users (username, email, password, first_name, last_name, class, role, email_verified) VALUES
                ('admin', 'admin@stem.edu.vn', '$hashed_password', 'Admin', 'System', NULL, 'admin', 1),
                ('student1', 'student1@stem.edu.vn', '$hashed_password', 'Minh', 'Nguyễn', '5A1', 'user', 1),
                ('student2', 'student2@stem.edu.vn', '$hashed_password', 'Lan', 'Trần', '5A2', 'user', 1)");

                $this->conn->exec("INSERT INTO stem_fields (name, description, icon, color) VALUES
                ('Toán học', 'Môn học về số học và hình học', '📊', '#A594F9'),
                ('Khoa học', 'Khám phá thế giới tự nhiên', '🔬', '#FF9E6D'),
                ('Công nghệ', 'Lập trình và robot', '🤖', '#96CEB4'),
                ('Kỹ thuật', 'Xây dựng và sáng tạo', '⚙️', '#FFD166')");

                $this->conn->exec("INSERT INTO games (topic_id, game_name, description, passing_score) VALUES 
                -- Toán học (topic_id = 1)
                (1, 'Trò chơi Hình dạng', 'Bài tập nhận diện và sắp xếp hình học', 50),
                (1, 'Trò chơi Số học', 'Các bài tập về số và tính toán', 50),
                (1, 'Trò chơi Góc và đo lường', 'Bài toán liên quan đến góc và đo lường', 50),
                (1, 'Tangram', 'Xếp hình tangram để tạo hình', 50),
                (1, 'Trò chơi Thời gian', 'Bài luyện đọc đồng hồ và giờ', 50),

                -- Khoa học (topic_id = 2)
                (2, 'Tháp dinh dưỡng', 'Sắp xếp các nhóm thực phẩm theo tháp dinh dưỡng', 50),
                (2, 'Pha màu', 'Pha màu đúng tỉ lệ', 50),
                (2, 'Ngày và đêm', 'Trả lời các câu hỏi về ngày và đêm', 50),
                (2, 'Thùng rác thân thiện', 'Phân loại rác đúng cách', 50),
                (2, 'Lắp ghép bộ phận', 'Lắp ghép các bộ phận của cây', 50),

                -- Công nghệ (topic_id = 3)
                (3, 'Cây gia đình', 'Tìm hiểu về các mối quan hệ gia đình qua cây phả hệ', 50),
                (3, 'Trò chơi Lập trình (Coding)', 'Các thử thách lập trình cơ bản', 50),
                (3, 'Em làm họa sĩ máy tính', 'Khám phá các công cụ vẽ đơn giản trên máy tính', 50),
                (3, 'Các bộ phận của máy tính', 'Tìm hiểu các thành phần cơ bản của máy tính', 50),
                (3, 'Em là người đánh máy', 'Rèn luyện kỹ năng đánh máy nhanh và chính xác', 50),

                -- Kỹ thuật (topic_id = 4)
                (4, 'Hoa yêu thương nở rộ', 'Thiết kế hoa giấy cơ học nở rộ khi kéo dây', 50),
                (4, 'Cầu kỹ thuật (Bridge)', 'Thiết kế cầu và thử tải', 50),
                (4, 'Chế tạo xe (Car Builder)', 'Lắp ráp và tối ưu xe cho bài toán', 50),
                (4, 'Bộ lọc nước', 'Thiết kế bộ lọc đơn giản để làm sạch nước', 50),
                (4, 'Trò chơi Tháp', 'Thử thách xây tháp bền vững', 50),
                (4, 'Trang trí phòng (Room Decor)', 'Thiết kế trang trí phòng sáng tạo', 50)
                ");



    

            }
        } catch(PDOException $e) {
            echo "Lỗi khi chèn dữ liệu mẫu: " . $e->getMessage();
        }
    }
}
?>