<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quản lý Người dùng STEM</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/users.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-atom"></i>
                <h2>STEM Admin</h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tổng quan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="statistics.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Thống kê</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="learning_materials.php">
                            <i class="fas fa-book"></i>
                            <span>Học liệu</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="users.php">
                            <i class="fas fa-users"></i>
                            <span>Người dùng</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Cài đặt</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="admin-info">
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="admin-details">
                        <h4>Admin</h4>
                        <p>Quản trị viên</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div class="filter-section">
                <div class="filter-header">
                    <div class="filter-title">Bộ lọc người dùng</div>
                    <div class="filter-controls">
                        <input class="filter-input" id="searchInput" type="text" placeholder="Tìm kiếm người dùng — nhập tên hoặc gmail">
                        <button class="clear-filters" id="clearFilters">Xóa tìm kiếm</button>
                    </div>
                </div>
            </div>

            <div class="excel-container">
                <div class="table-header">
                    <div class="table-stats">
                        <span id="usersCount">0</span> người dùng
                    </div>
                    <div class="table-actions">
                        <button class="btn-icon" id="exportBtn" title="Xuất Excel">
                            <i class="fas fa-file-excel"></i>
                        </button>
                        <button class="btn-icon" id="refreshBtn" title="Làm mới">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
                <table class="excel-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Thông tin người dùng</th>
                            <th>Khối lớp</th>
                            <th>Ghi chú</th>
                            <th>Ngày đăng ký</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                    </tbody>
                </table>
                
                <div class="empty-state" id="emptyState" style="display: none;">
                    <i class="fas fa-users"></i>
                    <h3>Không tìm thấy người dùng</h3>
                    <p>Không có người dùng nào phù hợp với bộ lọc của bạn. Hãy thử điều chỉnh bộ lọc hoặc thêm người dùng mới.</p>
                </div>
                
                <div class="pagination">
                    <div class="pagination-info" id="paginationInfo">Hiển thị 0-0 của 0 người dùng</div>
                    <div class="pagination-controls">
                        <button class="pagination-btn" id="prevPage" disabled>
                            <i class="fas fa-chevron-left"></i> Trước
                        </button>
                        <div class="pagination-numbers" id="paginationNumbers"></div>
                        <button class="pagination-btn" id="nextPage" disabled>
                            Sau <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="modal" id="userModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 id="modalTitle">Thêm người dùng mới</h3>
                        <button class="modal-close" id="closeModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="userForm">
                            <div class="form-group">
                                <label for="userFullName">Họ và tên *</label>
                                <input type="text" id="userFullName" name="fullName" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="userEmail">Email *</label>
                                    <input type="email" id="userEmail" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="userPhone">Số điện thoại</label>
                                    <input type="tel" id="userPhone" name="phone">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group" id="gradeField">
                                    <label for="userGrade">Khối / Lớp</label>
                                    <input type="text" id="userGrade" name="grade" placeholder="Nhập khối hoặc lớp (ví dụ: 5A1)">
                                </div>
                            </div>
                            
                            
                            <div class="form-group">
                                <label for="userNotes">Ghi chú</label>
                                <textarea id="userNotes" name="notes" rows="3"></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn-secondary" id="cancelBtn">Hủy</button>
                                <button type="submit" class="btn-primary" id="saveBtn">Lưu người dùng</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/users.js?v=<?php echo time(); ?>"></script>
</body>
</html>