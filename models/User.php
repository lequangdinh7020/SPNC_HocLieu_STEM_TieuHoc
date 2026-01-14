<?php
class User {
    private $conn;
    private $table_name = "users";
    
    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $full_name;
    public $class;
    public $avatar;
    public $xp;
    public $email_verified;
    public $verification_code;
    public $verification_expires;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function login($username, $password, $remember = false) {
      $query = "SELECT id, username, email, password, role, first_name, last_name, class, avatar, xp, email_verified 
            FROM " . $this->table_name . " 
            WHERE username = :username OR email = :username";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $row['password'])) {
                    // Nếu email chưa xác thực thì trả về chỉ báo
                    $emailVerified = isset($row['email_verified']) ? (int)$row['email_verified'] : 0;
                    if ($emailVerified === 0) {
                        // Không cho phép đăng nhập nếu email chưa xác thực
                        return 'not_verified';
                    }

                    // Cập nhật thông tin user
                    $this->id = $row['id'];
                    $this->username = $row['username'];
                    $this->email = $row['email'] ?? null;        
                    $this->role = $row['role'];
                    $this->full_name = $row['first_name'] . ' ' . $row['last_name'];
                    $this->class = $row['class'];
                    $this->avatar = $row['avatar'];
                    $this->xp = isset($row['xp']) ? (int)$row['xp'] : 0;

                    // Xử lý ghi nhớ đăng nhập với cơ chế selector/validator
                    if ($remember) {
                        $this->createRememberToken();
                    }
                    // last_active removed — no update here
                    
                    return true;
                }
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            throw new Exception("Lỗi đăng nhập");
        }
        return false;
    }

    private function createRememberToken() {
    try {
        $selector = bin2hex(random_bytes(16));   // hex string
        $validator = bin2hex(random_bytes(32));  // hex string
        $hashedValidator = hash('sha256', $validator);

        // Thời gian hết hạn (DATETIME)
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        $this->conn->beginTransaction();

        // Nếu bạn muốn giới hạn 1 token toàn cục cho user -> xóa tất cả token cũ
        // Nếu muốn cho phép nhiều thiết bị thì comment dòng delete này hoặc thay bằng xóa token cũ theo user_agent/ip
        $deleteOld = $this->conn->prepare("DELETE FROM remember_tokens WHERE user_id = :user_id");
        $deleteOld->execute([':user_id' => $this->id]);

        // Insert token mới
        $query = "INSERT INTO remember_tokens (user_id, selector, hashed_validator, user_agent, ip, expires_at) 
                  VALUES (:user_id, :selector, :hashed_validator, :user_agent, :ip, :expires)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => $this->id,
            ':selector' => $selector,
            ':hashed_validator' => $hashedValidator,
            ':user_agent' => $userAgent,
            ':ip' => $ip,
            ':expires' => $expires
        ]);

        // Commit transaction
        $this->conn->commit();

        // Tạo cookie value: Lưu selector + validator vào cookie (validator là bản gốc, hashed lưu DB)
        //  -> cookie format: base64(selector).base64(validator)
        $cookieValue = base64_encode($selector) . '.' . base64_encode($validator);

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        // Set cookie an toàn
        setcookie('remember', $cookieValue, [
            'expires' => time() + 86400 * 30,
            'path' => '/',
            'domain' => '', // nếu cần, đặt domain cụ thể
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        return true;
    } catch (Exception $e) {
        // rollback nếu transaction đang mở
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        error_log("Create remember token error: " . $e->getMessage());
        return false;
    }
}

    /**
     * Recalculate total XP for a user based on their highest xp_awarded per game.
     * If $userId is null, use $this->id. Returns total XP (int) or false on error.
     */
    public function recalculateXp($userId = null) {
        $uid = $userId ?: $this->id;
        if (!$uid) return false;
        try {
            $sql = "SELECT COALESCE(SUM(mx),0) AS total FROM (SELECT game_id, MAX(xp_awarded) AS mx FROM scores WHERE user_id = :uid GROUP BY game_id) t";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':uid' => $uid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = isset($row['total']) ? (int)$row['total'] : 0;

            $upd = $this->conn->prepare("UPDATE " . $this->table_name . " SET xp = :xp WHERE id = :id");
            $upd->execute([':xp' => $total, ':id' => $uid]);

            if ($this->id && (int)$this->id === (int)$uid) {
                $this->xp = $total;
            }

            return $total;
        } catch (PDOException $e) {
            error_log('recalculateXp error: ' . $e->getMessage());
            return false;
        }
    }


    public function register($username, $email, $password, $fullName, $class = null, $phone = null) {
        try {
            // Kiểm tra username đã tồn tại
            $stmt = $this->conn->prepare("SELECT id FROM " . $this->table_name . " WHERE username = :username");
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return false;
            }

            // Kiểm tra email đã tồn tại
            $stmt = $this->conn->prepare("SELECT id FROM " . $this->table_name . " WHERE email = :email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return false;
            }

            // Xử lý tách họ và tên
            $nameParts = explode(" ", $fullName);
            $lastName = array_pop($nameParts);
            $firstName = implode(" ", $nameParts);

            // Thêm user mới (bao gồm phone nếu cột tồn tại)
            // Detect columns
            $existing = [];
            try {
                $colStmt = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name);
                while ($c = $colStmt->fetch(PDO::FETCH_ASSOC)) $existing[] = $c['Field'];
            } catch (Exception $e) { $existing = []; }

            $cols = ['username','email','password','first_name','last_name','class','role'];
            $placeholders = [':username',':email',':password',':first_name',':last_name',':class',"'user'"];
            $params = [
                ':username' => $username,
                ':email' => $email,
                ':password' => null, // will bind after hash
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':class' => $class
            ];

            if (in_array('phone', $existing)) {
                $cols[] = 'phone';
                $placeholders[] = ':phone';
                $params[':phone'] = $phone;
            }

            $sql = 'INSERT INTO ' . $this->table_name . ' (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
            $stmt = $this->conn->prepare($sql);

            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $params[':password'] = $password_hash;

            // Execute once and return new user ID on success
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $params[':password'] = $password_hash;

            $success = $stmt->execute($params);
            if ($success) {
                return (int)$this->conn->lastInsertId();
            }
        } catch(PDOException $e) {
            error_log("Register error: " . $e->getMessage());
            throw new Exception("Lỗi đăng ký");
        }
        return false;
    }

    /**
     * Tạo mã xác thực cho user vừa đăng ký
     * Trả về mã xác thực (string) nếu thành công, false nếu thất bại
     */
    public function addVerification($userId, $length = 6, $minutes = 60) {
        try {
            // Tạo mã số ngẫu nhiên gồm chữ số
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= random_int(0, 9);
            }

            $expires = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));

            $query = "UPDATE " . $this->table_name . " SET verification_code = :code, verification_expires = :expires, email_verified = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':code' => $code,
                ':expires' => $expires,
                ':id' => $userId
            ]);

            return $code;
        } catch (PDOException $e) {
            error_log("Add verification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xác thực email bằng email và mã (hoặc bằng id và mã)
     */
    public function verifyEmail($email, $code) {
        try {
            $query = "SELECT id, verification_code, verification_expires FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);
            if ($stmt->rowCount() === 0) {
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row['verification_code']) return false;
            if ($row['verification_code'] !== $code) return false;
            if ($row['verification_expires'] && strtotime($row['verification_expires']) < time()) return false;

            // Cập nhật trạng thái đã xác thực
            $update = $this->conn->prepare("UPDATE " . $this->table_name . " SET email_verified = 1, verification_code = NULL, verification_expires = NULL WHERE id = :id");
            $update->execute([':id' => $row['id']]);
            return true;
        } catch (PDOException $e) {
            error_log("Verify email error: " . $e->getMessage());
            return false;
        }
    }

    public function createResetCode($email) {
        try {
            error_log("Creating reset code for email: " . $email);
            
            // Kiểm tra email tồn tại
            $stmt = $this->conn->prepare("SELECT id FROM " . $this->table_name . " WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                error_log("Email not found: " . $email);
                return false;
            }

            // Tạo mã xác nhận 6 số
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= random_int(0, 9);
            }

            // Thời gian hết hạn (15 phút)
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            error_log("Generated reset code: " . $code);
            error_log("Expires at: " . $expires);

            // Lưu mã và thời gian hết hạn
            $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " 
                                        SET reset_code = :code, reset_code_expires = :expires 
                                        WHERE email = :email");
            $stmt->execute([
                ':code' => $code,
                ':expires' => $expires,
                ':email' => $email
            ]);

            // Verify the code was saved correctly
            $verify = $this->conn->prepare("SELECT reset_code FROM " . $this->table_name . " WHERE email = :email");
            $verify->execute([':email' => $email]);
            $saved = $verify->fetch(PDO::FETCH_ASSOC);
            
            if ($saved['reset_code'] !== $code) {
                error_log("Warning: Saved code doesn't match generated code!");
                error_log("Generated: " . $code);
                error_log("Saved: " . $saved['reset_code']);
            }

            return $code;
        } catch (PDOException $e) {
            error_log("Create reset code error: " . $e->getMessage());
            return false;
        }
    }

    public function verifyResetCode($email, $code) {
        try {
            // Kiểm tra mã trong database
            $stmt = $this->conn->prepare("SELECT id, reset_code, reset_code_expires FROM " . $this->table_name . " 
                                        WHERE email = :email");
            $stmt->execute([':email' => $email]);
            
            if ($stmt->rowCount() == 0) {
                error_log("Reset code verify failed: Email not found: " . $email);
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Log để debug
            error_log("Reset code verification:");
            error_log("Email: " . $email);
            error_log("Submitted code: " . $code);
            error_log("Stored code: " . $row['reset_code']);
            error_log("Expires: " . $row['reset_code_expires']);
            error_log("Current time: " . date('Y-m-d H:i:s'));

            // Kiểm tra từng điều kiện riêng biệt
            if ($row['reset_code'] !== $code) {
                error_log("Reset code verify failed: Code mismatch");
                return false;
            }

            if (strtotime($row['reset_code_expires']) < time()) {
                error_log("Reset code verify failed: Code expired");
                return false;
            }

            return true;
        } catch (PDOException $e) {
            error_log("Verify reset code error: " . $e->getMessage());
            return false;
        }
    }

    public function resetPassword($email, $code, $newPassword) {
        try {
            // Kiểm tra mã xác nhận
            if (!$this->verifyResetCode($email, $code)) {
                return false;
            }

            // Hash mật khẩu mới
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Cập nhật mật khẩu và xóa mã reset
            $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " 
                                        SET password = :password, 
                                            reset_code = NULL, 
                                            reset_code_expires = NULL 
                                        WHERE email = :email");
            $stmt->execute([
                ':password' => $hashedPassword,
                ':email' => $email
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("Reset password error: " . $e->getMessage());
            return false;
        }
    }

    public function checkRememberToken($cookieValue) {
        try {
            // Tách selector và validator từ cookie
            $parts = explode('.', $cookieValue);
            if (count($parts) !== 2) {
                return false;
            }

            $selector = base64_decode($parts[0]);
            $validator = base64_decode($parts[1]);

            if (!$selector || !$validator) {
                return false;
            }

            // Tìm token trong database bằng selector
           $query = "SELECT rt.*, u.username, u.email, u.role, u.first_name, u.last_name, u.class, u.avatar
                FROM remember_tokens rt
                INNER JOIN " . $this->table_name . " u ON rt.user_id = u.id
                WHERE rt.selector = :selector AND rt.expires_at > NOW()
                LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':selector' => $selector]);
            
            if ($stmt->rowCount() === 0) {
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Xác thực validator
            if (hash_equals($row['hashed_validator'], hash('sha256', $validator))) {
                $this->id = $row['user_id'];
                $this->username = $row['username'];
                $this->email = $row['email'] ?? 
                $this->role = $row['role'];
                $this->full_name = $row['first_name'] . ' ' . $row['last_name'];
                $this->class = $row['class'];
                $this->avatar = $row['avatar'];

                // Xoay token (tạo token mới)
                $this->createRememberToken();

                // Xóa token cũ
                $delete = $this->conn->prepare("DELETE FROM remember_tokens WHERE id = :id");
                $delete->execute([':id' => $row['id']]);

                return true;
            }

            // Nếu validator không khớp, xóa token này vì có thể là tấn công
            $delete = $this->conn->prepare("DELETE FROM remember_tokens WHERE selector = :selector");
            $delete->execute([':selector' => $selector]);
            
        } catch(PDOException $e) {
            error_log("Check remember token error: " . $e->getMessage());
            throw new Exception("Lỗi kiểm tra token");
        }
        return false;
    }
}
?>