<?php
require_once __DIR__ . '/Database.php';

class Score {
	protected $conn;

	public function __construct($dbConn = null) {
		if ($dbConn) {
			$this->conn = $dbConn;
		} else {
			$db = new Database();
			$this->conn = $db->getConnection();
		}
	}

	/**
	 * Lưu điểm (theo %) và đánh dấu hoàn thành nếu đạt `passing_score`.
	 * Trả về mảng kết quả: success, completed (bool), completed_count, total_games, certificate_awarded
	 */
	public function saveScoreAndMarkCompletion(int $userId, int $gameId, int $scorePercentage, int $xpAwarded = 0) {
		if (empty($userId) || empty($gameId)) {
			return ['success' => false, 'message' => 'Missing user_id or game_id'];
		}

		try {
			$this->conn->beginTransaction();

			// Insert vào bảng scores (store percentage) and determine xp from games.xp
			// Lấy ngưỡng passing (sử dụng cột `passing_score`) và topic của game và xp
			$stmt = $this->conn->prepare("SELECT passing_score, topic_id, xp FROM games WHERE id = :gid LIMIT 1");
			$stmt->execute([':gid' => $gameId]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$passingScore = $row && $row['passing_score'] !== null ? (int)$row['passing_score'] : null;
			$gameTopicId = $row && $row['topic_id'] !== null ? (int)$row['topic_id'] : null;
			$gameXp = $row && isset($row['xp']) ? (int)$row['xp'] : 0;

			// So sánh với passing_score (nếu có). passing_score được xem là phần trăm (0-100).
			$isCompleted = false;
			$prevCount = 0;
			$completedInserted = false;
			if ($passingScore !== null) {
				$isCompleted = ($scorePercentage >= $passingScore);
				// Check whether user already had a completion for this game before inserting
				$prevStmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM scores WHERE user_id = :uid AND game_id = :gid AND score_percentage >= :passing");
				$prevStmt->execute([':uid' => $userId, ':gid' => $gameId, ':passing' => $passingScore]);
				$prevCount = (int)$prevStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
			}

			// Use provided xpAwarded (passed from controller/session), cap to game's xp
			$xp_awarded = max(0, (int)$xpAwarded);
			if (isset($gameXp) && $xp_awarded > $gameXp) {
				$xp_awarded = $gameXp;
			}

			// Insert the new score record now (including xp_awarded)
			$insStmt = $this->conn->prepare("INSERT INTO scores (user_id, game_id, score_percentage, xp_awarded) VALUES (:uid, :gid, :score, :xp)");
			$insStmt->execute([':uid' => $userId, ':gid' => $gameId, ':score' => $scorePercentage, ':xp' => $xp_awarded]);
			// Debug log for troubleshooting
			error_log(sprintf('Score saved: user=%d game=%d score=%d xp=%d', $userId, $gameId, $scorePercentage, $xp_awarded));

			// If this score reaches/passes the threshold and there was no previous
			// qualifying score, mark this as a newly completed game for the user.
			if ($isCompleted && $prevCount === 0) {
				$completedInserted = true;
			}


			// Lấy tổng số trò của hệ thống
			$totStmt = $this->conn->query("SELECT COUNT(*) as tot FROM games");
			$totalGames = (int)$totStmt->fetch(PDO::FETCH_ASSOC)['tot'];

			// Compute completedCount using best score per game (MAX) to avoid false negatives
			$cntStmt = $this->conn->prepare(<<<'SQL'
			SELECT COUNT(*) as cnt FROM (
			SELECT s.game_id, MAX(s.score_percentage) as best
			FROM scores s
			WHERE s.user_id = :uid
			GROUP BY s.game_id
			) b JOIN games g ON b.game_id = g.id
			WHERE g.passing_score IS NOT NULL AND b.best >= g.passing_score
			SQL
			);
			$cntStmt->execute([':uid' => $userId]);
			$completedCount = (int)$cntStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

			$this->conn->commit();

			
			$certificateAwarded = false;
			$certificateTopicId = null;

			if ($completedInserted) {
				try {
					// Nếu game có topic_id thì kiểm tra award certificate
					$topicId = $gameTopicId;
					if ($topicId !== null) {
						$certificateTopicId = $topicId;

						// Tổng số games của topic (dựa trên games.topic_id)
						$totStmt = $this->conn->prepare("SELECT COUNT(g.id) as tot FROM games g WHERE g.topic_id = :tid");
						$totStmt->execute([':tid' => $topicId]);
						$totForTopic = (int)$totStmt->fetch(PDO::FETCH_ASSOC)['tot'];

						// Số game user đã hoàn thành trong topic (dựa trên best score per game)
						$cntStmt = $this->conn->prepare(<<<'SQL'
						SELECT COUNT(*) as cnt FROM (
						  SELECT s.game_id, MAX(s.score_percentage) as best
						  FROM scores s
						  WHERE s.user_id = :uid
						  GROUP BY s.game_id
						) b JOIN games g ON b.game_id = g.id
						WHERE g.topic_id = :tid AND g.passing_score IS NOT NULL AND b.best >= g.passing_score
						SQL
						);
						$cntStmt->execute([':uid' => $userId, ':tid' => $topicId]);
						$completedForTopic = (int)$cntStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

						if ($totForTopic > 0 && $completedForTopic >= $totForTopic) {
							// Ghi chứng chỉ nếu chưa có
							$ins = $this->conn->prepare("INSERT IGNORE INTO certificates (user_id, topic_id) VALUES (:uid, :tid)");
							$ins->execute([':uid' => $userId, ':tid' => $topicId]);
							if ($ins->rowCount() > 0) {
								$certificateAwarded = true;
							}
						}
					}
				} catch (Exception $e) {
					error_log('Certificate awarding error: ' . $e->getMessage());
				}
				// Note: users.xp is not auto-updated here; UI should compute XP from scores (max xp_awarded per game)
			}

			return [
				'success' => true,
				'completed' => $completedInserted,
				'completed_count' => $completedCount,
				'total_games' => $totalGames,
				'certificate_awarded' => $certificateAwarded,
				'certificate_topic_id' => $certificateTopicId
			];
		} catch (Exception $e) {
			if ($this->conn->inTransaction()) {
				$this->conn->rollBack();
			}
			return ['success' => false, 'message' => $e->getMessage()];
		}
	}

	// Static helper
	public static function saveAndMark(int $userId, int $gameId, int $scorePercentage, int $xpAwarded = 0) {
		$s = new self();
		return $s->saveScoreAndMarkCompletion($userId, $gameId, $scorePercentage, $xpAwarded);
	}
}

?>
