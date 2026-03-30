<?php

class LessonController {

    private function findLessonIdByName($db, $name) {
        if (!$db || empty($name)) return null;
        try {
            $stmt = $db->prepare("SELECT id FROM lessons WHERE lesson_name = :name LIMIT 1");
            $stmt->execute([':name' => $name]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) return (int)$row['id'];

            $stmt = $db->prepare("SELECT id FROM lessons WHERE lesson_name LIKE :like LIMIT 1");
            $stmt->execute([':like' => '%' . $name . '%']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) return (int)$row['id'];

            $parts = preg_split('/\s+/', trim($name));
            foreach ($parts as $p) {
                if (strlen($p) < 3) continue;
                $stmt = $db->prepare("SELECT id FROM lessons WHERE lesson_name LIKE :like LIMIT 1");
                $stmt->execute([':like' => '%' . $p . '%']);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) return (int)$row['id'];
            }
        } catch (Exception $e) {
        }
        return null;
    }

    private function getTopicIdFromLesson($db, $lessonId) {
        if (!$db || empty($lessonId)) return null;
        try {
            $tstmt = $db->prepare("SELECT topic_id FROM lessons WHERE id = :lid LIMIT 1");
            $tstmt->execute([':lid' => $lessonId]);
            $trow = $tstmt->fetch(PDO::FETCH_ASSOC);
            if ($trow && !empty($trow['topic_id'])) return (int)$trow['topic_id'];
        } catch (Exception $e) {
        }
        return null;
    }

    public function commitQuizScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['lesson']) || !isset($data['score'])) {
            echo json_encode(['success' => false, 'message' => 'Missing lesson or score']);
            return;
        }

        if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $lessonName = $data['lesson'];
        $score = (int)$data['score'];
        $xp = isset($data['xp']) ? (int)$data['xp'] : 0;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            $stmt = $db->prepare("SELECT id FROM games WHERE game_name = :name LIMIT 1");
            $stmt->execute([':name' => $lessonName]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $gameId = (int)$row['id'];
            } else {
              
                $stmt2 = $db->prepare("SELECT id FROM games WHERE game_name LIKE :like LIMIT 1");
                $stmt2->execute([':like' => '%' . $lessonName . '%']);
                $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                if ($r2) {
                    $gameId = (int)$r2['id'];
                } else {
                    echo json_encode(['success' => false, 'message' => 'Game record not found for: ' . $lessonName]);
                    return;
                }
            }

            $userId = (int)$_SESSION['user_id'];
            $scorePct = max(0, min(100, (int)$score));
            $res = Score::saveAndMark($userId, $gameId, $scorePct, $xp);

            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    /**
     * TRÒ CHƠI PHA MÀU
     */
    public function showColorGame() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $showIntroModal = empty($_SESSION['color_game_intro_seen']);
        if ($showIntroModal) {
            $_SESSION['color_game_intro_seen'] = true;
        }

        if (!isset($_SESSION['total_score'])) {
            $_SESSION['total_score'] = 0;
        }
        if (!isset($_SESSION['total_xp'])) {
            $_SESSION['total_xp'] = 0;
        }

        if (isset($_GET['end'])) {
            $_SESSION['available_targets'] = [];
            unset($_SESSION['current_target']);
            unset($_SESSION['current_attempt']);
        }

        if (isset($_GET['next'])) {
            if (isset($_GET['points'])) {
                $_SESSION['total_score'] += (int)$_GET['points'];
            }
            if (isset($_GET['xp'])) {
                if (!isset($_SESSION['total_xp'])) $_SESSION['total_xp'] = 0;
                $_SESSION['total_xp'] += (int)$_GET['xp'];
            }
            unset($_SESSION['current_target']);
            unset($_SESSION['current_attempt']);
            if (isset($_SESSION['color_game_committed'])) {
                unset($_SESSION['color_game_committed']);
            }
            if (empty($_SESSION['available_targets']) && !isset($_GET['points'])) {
                $_SESSION['total_score'] = 0;
                unset($_SESSION['available_targets']);
                if (isset($_SESSION['total_xp'])) unset($_SESSION['total_xp']);
            }
        }

        $targets = [
            ["name" => "orange", "text" => "Hãy pha trộn màu CAM 🍊", "rgb" => [255, 165, 0], "colors" => ["red", "yellow"]],
            ["name" => "green", "text" => "Hãy pha trộn màu XANH LÁ 🍃", "rgb" => [0, 128, 0], "colors" => ["blue", "yellow"]],
            ["name" => "purple", "text" => "Hãy pha trộn màu TÍM 💜", "rgb" => [128, 0, 128], "colors" => ["red", "blue"]],
            ["name" => "gray", "text" => "Hãy pha trộn màu XÁM ⚙️", "rgb" => [128, 128, 128], "colors" => ["black", "white"]]
        ];

        if (!isset($_SESSION['available_targets'])) {
            $uniqueTargets = [];
            foreach ($targets as $item) {
                $uniqueTargets[$item['name']] = $item;
            }
            $_SESSION['available_targets'] = array_values($uniqueTargets);
            shuffle($_SESSION['available_targets']);
        }

        if (!isset($_SESSION['current_target'])) {
            if (!empty($_SESSION['available_targets'])) {
                $candidate = array_pop($_SESSION['available_targets']);
                if (
                    isset($_SESSION['last_target_name'])
                    && $candidate['name'] === $_SESSION['last_target_name']
                    && !empty($_SESSION['available_targets'])
                ) {
                    array_unshift($_SESSION['available_targets'], $candidate);
                    $candidate = array_pop($_SESSION['available_targets']);
                }

                $_SESSION['current_target'] = $candidate;
                $_SESSION['last_target_name'] = $candidate['name'];
                $_SESSION['current_attempt'] = 1;
                $target = $_SESSION['current_target'];
            } else {
                $target = null; 
            }
        } else {
            $target = $_SESSION['current_target'];
        }

        $current_attempt = $_SESSION['current_attempt'] ?? 1;
        $correct_colors_sorted = [];
        if ($target) {
            $correct_colors_sorted = $target['colors'];
            sort($correct_colors_sorted);
        }

        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $passingThreshold = 25;
        $completionResult = null;
        if ($target === null) {
            if (!empty($_SESSION['user_id']) && empty($_SESSION['color_game_committed'])) {
                try {
                    require_once __DIR__ . '/../models/Database.php';
                    require_once __DIR__ . '/../models/Score.php';

                    $database = new Database();
                    $db = $database->getConnection();

                    $stmt = $db->prepare("SELECT id FROM games WHERE game_name LIKE :name LIMIT 1");
                    $stmt->execute([':name' => '%Pha màu%']);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $gameId = (int)$row['id'];
                    } else {
                        $stmt2 = $db->prepare("SELECT id FROM games WHERE game_name LIKE :like LIMIT 1");
                        $stmt2->execute([':like' => '%Pha màu%']);
                        $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                        if ($r2) {
                            $gameId = (int)$r2['id'];
                        } else {
                            $completionResult = ['success' => false, 'message' => 'Game "Pha màu" not registered in database'];
                        }
                    }

                    $userId = (int)$_SESSION['user_id'];
                    $rawScore = isset($_SESSION['total_score']) ? (int)$_SESSION['total_score'] : 0;
                    $maxPoints = count($targets) * 10;
                    $percentage = 0;
                    if ($maxPoints > 0) {
                        $percentage = (int)round(($rawScore / $maxPoints) * 100);
                        if ($percentage > 100) $percentage = 100;
                        if ($percentage < 0) $percentage = 0;
                    }

                    $rawXp = isset($_SESSION['total_xp']) ? (int)$_SESSION['total_xp'] : 0;
                    $xpAwarded = $rawXp;
                    if (!empty($gameId)) {
                        $gstmt = $db->prepare("SELECT xp FROM games WHERE id = :gid LIMIT 1");
                        $gstmt->execute([':gid' => $gameId]);
                        $gRow = $gstmt->fetch(PDO::FETCH_ASSOC);
                        if ($gRow && isset($gRow['xp'])) {
                            $gameXpCap = (int)$gRow['xp'];
                            if ($xpAwarded > $gameXpCap) $xpAwarded = $gameXpCap;
                        }
                    }

                    if (!empty($gameId)) {
                        $completionResult = Score::saveAndMark($userId, $gameId, $percentage, $xpAwarded);
                    } else {
                    }

                    $_SESSION['color_game_committed'] = true;
                } catch (Exception $e) {
                    error_log('Color game commit error: ' . $e->getMessage());
                }
            }
        }

        if (!isset($percentage)) {
            $rawScore = isset($_SESSION['total_score']) ? (int)$_SESSION['total_score'] : 0;
            $maxPoints = count($targets) * 10;
            $percentage = 0;
            if ($maxPoints > 0) {
                $percentage = (int)round(($rawScore / $maxPoints) * 100);
                if ($percentage > 100) $percentage = 100;
                if ($percentage < 0) $percentage = 0;
            }
        }

        require_once __DIR__ . '/../views/lessons/science_color_game.php';
    }


    /**
     * TRÒ CHƠI THÁP DINH DƯỠNG
     */
    public function showNutritionGame() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['nutrition_score'])) {
            $_SESSION['nutrition_score'] = 0;
        }

        $foodItems = [
            ['id' => 'food1', 'name' => 'Hạt', 'group' => 1, 'img' => 'hat.png'],
            ['id' => 'food2', 'name' => 'Đậu', 'group' => 1, 'img' => 'hat_dau.png'],
            ['id' => 'food3', 'name' => 'Bánh mì', 'group' => 1, 'img' => 'banh_mi.png'],
            ['id' => 'food4', 'name' => 'Sandwich', 'group' => 1, 'img' => 'sandwich.png'],
            ['id' => 'food5', 'name' => 'Mì', 'group' => 1, 'img' => 'mi.png'],
            ['id' => 'food6', 'name' => 'Cơm', 'group' => 1, 'img' => 'com.png'],
            ['id' => 'food7', 'name' => 'Pasta', 'group' => 1, 'img' => 'pasta.png'],
            ['id' => 'food8', 'name' => 'Ngũ cốc', 'group' => 1, 'img' => 'ngu_coc.png'],

            ['id' => 'food9', 'name' => 'Cà chua', 'group' => 2, 'img' => 'ca_chua.png'],
            ['id' => 'food10', 'name' => 'Ớt chuông', 'group' => 2, 'img' => 'ot_chuong.png'],
            ['id' => 'food11', 'name' => 'Nấm', 'group' => 2, 'img' => 'nam.png'],
            ['id' => 'food12', 'name' => 'Cà rốt', 'group' => 2, 'img' => 'ca_rot.png'],
            ['id' => 'food13', 'name' => 'Cam', 'group' => 2, 'img' => 'cam.png'],
            ['id' => 'food14', 'name' => 'Chuối', 'group' => 2, 'img' => 'chuoi.png'],
            ['id' => 'food15', 'name' => 'Nho', 'group' => 2, 'img' => 'nho.png'],
            ['id' => 'food16', 'name' => 'Dâu', 'group' => 2, 'img' => 'dau.png'],

            ['id' => 'food17', 'name' => 'Yogurt', 'group' => 3, 'img' => 'yogurt.png'],
            ['id' => 'food18', 'name' => 'Sữa', 'group' => 3, 'img' => 'sua.png'],
            ['id' => 'food19', 'name' => 'Phô mai', 'group' => 3, 'img' => 'pho_mai.png'],
            ['id' => 'food20', 'name' => 'Cá', 'group' => 3, 'img' => 'ca.png'],
            ['id' => 'food21', 'name' => 'Thịt', 'group' => 3, 'img' => 'thit.png'],
            ['id' => 'food22', 'name' => 'Đùi gà', 'group' => 3, 'img' => 'dui_ga.png'],
            ['id' => 'food23', 'name' => 'Trứng', 'group' => 3, 'img' => 'trung.png'],
            ['id' => 'food24', 'name' => 'Tôm', 'group' => 3, 'img' => 'tom.png'],

            ['id' => 'food25', 'name' => 'Dầu ăn', 'group' => 4, 'img' => 'dau_an.png'],
            ['id' => 'food26', 'name' => 'Đường', 'group' => 4, 'img' => 'duong.png'],
            ['id' => 'food27', 'name' => 'Muối', 'group' => 4, 'img' => 'muoi.png'],
        ];

        shuffle($foodItems);

        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        
        require_once __DIR__ . '/../views/lessons/science_nutrition_game.php';
    }

    /**
     * API Cập nhật điểm (cho Game Dinh Dưỡng)
     */
    public function updateNutritionScore() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['nutrition_score'])) {
            $_SESSION['nutrition_score'] = 0;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if ($data) {
            if ($data['action'] === 'add_points' && isset($data['points'])) {
                $_SESSION['nutrition_score'] += (int)$data['points'];
            } elseif ($data['action'] === 'reset') {
                $_SESSION['nutrition_score'] = 0;
            } elseif ($data['action'] === 'commit') {
                require_once __DIR__ . '/../models/Database.php';
                require_once __DIR__ . '/../models/Score.php';

                $userId = $_SESSION['user_id'] ?? null;
                $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;
                $playTime = isset($data['play_time']) ? (int)$data['play_time'] : null;
                $totalTime = isset($data['total_time']) ? (int)$data['total_time'] : null;

                if (empty($userId)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'User not logged in']);
                    exit();
                }

                try {
                    $db = (new Database())->getConnection();

                    if (empty($gameId)) {
                        $stmt = $db->prepare("SELECT id FROM games WHERE game_name = :name LIMIT 1");
                        $stmt->execute([':name' => 'Tháp dinh dưỡng']);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);

                        $totalFoods = 27;
                        $threshold = (int)ceil(($totalFoods * 10) / 2.0);

                            if ($row) {
                                $gameId = (int)$row['id'];
                            } else {
                                $stmt2 = $db->prepare("SELECT id FROM games WHERE game_name LIKE :like LIMIT 1");
                                $stmt2->execute([':like' => '%Tháp dinh dưỡng%']);
                                $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                                if ($r2) {
                                    $gameId = (int)$r2['id'];
                                } else {
                                    header('Content-Type: application/json');
                                    echo json_encode(['success' => false, 'message' => 'Game "Tháp dinh dưỡng" not registered']);
                                    exit();
                                }
                            }
                    }

                    $raw = (int)$_SESSION['nutrition_score'];
                    $maxPoints = $totalFoods * 10;
                    $pct = 0;
                    if ($maxPoints > 0) {
                        $pct = (int)round(($raw / $maxPoints) * 100);
                        if ($pct > 100) $pct = 100;
                        if ($pct < 0) $pct = 0;
                    }

                    $passingScore = null;
                    try {
                        $pstmt = $db->prepare("SELECT passing_score FROM games WHERE id = :gid LIMIT 1");
                        $pstmt->execute([':gid' => $gameId]);
                        $prow = $pstmt->fetch(PDO::FETCH_ASSOC);
                        if ($prow && $prow['passing_score'] !== null) {
                            $passingScore = (int)$prow['passing_score'];
                        }
                    } catch (Exception $e) {
                    }

                    if ($passingScore === null) {
                        $passingScore = (int)ceil((($totalFoods * 10) / $maxPoints) * 100 / 2);
                        if ($passingScore <= 0) $passingScore = 50;
                    }

                    
                    if (!empty($_SESSION['nutrition_committed'])) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Already committed', 'newScore' => 0]);
                        exit();
                    }

                    $xpAwarded = 20; 

                    try {
                        $res = Score::saveAndMark($userId, $gameId, $pct, $xpAwarded);
                    } catch (Exception $e) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                        exit();
                    }

                    if (is_array($res) && !empty($res['success'])) {
                        $_SESSION['nutrition_score'] = 0;
                        $_SESSION['nutrition_committed'] = true;
                        $res['newScore'] = 0;
                    }

                    header('Content-Type: application/json');
                    echo json_encode($res);
                    exit();
                } catch (Exception $e) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit();
                }
            }
        }

        $raw = isset($_SESSION['nutrition_score']) ? (int)$_SESSION['nutrition_score'] : 0;
        $totalFoods = 27;
        $maxPoints = $totalFoods * 10;
        $pct = 0;
        if ($maxPoints > 0) {
            $pct = (int)round(($raw / $maxPoints) * 100);
            if ($pct > 100) $pct = 100;
            if ($pct < 0) $pct = 0;
        }
        header('Content-Type: application/json');
        echo json_encode(['newScore' => $pct]);
        exit();
    }

    /**
     * TRÒ CHƠI LẮP GHÉP BỘ PHẬN CÂY
     */
    public function showPlantGame() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        
        $plantType = $_GET['type'] ?? 'hoa';
        
        // *** TOÀN BỘ DỮ LIỆU 5 LOẠI CÂY MỚI ***
        $allPlantsData = [
            
            // === 1. CÂY HOA ===
            'hoa' => [
                'title' => 'Cây Hoa',
                'image_bg' => 'plant_hoa_bg.png',
                'parts' => [
                    ['id' => 'label-hoa', 'name' => 'hoa', 'text' => 'Hoa'],
                    ['id' => 'label-la', 'name' => 'la', 'text' => 'Lá'],
                    ['id' => 'label-than', 'name' => 'than', 'text' => 'Thân'],
                    ['id' => 'label-re', 'name' => 're', 'text' => 'Rễ'],
                ],
                'dropzones' => [
                    ['target' => 'hoa', 'top' => '26%', 'left' => '58.2%', 'width' => '6%', 'height' => '8%'],
                    ['target' => 'la', 'top' => '45.5%', 'left' => '58.4%', 'width' => '5.3%', 'height' => '10%'],
                    ['target' => 'than', 'top' => '58.5%', 'left' => '41.5%', 'width' => '5%', 'height' => '8%'],
                    ['target' => 're', 'top' => '78.3%', 'left' => '57.3%', 'width' => '5.8%', 'height' => '8.6%'],
                ]
            ],
            
            // === 2. CÂY CỔ THỤ ===
            'cothu' => [
                'title' => 'Cây Cổ Thụ',
                'image_bg' => 'plant_cothu_bg.png',
                'parts' => [
                    ['id' => 'label-la', 'name' => 'la', 'text' => 'Lá'],
                    ['id' => 'label-canh', 'name' => 'canh', 'text' => 'Cành'],
                    ['id' => 'label-than', 'name' => 'than', 'text' => 'Thân'],
                    ['id' => 'label-re', 'name' => 're', 'text' => 'Rễ'],
                ],
                'dropzones' => [
                    ['target' => 'la', 'top' => '27.5%', 'left' => '62.3%', 'width' => '5.5%', 'height' => '9.5%'],
                    ['target' => 'canh', 'top' => '35.2%', 'left' => '34.8%', 'width' => '6.1%', 'height' => '10.5%'],
                    ['target' => 'than', 'top' => '56%', 'left' => '39.5%', 'width' => '5.5%', 'height' => '10.3%'],
                    ['target' => 're', 'top' => '77.5%', 'left' => '59%', 'width' => '5.5%', 'height' => '10%'],
                ]
            ],
            
            // === 3. CÂY CỦ ===
            'cu' => [
                'title' => 'Cây Củ',
                'image_bg' => 'plant_cu_bg.png',
                'parts' => [
                    ['id' => 'label-la', 'name' => 'la', 'text' => 'Lá'],
                    ['id' => 'label-cu', 'name' => 'cu', 'text' => 'Củ'],
                    ['id' => 'label-re', 'name' => 're', 'text' => 'Rễ'],
                ],
                'dropzones' => [
                    ['target' => 'la', 'top' => '27%', 'left' => '56.5%', 'width' => '6.8%', 'height' => '10%'],
                    ['target' => 'cu', 'top' => '58%', 'left' => '53.5%', 'width' => '6.0%', 'height' => '10%'],
                    ['target' => 're', 'top' => '77%', 'left' => '56.5%', 'width' => '5.8%', 'height' => '10%'],
                ]
            ],
            
            // === 4. CÂY ĂN QUẢ ===
            'anqua' => [
                'title' => 'Cây Ăn Quả',
                'image_bg' => 'plant_anqua_bg.png',
                'parts' => [
                    ['id' => 'label-qua', 'name' => 'qua', 'text' => 'Quả'],
                    ['id' => 'label-la', 'name' => 'la', 'text' => 'Lá'],
                    ['id' => 'label-canh', 'name' => 'canh', 'text' => 'Cành'],
                    ['id' => 'label-than', 'name' => 'than', 'text' => 'Thân'],
                    ['id' => 'label-re', 'name' => 're', 'text' => 'Rễ'],
                ],
                'dropzones' => [
                    ['target' => 'qua', 'top' => '50.5%', 'left' => '55.5%', 'width' => '6.1%', 'height' => '9.7%'],
                    ['target' => 'la', 'top' => '29%', 'left' => '62.7%', 'width' => '6%', 'height' => '9.5%'],
                    ['target' => 'canh', 'top' => '9%', 'left' => '32.9%', 'width' => '6%', 'height' => '10.7%'],
                    ['target' => 'than', 'top' => '56.5%', 'left' => '37.7%', 'width' => '6%', 'height' => '10%'],
                    ['target' => 're', 'top' => '77.5%', 'left' => '55.7%', 'width' => '5.4%', 'height' => '10.2%'],
                ]
            ],
            
            // === 5. CÂY DÂY LEO ===
            'dayleo' => [
                'title' => 'Cây Dây Leo',
                'image_bg' => 'plant_dayleo_bg.png',
                'parts' => [
                    ['id' => 'label-la', 'name' => 'la', 'text' => 'Lá'],
                    ['id' => 'label-hoa', 'name' => 'hoa', 'text' => 'Hoa'],
                    ['id' => 'label-than', 'name' => 'than', 'text' => 'Thân (dây)'],
                    ['id' => 'label-qua', 'name' => 'qua', 'text' => 'Quả'],
                    ['id' => 'label-re', 'name' => 're', 'text' => 'Rễ'],
                ],
                'dropzones' => [
                    ['target' => 'la', 'top' => '8.8%', 'left' => '49.6%', 'width' => '9.5%', 'height' => '10.5%'],
                    ['target' => 'hoa', 'top' => '20.7%', 'left' => '15%', 'width' => '10.2%', 'height' => '11.3%'],
                    ['target' => 'than', 'top' => '57.5%', 'left' => '14.8%', 'width' => '11.5%', 'height' => '12.1%'],
                    ['target' => 'qua', 'top' => '37.6%', 'left' => '74.5%', 'width' => '11.0%', 'height' => '12.8%'],
                    ['target' => 're', 'top' => '82.5%', 'left' => '43.8%', 'width' => '11.7%', 'height' => '12.7%'],
                ]
            ],
        ];
        
        $keys = array_keys($allPlantsData); 
        $currentIndex = array_search($plantType, $keys);
        $nextType = null;

        if ($currentIndex !== false && isset($keys[$currentIndex + 1])) {
            $nextType = $keys[$currentIndex + 1];
        }
        $prevType = null;
        if ($currentIndex !== false && isset($keys[$currentIndex - 1])) {
            $prevType = $keys[$currentIndex - 1];
        }
        
        $plantData = $allPlantsData[$plantType] ?? $allPlantsData['hoa']; 
        shuffle($plantData['parts']);

        require_once __DIR__ . '/../views/lessons/science_plant_game.php';
    }

    /**
     * API Cập nhật điểm (cho Game Ghép Cây)
     */
    public function updatePlantScore() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        header('Content-Type: application/json');

        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            exit();
        }

        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Score.php';

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit();
        }

        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            $db = (new Database())->getConnection();
            if (empty($gameId)) {
                try {
                    $gstmt = $db->prepare("SELECT id FROM games WHERE game_name = :name LIMIT 1");
                    $gstmt->execute([':name' => 'Lắp ghép bộ phận']);
                    $gRow = $gstmt->fetch(PDO::FETCH_ASSOC);
                    if ($gRow) {
                        $gameId = (int)$gRow['id'];
                    } else {
                        $gstmt2 = $db->prepare("SELECT id FROM games WHERE game_name LIKE :like LIMIT 1");
                        $gstmt2->execute([':like' => '%Lắp ghép bộ phận%']);
                        $gRow2 = $gstmt2->fetch(PDO::FETCH_ASSOC);
                        if ($gRow2) $gameId = (int)$gRow2['id'];
                    }
                } catch (Exception $e) {
                }
            }

            $pct = 100;

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Lắp ghép bộ phận" not registered']);
                exit();
            }

            
            $xpAwarded = 20;
            if (isset($data['xp'])) {
                $xpAwarded = (int)$data['xp'];
            } else {
                try {
                    if (!empty($gameId)) {
                        $gstmt = $db->prepare('SELECT xp FROM games WHERE id = :gid LIMIT 1');
                        $gstmt->execute([':gid' => $gameId]);
                        $grow = $gstmt->fetch(PDO::FETCH_ASSOC);
                        if ($grow && isset($grow['xp'])) {
                            $xpAwarded = (int)$grow['xp'];
                        }
                    }
                } catch (Exception $e) {
                }
            }

            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res) && !empty($res['success'])) {
            }
            echo json_encode($res);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }

    public function showMathShapesGame() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['shapes_score'])) {
            $_SESSION['shapes_score'] = 0;
        }
        if (!isset($_SESSION['shapes_committed'])) {
            $_SESSION['shapes_committed'] = false;
        }

        require_once __DIR__ . '/../views/lessons/math_shapes_challenge.php';
    }

    /**
     * 
     * Game name: 'Trò chơi Hình dạng'
     */
    public function updateShapesScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $scorePct = isset($data['score_pct']) ? (int)$data['score_pct'] : null;
        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Trò chơi Hình dạng']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Hình dạng%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Trò chơi Hình dạng" not registered']);
                return;
            }

            $pct = 0;
            if ($scorePct !== null) {
                $pct = max(0, min(100, $scorePct));
            } else {
                $raw = isset($_SESSION['shapes_score']) ? (int)$_SESSION['shapes_score'] : 0;
                $totalChallenges = isset($data['total_challenges']) ? (int)$data['total_challenges'] : 6;
                $maxPoints = max(1, $totalChallenges) * 100;
                $pct = ($maxPoints > 0) ? (int) round((($raw / $maxPoints) * 100)) : 0;
                if ($pct > 100) $pct = 100;
                if ($pct < 0) $pct = 0;
            }

            $xpAwarded = 20;
            if (isset($data['xp'])) $xpAwarded = (int)$data['xp'];

            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }

            $_SESSION['shapes_committed'] = true;

            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    public function showMathNumberGame() {
        if (!isset($_SESSION['number_score'])) {
          $_SESSION['number_score'] = 0;
        }
        require_once __DIR__ . '/../views/lessons/math_number_game.php';
   }

    /**
     * Hiển thị TRÒ CHƠI PHÂN LOẠI RÁC
     */
    public function showTrashGame() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['trash_score'])) {
            $_SESSION['trash_score'] = 0;
        }

        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        $trashItems = [
            // Rác Vô Cơ
            ['id' => 'trash1', 'name' => 'Bao tay rách', 'group' => 'voco', 'img' => 'bao_tay_rach.png', 'top' => '70%', 'left' => '63%'],
            ['id' => 'trash2', 'name' => 'Túi nilon rách', 'group' => 'voco', 'img' => 'tui_nilon_rach.png', 'top' => '41%', 'left' => '4%'],
            ['id' => 'trash3', 'name' => 'Chai thủy tinh vỡ', 'group' => 'voco', 'img' => 'chai_vo.png', 'top' => '90%', 'left' => '3%'],
            ['id' => 'trash4', 'name' => 'Cốc vỡ', 'group' => 'voco', 'img' => 'coc_vo.png', 'top' => '47%', 'left' => '60%'],
            ['id' => 'trash5', 'name' => 'Áo mưa rách', 'group' => 'voco', 'img' => 'ao_mua_rach.png', 'top' => '73%', 'left' => '38%'],
            ['id' => 'trash6', 'name' => 'Dép hỏng', 'group' => 'voco', 'img' => 'dep_hong.png', 'top' => '25%', 'left' => '13%'],
            ['id' => 'trash7', 'name' => 'Bàn chải gãy', 'group' => 'voco', 'img' => 'ban_chai.png', 'top' => '4%', 'left' => '60%'],
            
            // Rác Hữu Cơ
            ['id' => 'trash8', 'name' => 'Vỏ trứng', 'group' => 'huuco', 'img' => 'vo_trung.png', 'top' => '55%', 'left' => '41%'],
            ['id' => 'trash9', 'name' => 'Vỏ chuối', 'group' => 'huuco', 'img' => 'vo_chuoi.png', 'top' => '68%', 'left' => '80%'],
            ['id' => 'trash10', 'name' => 'Ruột táo', 'group' => 'huuco', 'img' => 'ruot_tao.png', 'top' => '70%', 'left' => '15%'],
            ['id' => 'trash11', 'name' => 'Xương cá', 'group' => 'huuco', 'img' => 'xuong_ca.png', 'top' => '17%', 'left' => '83%'],
            ['id' => 'trash12', 'name' => 'Pizza thừa', 'group' => 'huuco', 'img' => 'pizza.png', 'top' => '22%', 'left' => '55%'],
            ['id' => 'trash13', 'name' => 'Vỏ dưa hấu', 'group' => 'huuco', 'img' => 'vo_dua_hau.png', 'top' => '84%', 'left' => '50%'],
            ['id' => 'trash14', 'name' => 'Lá cây', 'group' => 'huuco', 'img' => 'la_cay.png', 'top' => '90%', 'left' => '35%'],

            // Rác Tái Chế
            ['id' => 'trash15', 'name' => 'Áo', 'group' => 'taiche', 'img' => 'ao.png', 'top' => '21%', 'left' => '30%'],
            ['id' => 'trash16', 'name' => 'Thùng carton', 'group' => 'taiche', 'img' => 'thung_carton.png', 'top' => '57%', 'left' => '24%'],
            ['id' => 'trash17', 'name' => 'Túi giấy', 'group' => 'taiche', 'img' => 'tui_giay.png', 'top' => '57%', 'left' => '85%'],
            ['id' => 'trash18', 'name' => 'Vở', 'group' => 'taiche', 'img' => 'vo_sach.png', 'top' => '5%', 'left' => '40%'],
            ['id' => 'trash19', 'name' => 'Lon nước', 'group' => 'taiche', 'img' => 'lon_nuoc.png', 'top' => '62%', 'left' => '7%'],
            ['id' => 'trash20', 'name' => 'Chai thủy tinh', 'group' => 'taiche', 'img' => 'chai_thuy_tinh.png', 'top' => '48%', 'left' => '69.5%'],
            ['id' => 'trash21', 'name' => 'Túi nilon', 'group' => 'taiche', 'img' => 'tui_nilon.png', 'top' => '38%', 'left' => '88%'],
        ];
        
        shuffle($trashItems); 

        require_once __DIR__ . '/../views/lessons/science_trash_game.php';
    }

    /**
     * API Cập nhật điểm (cho Game Rác)
     */
    public function updateTrashScore() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['trash_score'])) {
            $_SESSION['trash_score'] = 0;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if ($data) {
            if ($data['action'] === 'add_points' && isset($data['points'])) {
                $_SESSION['trash_score'] += (int)$data['points'];
            } elseif ($data['action'] === 'reset') { 
                $_SESSION['trash_score'] = 0;
            } elseif ($data['action'] === 'commit') {
                require_once __DIR__ . '/../models/Score.php';
                require_once __DIR__ . '/../models/Database.php';
                $userId = $_SESSION['user_id'] ?? null;
                $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

                header('Content-Type: application/json');

                if (empty($userId)) {
                    echo json_encode(['success' => false, 'message' => 'User not logged in']);
                    exit();
                }

                try {
                    $db = (new Database())->getConnection();

                    
                    $raw = (int)($_SESSION['trash_score'] ?? 0);
                    $totalDropsParam = isset($data['total_drops']) ? (int)$data['total_drops'] : null;
                    if ($totalDropsParam && $totalDropsParam > 0) {
                        $maxPoints = $totalDropsParam * 10;
                        $pct = ($maxPoints > 0) ? (int) round((($raw / $maxPoints) * 100)) : 0;
                    } else {
                        $defaultItems = 21;
                        $maxPoints = $defaultItems * 10;
                        $pct = ($maxPoints > 0) ? (int) round((($raw / $maxPoints) * 100)) : 0;
                    }
                    if ($pct > 100) $pct = 100;
                    if ($pct < 0) $pct = 0;

                    if (empty($gameId)) {
                        try {
                            $gstmt = $db->prepare("SELECT id FROM games WHERE game_name = :name LIMIT 1");
                            $gstmt->execute([':name' => 'Thùng rác thân thiện']);
                            $gRow = $gstmt->fetch(PDO::FETCH_ASSOC);
                            if ($gRow) {
                                $gameId = (int)$gRow['id'];
                            } else {
                                // looser match
                                $gstmt2 = $db->prepare("SELECT id FROM games WHERE game_name LIKE :like LIMIT 1");
                                $gstmt2->execute([':like' => '%Thùng rác thân thiện%']);
                                $gRow2 = $gstmt2->fetch(PDO::FETCH_ASSOC);
                                if ($gRow2) $gameId = (int)$gRow2['id'];
                            }
                        } catch (Exception $e) {
                        }
                    }

                    $passingScore = null;
                    try {
                        if (!empty($gameId)) {
                            $pstmt = $db->prepare("SELECT passing_score FROM games WHERE id = :gid LIMIT 1");
                            $pstmt->execute([':gid' => $gameId]);
                            $prow = $pstmt->fetch(PDO::FETCH_ASSOC);
                            if ($prow && $prow['passing_score'] !== null) {
                                $passingScore = (int)$prow['passing_score'];
                            }
                        }
                    } catch (Exception $e) {
                    }

                    if ($passingScore === null) {
                        $passingScore = 50;
                    }

                        if (empty($gameId)) {
                            echo json_encode(['success' => false, 'message' => 'Game "Thùng rác thân thiện" not registered']);
                            exit();
                        }

            
                    $xpAwarded = 20;
                    if (isset($data['xp'])) {
                        $xpAwarded = (int)$data['xp'];
                    } else {
                        try {
                            if (!empty($gameId)) {
                                $gstmt = $db->prepare("SELECT xp FROM games WHERE id = :gid LIMIT 1");
                                $gstmt->execute([':gid' => $gameId]);
                                $grow = $gstmt->fetch(PDO::FETCH_ASSOC);
                                if ($grow && isset($grow['xp'])) {
                                    $xpAwarded = (int)$grow['xp'];
                                }
                            }
                        } catch (Exception $e) {
                        }
                    }

                    try {
                        $res = Score::saveAndMark($userId, $gameId, $pct, $xpAwarded);
                    } catch (Exception $e) {
                        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                        exit();
                    }

                    if (is_array($res) && !empty($res['success'])) {
                        $_SESSION['trash_score'] = 0;
                        $res['newScore'] = 0;
                        $res['xp_awarded'] = $xpAwarded;
                    }
                    echo json_encode($res);
                    exit();
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit();
                }
            }
        }

        
        $raw = isset($_SESSION['trash_score']) ? (int)$_SESSION['trash_score'] : 0;
        $totalDropsResp = isset($data['total_drops']) ? (int)$data['total_drops'] : null;
        if ($totalDropsResp && $totalDropsResp > 0) {
            $maxPointsResp = $totalDropsResp * 10;
            $pct = ($maxPointsResp > 0) ? (int) round((($raw / $maxPointsResp) * 100)) : 0;
        } else {
            $defaultItems = 21;
            $maxPointsResp = $defaultItems * 10;
            $pct = ($maxPointsResp > 0) ? (int) round((($raw / $maxPointsResp) * 100)) : 0;
        }
        if ($pct > 100) $pct = 100;
        if ($pct < 0) $pct = 0;
        header('Content-Type: application/json');
        echo json_encode(['newScore' => $pct]);
        exit();
    }

    /**
     * Bài học Ngày và Đêm
     */
    public function showDayNightLesson() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $questions = [
            [
                'id' => 1,
                'question' => 'Mặt trời mọc ở hướng nào?',
                'options' => [
                    'A' => 'Bắc',
                    'B' => 'Đông',
                    'C' => 'Nam',
                    'D' => 'Tây'
                ],
                'correct' => 'B',
                'explanation' => 'Do Trái Đất quay từ Tây sang Đông, nên ta luôn nhìn thấy Mặt Trời mọc từ hướng Đông.'
            ],
            [
                'id' => 2,
                'question' => 'Thời gian để Trái Đất quay hết một vòng quanh trục của mình là bao lâu?',
                'options' => [
                    'A' => '12 giờ',
                    'B' => '1 tháng',
                    'C' => '24 giờ',
                    'D' => '1 năm'
                ],
                'correct' => 'C',
                'explanation' => 'Trái Đất mất 24 giờ (một ngày đêm) để tự quay hết một vòng quanh trục của nó.'
            ],
            [
                'id' => 3,
                'question' => 'Khi một nửa Trái Đất hướng về phía Mặt Trời thì nửa đó là ban gì?',
                'options' => [
                    'A' => 'Ban đêm',
                    'B' => 'Ban ngày',
                    'C' => 'Cả ngày và đêm',
                    'D' => 'Buổi chiều'
                ],
                'correct' => 'B',
                'explanation' => 'Phần được Mặt Trời chiếu sáng sẽ là ban ngày, phần còn lại bị khuất bóng là ban đêm.'
            ],
            [
                'id' => 4,
                'question' => 'Câu nào sau đây là ĐÚNG về chuyển động của Trái Đất?',
                'options' => [
                    'A' => 'Trái Đất đứng yên, Mặt Trời quay quanh nó.',
                    'B' => 'Trái Đất vừa quay quanh Mặt Trời, vừa tự quay quanh mình nó.',
                    'C' => 'Trái Đất chỉ quay quanh Mặt Trời.',
                    'D' => 'Mặt Trời và Trái Đất đều đứng yên.'
                ],
                'correct' => 'B',
                'explanation' => 'Trái Đất không đứng yên mà luôn thực hiện 2 chuyển động cùng lúc: tự quay quanh trục và quay quanh Mặt Trời.'
            ],
            [
                'id' => 5,
                'question' => 'Nếu ở Việt Nam đang là buổi trưa, thì ở phía bên kia Trái Đất sẽ là:',
                'options' => [
                    'A' => 'Buổi sáng',
                    'B' => 'Buổi trưa',
                    'C' => 'Ban đêm',
                    'D' => 'Buổi chiều'
                ],
                'correct' => 'C',
                'explanation' => 'Vì Trái Đất hình cầu, khi một bên được chiếu sáng (buổi trưa) thì bên đối diện sẽ chìm trong bóng tối (ban đêm).'
            ]
        ];

        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        require_once __DIR__ . '/../views/lessons/science_day_night.php';
    }

    public function showFamilyTree() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $base_url = str_replace('\\', '/', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
        
        $gameLevels = [
            // LEVEL 1:
            1 => [
                'id' => 1,
                'layout_type' => 'type_2p_3c_fixed', 
                'level_title' => 'Gia đình của Lan (Dễ)',
                'fixed_chars' => ['parent1' => ['id' => 'lan', 'name' => 'Lan']], 
                'available_characters' => ['Hùng', 'Chi', 'An', 'Bình'],
                'clues' => [
                    'Lan là vợ của Hùng.',
                    'Chi là chị cả trong nhà.',
                    'Bình là em út.'
                ],
                'solution' => [
                    'parent2' => 'Hùng', // Bố
                    'child1' => 'Chi',   // Con cả
                    'child2' => 'An',    // Con giữa
                    'child3' => 'Bình'   // Con út
                ]
            ],

            // LEVEL 2:
            2 => [
                'id' => 2,
                'layout_type' => 'type_2p_2c', 
                'level_title' => 'Gia đình của Tuấn & Mai (Trung bình)',
                'fixed_chars' => [],
                'available_characters' => ['Tuấn', 'Mai', 'Tí', 'Tèo'],
                'clues' => [
                    'Tuấn kết hôn với Mai.',
                    'Tí là anh của Tèo.'
                ],
                'solution' => [
                    'parent1' => 'Tuấn',
                    'parent2' => 'Mai',
                    'child1' => 'Tí',
                    'child2' => 'Tèo'
                ]
            ],

            // LEVEL 3:
            3 => [
                'id' => 3,
                'layout_type' => 'type_vertical_3gen', 
                'level_title' => 'Gia đình 3 thế hệ (Khá)',
                'fixed_chars' => [],
                'available_characters' => ['Ba', 'Nam', 'Bi'],
                'clues' => [
                    'Bi là cháu nội của Ba.',
                    'Nam là ba của Bi.'
                ],
                'solution' => [
                    'gen1' => 'Ba',  // Ông
                    'gen2' => 'Nam', // Bố
                    'gen3' => 'Bi'   // Cháu
                ]
            ],

            // LEVEL 4:
            4 => [
                'id' => 4,
                'layout_type' => 'type_2p_3c_fixed_dad',
                'level_title' => 'Gia đình của Bảo (Khá)',
                'fixed_chars' => ['parent1' => ['id' => 'Bảo', 'name' => 'Bảo']],
                'available_characters' => ['Nga', 'Minh', 'Cúc', 'Hải'],
                'clues' => [
                    'Nga là mẹ của 3 đứa trẻ.',
                    'Hải có 1 anh trai và 1 chị gái (Hải là út).',
                    'Cúc không phải con cả.'
                ],
                'solution' => [
                    'parent2' => 'Nga',  // Mẹ
                    'child1' => 'Minh',  // Cả
                    'child2' => 'Cúc',   // Giữa
                    'child3' => 'Hải'    // Út
                ]
            ],

            // LEVEL 5:
            5 => [
                'id' => 5,
                'layout_type' => 'type_3gen_complex',
                'level_title' => 'Gia đình Đạt & Hoàng (Nâng cao)',
                'fixed_chars' => [],
                'available_characters' => ['Đạt', 'Hoàng', 'Linh', 'Dũng', 'Thảo', 'Anh'],
                'clues' => [
                    'Đạt và Hoàng có hai người con là Linh và Dũng.',
                    'Linh là chị của Dũng.',
                    'Đạt là ông nội của Anh.'
                ],
                'solution' => [
                    'gen1_p1' => 'Đạt',   // Ông
                    'gen1_p2' => 'Hoàng', // Bà
                    'gen2_c1' => 'Linh',  // Con (Bác)
                    'gen2_c2' => 'Dũng',  // Con (Bố)
                    'gen2_spouse' => 'Thảo',// Mẹ
                    'gen3_c1' => 'Anh'   // Cháu
                ]
            ]
        ];

        $currentLevelId = isset($_GET['level']) ? (int)$_GET['level'] : 1;
        $currentLevel = $gameLevels[$currentLevelId] ?? $gameLevels[1];
        $totalLevels = count($gameLevels);

        require_once __DIR__ . '/../views/lessons/technology_family_tree_game.php';
    }

    /**
     * TRÒ CHƠI SƠN TINH - THỦY TINH
     */
    public function showCodingGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        
        // Map codes: 0=Đất, 1=Núi(Chặn), 2=Sơn Tinh, 3=Đích(Sính lễ), 4=Nước, 5=Cầu(Sau khi xây)
        $levels = [
            1 => [
                'id' => 1,
                'title' => 'Khu rừng rậm rạp',
                'mission' => 'Tìm Voi chín ngà',
                'target_img' => 'voi9nga.png',
                'hint' => 'Sử dụng các lệnh Đi thẳng và Rẽ để vượt qua mê cung.',
                'concepts' => ['sequence'], // Tuần tự
                'map' => [
                    [1, 1, 1, 1, 1],
                    [1, 0, 0, 3, 1],
                    [1, 0, 1, 1, 1],
                    [1, 2, 0, 0, 1],
                    [1, 1, 1, 1, 1]
                ],
                'limit' => 10,
                'time' => 60 
            ],
            2 => [
                'id' => 2,
                'title' => 'Vách núi cheo leo',
                'mission' => 'Tìm Gà chín cựa',
                'target_img' => 'ga9cua.png',
                'hint' => 'Đường đi lặp lại giống nhau. Hãy dùng khối [Lặp lại] để leo núi nhanh hơn!',
                'concepts' => ['loop'], 
                'map' => [
                    [1, 1, 1, 3, 1],
                    [1, 1, 0, 0, 1],
                    [1, 0, 0, 1, 1],
                    [2, 0, 1, 1, 1],
                    [1, 1, 1, 1, 1]
                ],
                'limit' => 5,
                'time' => 60
            ],
            3 => [
                'id' => 3,
                'title' => 'Đồng cỏ ngập nước',
                'mission' => 'Tìm Ngựa chín hồng mao',
                'target_img' => 'ngua9hongmao.png',
                'hint' => 'Nước lũ dâng cao! Dùng khối [Nếu gặp nước] để bắc cầu.',
                'concepts' => ['condition'], 
                'map' => [
                    [1, 1, 1, 1, 1],
                    [1, 3, 4, 0, 0],
                    [1, 1, 1, 1, 4],
                    [1, 2, 0, 4, 0],
                    [1, 1, 1, 1, 1]
                ],
                'limit' => 12,
                'time' => 70
            ]
        ];

        $currentLevelId = isset($_GET['level']) ? (int)$_GET['level'] : 1;
        $currentLevel = $levels[$currentLevelId] ?? $levels[1];
        $totalLevels = count($levels);

        require_once __DIR__ . '/../views/lessons/technology_coding_game.php';
    }

    /**
     * game named 'Trò chơi Lập trình'
     */
    public function updateCodingScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Trò chơi Lập trình']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Lập trình%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Trò chơi Lập trình" not registered']);
                return;
            }

            $pct = 100;
            $xpAwarded = 20;
            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    /**
     * TRÒ CHƠI CÁC BỘ PHẬN MÁY TÍNH
     */
    public function showComputerPartsGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        $computerParts = [
            ['id' => 'monitor', 'name' => 'Màn hình', 'img' => 'monitor.png'],
            ['id' => 'case', 'name' => 'Thùng máy', 'img' => 'case.png'],
            ['id' => 'keyboard', 'name' => 'Bàn phím', 'img' => 'keyboard.png'],
            ['id' => 'mouse', 'name' => 'Chuột', 'img' => 'mouse.png'],
            ['id' => 'printer', 'name' => 'Máy in', 'img' => 'printer.png'],
            ['id' => 'speaker', 'name' => 'Loa', 'img' => 'speaker.png'],
            ['id' => 'microphone', 'name' => 'Micrô', 'img' => 'microphone.png']
        ];
        
        shuffle($computerParts); 

        require_once __DIR__ . '/../views/lessons/technology_computer_parts.php';
    }

    /**
     * GAME ĐÁNH MÁY THẠCH SANH
     */
    public function showThachSanhGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $base_url = str_replace('\\', '/', $base_url);

        $gameData = [
            'easy' => [ // Hàng phím cơ sở
                'A', 'S', 'D', 'F', 'J', 'K', 'L', 
                'A', 'S', 'D', 'F', 'J', 'K', 'L'
            ],
            'hard' => [ // Từ đơn không dấu
                'GA', 'CA', 'BA', 'DA', 'LA', 'MA', 'NA', 
                'CO', 'BO', 'HO', 'TO', 'LO', 
                'VE', 'XE', 'BE', 'HE',
                'VOI', 'CUA', 'MEO', 'CHO'
            ]
        ];

        $level = $_GET['level'] ?? 'easy';
        $wordList = $gameData[$level];

        require_once __DIR__ . '/../views/lessons/technology_typing_thach_sanh.php';
    }

    /**
     * TRÒ CHƠI HỌA SĨ MÁY TÍNH
     */
    public function showPainterGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        // Lấy chủ đề từ URL, mặc định là 'free' (Tự vẽ)
        $topic = $_GET['topic'] ?? 'free';
        
        // Cấu hình các chủ đề
        $topicConfig = [
            'free' => [
                'title' => 'Tự do sáng tạo', 
                'bg_image' => '', // Không có nền
                'icon' => 'icon_free.png'
            ],
            'house' => [
                'title' => 'Ngôi nhà mơ ước', 
                'bg_image' => 'bg_house.png', // Ảnh ngôi nhà
                'icon' => 'icon_house.png'
            ],
            'animal' => [
                'title' => 'Thế giới động vật', 
                'bg_image' => 'bg_animal.png', // Ảnh con vật
                'icon' => 'icon_animal.png'
            ],
            'computer' => [
                'title' => 'Máy tính của em', 
                'bg_image' => 'bg_computer.png', // Ảnh máy tính
                'icon' => 'icon_computer.png'
            ],
            'nature' => [
                'title' => 'Thiên nhiên tươi đẹp', 
                'bg_image' => 'bg_nature.png', // Ảnh cây cối
                'icon' => 'icon_nature.png'
            ]
        ];

        $currentConfig = $topicConfig[$topic] ?? $topicConfig['free'];
        $timeLimit = 300; 

        require_once __DIR__ . '/../views/lessons/technology_painter_game.php';
    }

    /*TRÒ CHƠI CƠ CHẾ HOA*/
    public function showFlowerMechanismGame() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        
        if (!isset($_SESSION['flower_score'])) {
            $_SESSION['flower_score'] = 0;
        }

        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        // Dữ liệu game 
        $flowerParts = [
            ['id' => 'petal', 'name' => 'Cánh hoa'],
            ['id' => 'stamen', 'name' => 'Nhị hoa'],
            ['id' => 'pistil', 'name' => 'Nhuỵ hoa'],
            ['id' => 'sepal', 'name' => 'Đài hoa'],
            ['id' => 'stem', 'name' => 'Thân'],
        ];

        shuffle($flowerParts);

        require_once __DIR__ . '/../views/lessons/engineering_flower_mechanism.php';
    }

  
    public function updateFlowerScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $correct = isset($data['correct']) ? (bool)$data['correct'] : false;
        if (!$correct) {
            echo json_encode(['success' => false, 'message' => 'Prediction incorrect; not saved']);
            return;
        }

        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;
        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $preferred = ['Hoa yêu thương nở rộ', 'Hoa yêu thương'];
                foreach ($preferred as $nm) {
                    $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                    $pstmt->execute([':name' => $nm]);
                    $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                    if ($pr) { $gameId = (int)$pr['id']; break; }
                }
                if (empty($gameId)) {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Hoa%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
                if (empty($gameId)) {
                    $stmt = $db->prepare('SELECT id FROM games WHERE topic_id = :tid LIMIT 1');
                    $stmt->execute([':tid' => 4]);
                    $r = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($r) $gameId = (int)$r['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Could not resolve game id for flower experiment']);
                return;
            }

            $pct = 100;
            $res = Score::saveAndMark((int)$userId, $gameId, $pct);
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    
    public function updateFamilyTreeScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();
            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Cây gia đình']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Cây gia đình%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }
            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Cây gia đình" not registered in database']);
                return;
            }

            $pct = 100;
            $xpAwarded = 20;
            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

   
    public function updateComputerPartsScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();
            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Các bộ phận của máy tính']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%bộ phận máy tính%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }
            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Các bộ phận của máy tính" not registered']);
                return;
            }

            $pct = 100;
            $xpAwarded = 20;
            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    
    public function updateThachSanhScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit' || !isset($data['score_pct'])) {
            echo json_encode(['success' => false, 'message' => 'Unsupported action or missing score_pct']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $scorePct = (int)$data['score_pct'];
        $gameName = isset($data['game_name']) ? trim($data['game_name']) : null;
        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();
            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Em là người đánh máy']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    if (!empty($gameName)) {
                        $p2 = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                        $p2->execute([':name' => $gameName]);
                        $pr2 = $p2->fetch(PDO::FETCH_ASSOC);
                        if ($pr2) $gameId = (int)$pr2['id'];
                    }
                    if (empty($gameId)) {
                        $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                        $lstmt->execute([':like' => '%đánh máy%']);
                        $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                        if ($lr) $gameId = (int)$lr['id'];
                    }
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Em là người đánh máy" not registered']);
                return;
            }


            $pct = max(0, min(100, $scorePct));
            $xpAwarded = 20;
            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    
    public function resetThachSanhCommit() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');
        try {
            if (!empty($_SESSION['thach_sanh_committed'])) {
                unset($_SESSION['thach_sanh_committed']);
            }
            echo json_encode(['success' => true, 'message' => 'reset']);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    
    public function updatePainterScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();
            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Em làm họa sĩ máy tính']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%họa sĩ%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }
            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Em làm họa sĩ máy tính" not registered']);
                return;
            }

            $pct = 100;
            $xpAwarded = 20;
            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    public function showBridgeGame() {
        require_once 'views/lessons/engineering_bridge_game.php';
    }

    /**
     * TRÒ CHƠI CHẾ TẠO XE
     */
    public function showCarBuilderGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        $levels = [
            1 => [
                'id' => 1,
                'title' => 'Đường Đua Tốc Độ',
                'desc' => 'Đường bằng phẳng và dài. Hãy chế tạo chiếc xe có Tốc độ cao nhất!',
                'bg' => 'bg_track.jpg',
                'req_speed' => 80,    // Yêu cầu Tốc độ
                'req_power' => 20,    // Yêu cầu Sức mạnh (Leo dốc)
                'req_grip' => 20      // Yêu cầu Độ bám (Đường trơn)
            ],
            2 => [
                'id' => 2,
                'title' => 'Vượt Đèo Dốc',
                'desc' => 'Ngọn núi rất cao! Xe cần Động cơ mạnh và Bánh lớn để leo dốc.',
                'bg' => 'bg_hill.jpg',
                'req_speed' => 30,
                'req_power' => 70,    
                'req_grip' => 40
            ],
            3 => [
                'id' => 3,
                'title' => 'Đầm Lầy Trơn Trượt',
                'desc' => 'Đường rất trơn. Nếu không có Độ bám tốt, xe sẽ bị trượt!',
                'bg' => 'bg_mud.jpg',
                'req_speed' => 40,
                'req_power' => 40,
                'req_grip' => 80      
            ]
        ];

        // Dữ liệu các bộ phận xe
        $parts = [
            'body' => [
                ['id' => 'sport', 'name' => 'Xe Đua', 'img' => 'body_sport.png', 'speed' => 40, 'power' => 10, 'grip' => 10],
                ['id' => 'truck', 'name' => 'Xe Tải', 'img' => 'body_truck.png', 'speed' => 10, 'power' => 40, 'grip' => 20],
                ['id' => 'buggy', 'name' => 'Xe Địa Hình', 'img' => 'body_buggy.png', 'speed' => 25, 'power' => 25, 'grip' => 25],
            ],
            'engine' => [
                ['id' => 'v4', 'name' => 'Động cơ V4', 'img' => 'engine_v4.png', 'speed' => 20, 'power' => 10, 'grip' => 0],
                ['id' => 'v8', 'name' => 'Động cơ V8', 'img' => 'engine_v8.png', 'speed' => 40, 'power' => 30, 'grip' => 0],
                ['id' => 'electric', 'name' => 'Động cơ Điện', 'img' => 'engine_electric.png', 'speed' => 30, 'power' => 20, 'grip' => 0],
            ],
            'wheel' => [
                ['id' => 'small', 'name' => 'Bánh Nhỏ', 'img' => 'wheel_small.png', 'speed' => 20, 'power' => 0, 'grip' => 10],
                ['id' => 'big', 'name' => 'Bánh Lớn', 'img' => 'wheel_big.png', 'speed' => 10, 'power' => 20, 'grip' => 30],
                ['id' => 'chain', 'name' => 'Bánh Xích', 'img' => 'wheel_chains.png', 'speed' => 5, 'power' => 30, 'grip' => 50],
            ],
            'addon' => [
                ['id' => 'none', 'name' => 'Không', 'img' => '', 'speed' => 0, 'power' => 0, 'grip' => 0],
                ['id' => 'spoiler', 'name' => 'Cánh Gió', 'img' => 'spoiler.png', 'speed' => 10, 'power' => 0, 'grip' => 20],
                ['id' => 'booster', 'name' => 'Tên Lửa', 'img' => 'booster.png', 'speed' => 30, 'power' => 10, 'grip' => -10], // Tăng tốc nhưng giảm bám
            ]
        ];

        $currentLevelId = isset($_GET['level']) ? (int)$_GET['level'] : 1;
        $currentLevel = $levels[$currentLevelId] ?? $levels[1];
        $totalLevels = count($levels);

        require_once __DIR__ . '/../views/lessons/engineering_car_builder.php';
    }

    /**
     * HẬU NGHỆ BẮN MẶT TRỜI
     */
    public function showMathAngleGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        // Dữ liệu các màn chơi
        $levels = [
            1 => [
                'id' => 1,
                'title' => 'Bình Minh (Góc Nhọn)',
                'desc' => 'Mặt trời vừa mọc ở phía Đông. Hãy bắn hạ nó! Góc bắn nhỏ hơn 90°.',
                'sun_pos' => ['x' => 0.8, 'y' => 0.4],
                'type' => 'acute'
            ],
            2 => [
                'id' => 2,
                'title' => 'Giữa Trưa (Góc Vuông)',
                'desc' => 'Mặt trời đang đứng bóng. Góc bắn là 90°.',
                'sun_pos' => ['x' => 0.5, 'y' => 0.15],
                'type' => 'right'
            ],
            3 => [
                'id' => 3,
                'title' => 'Hoàng Hôn (Góc Tù)',
                'desc' => 'Mặt trời lặn về phía Tây. Hãy bắn vòng qua núi! Góc bắn lớn hơn 90°.',
                'sun_pos' => ['x' => 0.2, 'y' => 0.4],
                'type' => 'obtuse'
            ]
        ];

        $currentLevelId = isset($_GET['level']) ? (int)$_GET['level'] : 1;
        $currentLevel = $levels[$currentLevelId] ?? $levels[1];
        $totalLevels = count($levels);

        require_once __DIR__ . '/../views/lessons/math_angle_game.php';
    }

    
    public function updateAngleGameScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Trò chơi Góc và đo lường']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Góc%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Trò chơi Góc và đo lường" not registered']);
                return;
            }

            $pct = 100;
            $xpAwarded = 20;
            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    /**
     * Game name: 'Trò chơi Số học'
     */
    public function updateNumberScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $scorePct = isset($data['score_pct']) ? (int)$data['score_pct'] : null;
        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Trò chơi Số học']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Số%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Trò chơi Số học" not registered']);
                return;
            }

            $pct = 0;
            if ($scorePct !== null) {
                $pct = max(0, min(100, $scorePct));
            } else {
                $raw = isset($_SESSION['number_score']) ? (int)$_SESSION['number_score'] : 0;
                $maxPoints = 20 * 10;
                if ($maxPoints > 0) {
                    $pct = (int)round(($raw / $maxPoints) * 100);
                    if ($pct > 100) $pct = 100;
                    if ($pct < 0) $pct = 0;
                }
            }

            $xpAwarded = 20;
            
            if ($scorePct !== null) {
                $correctCount = (int)round(($pct / 100) * 20);
                $xpAwarded = max(0, min(20, $correctCount));
            }
            if (isset($data['xp'])) $xpAwarded = (int)$data['xp'];

            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }

            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    /**
     * TRÒ CHƠI TANGRAM
     */
    public function showTangramGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['math_score'])) { $_SESSION['math_score'] = 0; }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        $levels = [
            1 => [
                'id' => 1,
                'title' => 'Màn 1: Chú Mèo Đang Ngồi',
                'desc' => 'Hãy sắp xếp 7 mảnh ghép để tạo thành hình chú mèo.',
                'silhouetteShape' => 'cat', 
                
                'solution' => [
                    // 1. Đầu mèo (Hình vuông)
                    'square'        => ['x' => 273, 'y' => -99,  'rot' => 0],

                    // 2. Tai trái (Tam giác nhỏ 1)
                    'small1'        => ['x' => 306, 'y' => -160, 'rot' => 2],

                    // 3. Tai phải (Tam giác nhỏ 2)
                    'small2'        => ['x' => 241, 'y' => -161, 'rot' => 6],

                    // 4. Thân trên (Tam giác lớn 2)
                    'big2'          => ['x' => 212, 'y' => 82,   'rot' => 2],

                    // 5. Lưng/Cổ (Tam giác vừa)
                    'medium'        => ['x' => 359, 'y' => 49,   'rot' => 2],

                    // 6. Thân dưới (Tam giác lớn 1)
                    'big1'          => ['x' => 195, 'y' => 213,  'rot' => 1],

                    // 7. Đuôi (Hình bình hành)
                    'parallelogram' => ['x' => 60,  'y' => 225,  'rot' => 0]
                ]
            ],
            2 => [
                'id' => 2,
                'title' => 'Màn 2: Chú Ngựa Phi',
                'desc' => 'Sắp xếp các mảnh ghép để tạo hình chú ngựa đang phi nước đại.',
                'silhouetteShape' => 'horse', 
                'solution' => [
                    // 1. Đầu ngựa (Tam giác vừa)
                    'medium'        => ['x' => 312, 'y' => -240, 'rot' => 0],

                    // 2. Cổ ngựa (Hình vuông)
                    'square'        => ['x' => 270, 'y' => -109, 'rot' => 1],

                    // 3. Thân trên (Tam giác lớn 2)
                    'big2'          => ['x' => 271, 'y' => -24,  'rot' => 3],

                    // 4. Thân dưới (Tam giác lớn 1)
                    'big1'          => ['x' => 166, 'y' => 56,   'rot' => 2],

                    // 5. Đuôi (Hình bình hành)
                    'parallelogram' => ['x' => 72,  'y' => 150,  'rot' => 2],

                    // 6. Chân trước (Tam giác nhỏ 1)
                    'small1'        => ['x' => 397, 'y' => -33,  'rot' => 4],

                    // 7. Chân sau (Tam giác nhỏ 2)
                    'small2'        => ['x' => 252, 'y' => 168,  'rot' => 1],
                ]
            ],
            3 => [
                'id' => 3,
                'title' => 'Màn 3: Chú Thỏ Đáng Yêu',
                'desc' => 'Hãy ghép các khối hình để tạo thành chú thỏ.',
                'silhouetteShape' => 'rabbit', 
                'solution' => [
                    // 1. Tai thỏ (Hình bình hành) - Nằm cao nhất
                    'parallelogram' => ['x' => 228, 'y' => -196, 'rot' => 3],

                    // 2. Đầu thỏ (Hình vuông)
                    'square'        => ['x' => 148, 'y' => -109, 'rot' => 1],

                    // 3. Thân trên (Tam giác lớn 2)
                    'big2'          => ['x' => 234, 'y' => -24,  'rot' => 1],

                    // 4. Chân trước (Tam giác nhỏ 1)
                    'small1'        => ['x' => 158, 'y' => 20,   'rot' => 2],

                    // 5. Thân dưới (Tam giác lớn 1)
                    'big1'          => ['x' => 319, 'y' => 63,   'rot' => 5],

                    // 6. Đuôi (Tam giác nhỏ 2)
                    'small2'        => ['x' => 207, 'y' => 134,  'rot' => 2],

                    // 7. Chân sau (Tam giác vừa) - Nằm thấp nhất
                    'medium'        => ['x' => 241, 'y' => 192,  'rot' => 5],
                ]
            ],
            4 => [
                'id' => 4,
                'title' => 'Màn 4: Chú Cá Vàng',
                'desc' => 'Sắp xếp các mảnh ghép để tạo thành hình chú cá đang bơi.',
                'silhouetteShape' => 'fish', 
                'solution' => [
                    // 1. Thân trước (Hình vuông) - Phần đầu
                    'square'        => ['x' => 221, 'y' => 1,    'rot' => 1],

                    // 2. Thân trên (Tam giác lớn 1) - Lưng cá
                    'big1'          => ['x' => 134, 'y' => -39,  'rot' => 7],

                    // 3. Thân dưới (Tam giác lớn 2) - Bụng cá
                    'big2'          => ['x' => 133, 'y' => 49,   'rot' => 5],

                    // 4. Vây lưng (Tam giác nhỏ 1)
                    'small1'        => ['x' => 201, 'y' => -66,  'rot' => 1],

                    // 5. Vây bụng (Tam giác nhỏ 2)
                    'small2'        => ['x' => 199, 'y' => 68,   'rot' => 3],

                    // 6. Đuôi phần gốc (Tam giác vừa)
                    'medium'        => ['x' => 267, 'y' => -45,  'rot' => 6],

                    // 7. Đuôi phần ngọn (Hình bình hành)
                    'parallelogram' => ['x' => 308, 'y' => 43,   'rot' => 1],
                ]
            ],
            5 => [
                'id' => 5,
                'title' => 'Màn 5: Cánh Bướm Xinh',
                'desc' => 'Hãy ghép hình để tạo thành chú bướm đang bay.',
                'silhouetteShape' => 'butterfly', 
                'solution' => [
                    // 1. Cánh lớn bên trái (Tam giác lớn 1)
                    'big1'          => ['x' => 79,  'y' => -89, 'rot' => 1],

                    // 2. Cánh lớn bên phải (Tam giác lớn 2)
                    'big2'          => ['x' => 342, 'y' => -90, 'rot' => 7],

                    // 3. Đầu/Thân trên (Hình vuông)
                    'square'        => ['x' => 165, 'y' => -1,  'rot' => 1],

                    // 4. Cánh nhỏ dưới trái (Tam giác nhỏ 2)
                    'small2'        => ['x' => 98,  'y' => -22, 'rot' => 5],

                    // 5. Cánh nhỏ dưới phải (Tam giác nhỏ 1)
                    'small1'        => ['x' => 145, 'y' => 66,  'rot' => 3],

                    // 6. Thân dưới (Hình bình hành)
                    'parallelogram' => ['x' => 255, 'y' => 43,  'rot' => 1],

                    // 7. Đuôi (Tam giác vừa)
                    'medium'        => ['x' => 302, 'y' => 42,  'rot' => 4],
                ]
            ]
        ];

        $currentLevelId = isset($_GET['level']) ? (int)$_GET['level'] : 1;
        $currentLevel = $levels[$currentLevelId] ?? $levels[1];
        $totalLevels = count($levels);

        require_once __DIR__ . '/../views/lessons/math_tangram.php';
    }

    /**
     * Game name in DB: 'Tangram'
     */
    public function updateTangramScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $scorePct = isset($data['score_pct']) ? (int)$data['score_pct'] : 0;
        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Tangram']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Tangram%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Tangram" not registered']);
                return;
            }

            $pct = max(0, min(100, $scorePct));
            $xpAwarded = 20;

            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    
    public function updateTowerScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $scorePct = isset($data['score_pct']) ? (int)$data['score_pct'] : 0;
        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Trò chơi Tháp']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Tháp%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Trò chơi Tháp" not registered']);
                return;
            }

            $pct = max(0, min(100, $scorePct));
            // default xp for tower
            $xpAwarded = 20;

            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    /**
     * TRÒ CHƠI LỌC NƯỚC
     */
    public function showWaterFilterGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $base_url = str_replace('\\', '/', $base_url);

        $gameData = [
            'title' => 'Máy Lọc Nước Mini',
            'desc' => 'Hãy sắp xếp các lớp vật liệu để lọc nước bẩn thành nước sạch nhé!',
            'materials' => [
                ['id' => 'gravel', 'name' => 'Sỏi', 'img' => 'gravel.png', 'desc' => 'Lọc rác lớn'],
                ['id' => 'sand', 'name' => 'Cát', 'img' => 'sand.png', 'desc' => 'Lọc bụi nhỏ'],
                ['id' => 'charcoal', 'name' => 'Than', 'img' => 'charcoal.png', 'desc' => 'Khử mùi & độc'],
                ['id' => 'cotton', 'name' => 'Bông', 'img' => 'cotton.png', 'desc' => 'Lọc cặn cuối cùng']
            ],
            'correct_order' => ['cotton', 'charcoal', 'sand', 'gravel']
        ];

        require_once __DIR__ . '/../views/lessons/engineering_water_filter.php';
    }

    public function updateWaterFilterScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $scorePct = isset($data['score_pct']) ? (int)$data['score_pct'] : 100;
        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                // Prefer exact name 'Bộ lọc nước'
                $stmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $stmt->execute([':name' => 'Bộ lọc nước']);
                $r = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($r) {
                    $gameId = (int)$r['id'];
                } else {
                    $stmt2 = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $stmt2->execute([':like' => '%lọc nước%']);
                    $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                    if ($r2) $gameId = (int)$r2['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Bộ lọc nước" not registered']);
                return;
            }

            $pct = max(0, min(100, $scorePct));

            $xpAwarded = 20;
            if (isset($data['xp'])) {
                $xpAwarded = (int)$data['xp'];
            } else {
                try {
                    $gstmt = $db->prepare('SELECT xp FROM games WHERE id = :gid LIMIT 1');
                    $gstmt->execute([':gid' => $gameId]);
                    $grow = $gstmt->fetch(PDO::FETCH_ASSOC);
                    if ($grow && isset($grow['xp'])) $xpAwarded = (int)$grow['xp'];
                } catch (Exception $e) {
                }
            }

            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }

            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    /**
     * Bây giờ là mấy giờ
     */
    public function showTimeGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $base_url = str_replace('\\', '/', $base_url);

        $levels = [
            1 => [
                'id' => 1,
                'title' => 'Cấp độ 1: Giờ Chẵn',
                'desc' => 'Kéo kim giờ và kim phút đúng vị trí nhé!',
                'questions' => [
                    ['h' => 3, 'm' => 0],
                    ['h' => 9, 'm' => 0],
                    ['h' => 12, 'm' => 0],
                    ['h' => 6, 'm' => 0],
                    ['h' => 1, 'm' => 0]
                ]
            ],
            2 => [
                'id' => 2,
                'title' => 'Cấp độ 2: Giờ Rưỡi',
                'desc' => 'Lưu ý kim giờ sẽ nằm giữa 2 số khi ở 30 phút đấy!',
                'questions' => [
                    ['h' => 2, 'm' => 30],
                    ['h' => 8, 'm' => 30],
                    ['h' => 10, 'm' => 30],
                    ['h' => 5, 'm' => 30]
                ]
            ],
            3 => [
                'id' => 3,
                'title' => 'Cấp độ 3: Phút Lẻ',
                'desc' => 'Thử thách khó hơn với các phút bất kỳ.',
                'questions' => [
                    ['h' => 4, 'm' => 15],
                    ['h' => 7, 'm' => 45],
                    ['h' => 11, 'm' => 20],
                    ['h' => 6, 'm' => 10]
                ]
            ]
        ];

        $currentLevelId = isset($_GET['level']) ? (int)$_GET['level'] : 1;
        $currentLevel = $levels[$currentLevelId] ?? $levels[1];
        $totalLevels = count($levels);

        require_once __DIR__ . '/../views/lessons/math_time_game.php';
    }

    /**
     * Game name: 'Trò chơi Thời gian'
     */
    public function updateTimeScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $scorePct = isset($data['score_pct']) ? (int)$data['score_pct'] : 0;
        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Trò chơi Thời gian']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Thời gian%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Trò chơi Thời gian" not registered']);
                return;
            }

            $pct = max(0, min(100, $scorePct));
            $xpAwarded = 20;

            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    /**
     * XÂY THÁP
     */
    public function showTowerGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $base_url = str_replace('\\', '/', $base_url);

        $levels = [
            1 => [
                'id' => 1,
                'title' => 'Màn 1: Tháp Vươn Cao',
                'desc' => 'Xây tháp cao để chạm vào mục tiêu tròn duy nhất.',
                'config' => [
                    'targets' => [ 
                        ['x' => '50%', 'y' => '40%'] 
                    ],
                    'anchors' => [ 
                        ['x' => '44%', 'y' => '96%'],
                        ['x' => '56%', 'y' => '96%']
                    ],
                    'freeNodes' => 10,
                    'connectDistance' => 130
                ]
            ],
            2 => [
                'id' => 2,
                'title' => 'Màn 2: Cầu Treo Thách Thức',
                'desc' => 'Xây dựng kết cấu chia làm 2 nhánh để chạm cả 2 mục tiêu cùng lúc.',
                'config' => [
                    'targets' => [ 
                        ['x' => '30%', 'y' => '45%'], // Trái
                        ['x' => '70%', 'y' => '45%']  // Phải
                    ], 
                    'anchors' => [ 
                        ['x' => '44%', 'y' => '96%'],
                        ['x' => '56%', 'y' => '96%']
                    ],
                    'freeNodes' => 25,
                    'connectDistance' => 130
                ]
            ],
            3 => [
                'id' => 3,
                'title' => 'Màn 3: Thử Thách Bất Đối Xứng',
                'desc' => 'Một mục tiêu cao, một mục tiêu thấp.',
                'config' => [
                    'targets' => [ 
                        ['x' => '30%', 'y' => '55%'], // Trái thấp
                        ['x' => '60%', 'y' => '30%']  // Phải cao
                    ], 
                    'anchors' => [ 
                        ['x' => '44%', 'y' => '96%'],
                        ['x' => '56%', 'y' => '96%']
                    ],
                    'freeNodes' => 25,
                    'connectDistance' => 130
                ]
            ]
        ];
        
        $currentId = isset($_GET['level']) ? (int)$_GET['level'] : 1;
        $currentLevel = $levels[$currentId] ?? $levels[1];
        $totalLevels = count($levels);

        require_once __DIR__ . '/../views/lessons/engineering_tower_game.php';
    }

    /**
     * TRANG TRÍ PHÒNG
     */
    public function showRoomDecorGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $base_url = str_replace('\\', '/', $base_url);

        $gameData = [
            'title' => 'Kiến Trúc Sư Nhí: Thiết Kế Phòng Ngủ',
            
            'categories' => [
                'room_type' => [
                    'label' => 'Chọn Phòng',
                    'icon' => 'fa-home', 
                    'items' => [
                        ['id' => 'room_1', 'name' => 'Phòng Rừng Xanh', 'type' => 'room', 'img' => 'room_1.png'],
                        ['id' => 'room_2', 'name' => 'Phòng Mộng Mơ', 'type' => 'room', 'img' => 'room_2.png'],
                        ['id' => 'room_3', 'name' => 'Phòng Dơi', 'type' => 'room', 'img' => 'room_3.png'],
                        ['id' => 'room_4', 'name' => 'Phòng Cơ Bản', 'type' => 'room', 'img' => 'room_4.png'],
                    ]
                ],
                'bed' => [
                    'label' => 'Giường',
                    'icon' => 'fa-bed',
                    'items' => [
                        ['id' => 'bed_1', 'img' => 'bed_1.png', 'w' => 200, 'name' => 'Giường Gỗ'],
                        ['id' => 'bed_2', 'img' => 'bed_2.png', 'w' => 200, 'name' => 'Giường Hồng'],
                        
                    ]
                ],
                'storage' => [
                    'label' => 'Tủ & Kệ',
                    'icon' => 'fa-door-closed',
                    'items' => [
                        ['id' => 'wardrobe_1', 'img' => 'wardrobe_1.png', 'w' => 125, 'name' => 'Tủ Áo'],
                        ['id' => 'bookshelf_1', 'img' => 'bookshelf_1.png', 'w' => 80, 'name' => 'Giá Sách'],
                        ['id' => 'cabinet_1', 'img' => 'cabinet_1.png', 'w' => 140, 'name' => 'Tủ Nhỏ'],
                        ['id' => 'cabinet_2', 'img' => 'cabinet_2.png', 'w' => 140, 'name' => 'Tủ Nhỏ'],
                        ['id' => 'cabinet_3', 'img' => 'cabinet_3.png', 'w' => 140, 'name' => 'Tủ Nhỏ'],
                    ]
                ],
                'study' => [
                    'label' => 'Bàn & Ghế',
                    'icon' => 'fa-book-reader',
                    'items' => [
                        ['id' => 'desk_1', 'img' => 'desk_1.png', 'w' => 140, 'name' => 'Bàn Học'],
                        ['id' => 'chair_1', 'img' => 'chair_1.png', 'w' => 60, 'name' => 'Ghế Xoay'],
                        ['id' => 'chair_2', 'img' => 'chair_2.png', 'w' => 60, 'name' => 'Ghế Gỗ'],
                        ['id' => 'chair_3', 'img' => 'chair_3.png', 'w' => 80, 'name' => 'Ghế Gỗ'],
                        ['id' => 'chair_4', 'img' => 'chair_4.png', 'w' => 140, 'name' => 'Ghế Gỗ'],
                        ['id' => 'chair_5', 'img' => 'chair_5.png', 'w' => 60, 'name' => 'Ghế Gỗ'],
                        ['id' => 'chair_6', 'img' => 'chair_6.png', 'w' => 60, 'name' => 'Ghế Gỗ'],
                    ]
                ],
                'rug' => [
                    'label' => 'Thảm Sàn',
                    'icon' => 'fa-rug', 
                    'items' => [
                        ['id' => 'rug_1', 'img' => 'rug_1.png', 'w' => 240, 'name' => 'Thảm Tròn'],
                        ['id' => 'rug_2', 'img' => 'rug_2.png', 'w' => 240, 'name' => 'Thảm Vuông'],
                        ['id' => 'rug_3', 'img' => 'rug_3.png', 'w' => 160, 'name' => 'Thảm Lông'],
                    ]
                ],
                'decor' => [
                    'label' => 'Trang Trí',
                    'icon' => 'fa-shapes',
                    'items' => [
                        ['id' => 'window_1', 'img' => 'window_1.png', 'w' => 100, 'name' => 'Cửa Sổ'],
                        ['id' => 'poster_1', 'img' => 'poster_1.png', 'w' => 60, 'name' => 'Tranh'],
                        ['id' => 'clock_1', 'img' => 'clock_1.png', 'w' => 40, 'name' => 'Đồng Hồ'],
                        ['id' => 'clock_2', 'img' => 'clock_2.png', 'w' => 40, 'name' => 'Đồng Hồ'],
                    ]
                ],
                'misc' => [
                    'label' => 'Đồ Khác',
                    'icon' => 'fa-gamepad',
                    'items' => [
                        ['id' => 'plant_1', 'img' => 'plant_1.png', 'w' => 60, 'name' => 'Cây Cảnh'],
                        ['id' => 'plant_2', 'img' => 'plant_2.png', 'w' => 60, 'name' => 'Cây Cảnh'],
                        ['id' => 'plant_3', 'img' => 'plant_3.png', 'w' => 60, 'name' => 'Cây Cảnh'],
                        ['id' => 'lamp_1', 'img' => 'lamp_1.png', 'w' => 50, 'name' => 'Đèn'],
                        ['id' => 'lamp_2', 'img' => 'lamp_2.png', 'w' => 50, 'name' => 'Đèn'],
                        ['id' => 'toy_1', 'img' => 'toy_1.png', 'w' => 50, 'name' => 'Đồ Chơi'],
                        ['id' => 'toy_2', 'img' => 'toy_2.png', 'w' => 50, 'name' => 'Đồ Chơi'],
                        ['id' => 'toy_3', 'img' => 'toy_3.png', 'w' => 50, 'name' => 'Đồ Chơi'],
                        ['id' => 'toy_4', 'img' => 'toy_4.png', 'w' => 50, 'name' => 'Đồ Chơi'],
                        ['id' => 'toy_5', 'img' => 'toy_5.png', 'w' => 50, 'name' => 'Đồ Chơi'],
                    ]
                ]
            ]
        ];

        require_once __DIR__ . '/../views/lessons/engineering_room_decor.php';
    }

    
    public function updateRoomDecorScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $scorePct = isset($data['score_pct']) ? (int)$data['score_pct'] : 100;
        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $pstmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $pstmt->execute([':name' => 'Trang trí phòng (Room Decor)']);
                $pr = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $gameId = (int)$pr['id'];
                } else {
                    $lstmt = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $lstmt->execute([':like' => '%Room Decor%']);
                    $lr = $lstmt->fetch(PDO::FETCH_ASSOC);
                    if ($lr) $gameId = (int)$lr['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Trang trí phòng (Room Decor)" not registered']);
                return;
            }

            $pct = max(0, min(100, $scorePct));
            $xpAwarded = 0;
            $gstmt = $db->prepare("SELECT xp FROM games WHERE id = :gid LIMIT 1");
            $gstmt->execute([':gid' => $gameId]);
            $gRow = $gstmt->fetch(PDO::FETCH_ASSOC);
            if ($gRow && isset($gRow['xp'])) $xpAwarded = (int)$gRow['xp'];

            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }
            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    public function showPipeGame() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $base_url = str_replace('\\', '/', $base_url);

        // Cấu hình các màn chơi
        // Grid: 0=Rỗng, S=Nguồn, E=Đích (Cây), I=Thẳng, L=Cong, T=Ba ngã, X=Bốn ngã
        $levels = [
            1 => [
                'id' => 1,
                'title' => 'Cấp độ 1: Làm quen',
                'desc' => 'Xoay các ống thẳng để dẫn nước tưới cây.',
                'grid_size' => 3, // 3x3
                // Map layout (Mảng 1 chiều, sẽ render thành lưới)
                // S: Nguồn, E: Cây, I: Ống thẳng
                'layout' => [
                    '0', '0', '0',
                    'S', 'I', 'E',
                    '0', '0', '0'
                ]
            ],
            2 => [
                'id' => 2,
                'title' => 'Cấp độ 2: Rẽ hướng',
                'desc' => 'Sử dụng ống cong để thay đổi dòng chảy.',
                'grid_size' => 4,
                'layout' => [
                    'S', 'L', '0', '0',
                    '0', 'I', 'L', 'E',
                    '0', 'L', 'L', '0', // Đường giả để đánh lạc hướng
                    '0', '0', '0', '0'
                ]
            ],
            3 => [
                'id' => 3,
                'title' => 'Cấp độ 3: Tránh rò rỉ',
                'desc' => 'Cẩn thận! Nếu đầu ống bị hở, nước sẽ tràn ra ngoài.',
                'grid_size' => 4,
                'layout' => [
                    '0', 'L', 'I', 'E',
                    'S', 'L', '0', '0',
                    '0', '0', '0', '0',
                    '0', '0', '0', '0'
                ]
            ],
            4 => [
                'id' => 4,
                'title' => 'Cấp độ 4: Đường dài',
                'desc' => 'Lập kế hoạch cho đường ống dài ngoằn ngoèo.',
                'grid_size' => 5,
                'layout' => [
                    'S', 'I', 'I', 'L', '0',
                    '0', '', '0', 'I', '0',
                    '0', 'L', 'I', 'L', '0',
                    '0', 'L', 'I', 'L', '0',
                    '0', '0', '0', 'L', 'E' 
                ]
            ],
            5 => [
                'id' => 5,
                'title' => 'Cấp độ 5: Khu vườn lớn',
                'desc' => 'Dùng ống chia nhánh (T) để tưới cho 2 cây cùng lúc.',
                'grid_size' => 5,
                'layout' => [
                    'S', 'I', 'T', 'I', 'E', // Cây 1
                    '0', '0', 'I', '0', '0',
                    '0', '0', 'L', 'I', 'E', // Cây 2
                    '0', '0', '0', '0', '0',
                    '0', '0', '0', '0', '0'
                ]
            ]
        ];

        $currentLevelId = isset($_GET['level']) ? (int)$_GET['level'] : 1;
        $currentLevel = $levels[$currentLevelId] ?? $levels[1];
        $totalLevels = count($levels);

        require_once __DIR__ . '/../views/lessons/engineering_water_pipe.php';
    }

    
    public function updateWaterPipeScore() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['action']) || $data['action'] !== 'commit') {
            echo json_encode(['success' => false, 'message' => 'Unsupported action']);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        $scorePct = isset($data['score_pct']) ? (int)$data['score_pct'] : 100;
        $gameId = isset($data['game_id']) ? (int)$data['game_id'] : null;

        try {
            require_once __DIR__ . '/../models/Database.php';
            require_once __DIR__ . '/../models/Score.php';

            $db = (new Database())->getConnection();

            if (empty($gameId)) {
                $stmt = $db->prepare('SELECT id FROM games WHERE game_name = :name LIMIT 1');
                $stmt->execute([':name' => 'Hệ thống dẫn nước']);
                $r = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($r) {
                    $gameId = (int)$r['id'];
                } else {
                    $stmt2 = $db->prepare('SELECT id FROM games WHERE game_name LIKE :like LIMIT 1');
                    $stmt2->execute([':like' => '%dẫn nước%']);
                    $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                    if ($r2) $gameId = (int)$r2['id'];
                }
            }

            if (empty($gameId)) {
                echo json_encode(['success' => false, 'message' => 'Game "Hệ thống dẫn nước" not registered']);
                return;
            }

            $pct = max(0, min(100, $scorePct));

            $xpAwarded = 20;
            if (isset($data['xp'])) {
                $xpAwarded = (int)$data['xp'];
            } else {
                try {
                    $gstmt = $db->prepare('SELECT xp FROM games WHERE id = :gid LIMIT 1');
                    $gstmt->execute([':gid' => $gameId]);
                    $grow = $gstmt->fetch(PDO::FETCH_ASSOC);
                    if ($grow && isset($grow['xp'])) $xpAwarded = (int)$grow['xp'];
                } catch (Exception $e) {
                    // ignore
                }
            }

            $res = Score::saveAndMark((int)$userId, $gameId, $pct, $xpAwarded);
            if (is_array($res)) {
                $res['xp_awarded'] = $xpAwarded;
            }

            echo json_encode($res);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    
    public function updateNumberGameScore() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['action'])) {
            echo json_encode(['success' => false, 'message' => 'Missing action']);
            exit();
        }

        if ($data['action'] === 'commit') {
            $userId = $_SESSION['user_id'] ?? null;
            if (empty($userId)) {
                echo json_encode(['success' => false, 'message' => 'User not logged in']);
                exit();
            }

            try {
                require_once __DIR__ . '/../models/Database.php';
                require_once __DIR__ . '/../models/Score.php';

                $db = (new Database())->getConnection();

                $stmt = $db->prepare("SELECT id FROM games WHERE game_name = :name LIMIT 1");
                $stmt->execute([':name' => 'Trò chơi Số học']);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$row) {
                    $stmt2 = $db->prepare("SELECT id FROM games WHERE game_name LIKE :like LIMIT 1");
                    $stmt2->execute([':like' => '%Số học%']);
                    $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                    if ($r2) {
                        $gameId = (int)$r2['id'];
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Game "Trò chơi Số học" not found']);
                        exit();
                    }
                } else {
                    $gameId = (int)$row['id'];
                }

                $scorePct = isset($data['score_pct']) ? max(0, min(100, (int)$data['score_pct'])) : 0;

               
                $correctCount = (int)round(($scorePct / 100) * 20);
                $xpAwarded = max(0, min(20, $correctCount)); 

                if (!empty($_SESSION['number_game_committed'])) {
                    echo json_encode(['success' => true, 'message' => 'Already committed', 'xp_awarded' => 0]);
                    exit();
                }

                $res = Score::saveAndMark((int)$userId, $gameId, $scorePct, $xpAwarded);

                if (is_array($res) && !empty($res['success'])) {
                    $_SESSION['number_game_committed'] = true;
                    $res['xp_awarded'] = $xpAwarded;
                }

                echo json_encode($res);
                exit();

            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit();
            }
        }

        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
    }
}