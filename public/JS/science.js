console.log('science.js loaded');
console.log('baseUrl(from DOM):', baseUrl, ' window.baseUrl:', window.baseUrl);

const planets = {
    1: {
        name: "THẾ GIỚI MÀU SẮC",
        icon: "🎨",
        status: "not-started",
        description: "Khám phá bí mật của màu sắc qua các hoạt động thú vị",
        time: "15 phút",
        xp: "20 XP",
        activities: [
            { 
                type: "game", 
                name: "Trò chơi pha màu", 
                icon: "🎮", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/science_color_game', 
                status: "not-started"
            }
        ]
    },
    2: {
        name: "BÍ KÍP ĂN UỐNG LÀNH MẠNH",
        icon: "🍎",
        status: "not-started",
        description: "Học cách chọn thực phẩm tốt cho sức khỏe",
        time: "20 phút",
        xp: "20 XP",
        activities: [
            { 
                type: "game", 
                name: "Trò chơi tháp dinh dưỡng", 
                icon: "🧩", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/science_nutrition_game', 
                status: "not-started"
            }
        ]
    },
    3: {
        name: "NGÀY VÀ ĐÊM", 
        icon: "🌓",
        status: "not-started",
        description: "Khám phá bí mật của thời gian và thiên văn",
        time: "12 phút", 
        xp: "20 XP",
        activities: [
            { 
                type: "question", 
                name: "Trả lời câu hỏi", 
                icon: "🌞", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/science_day_night',
                status: "not-started"
            }
        ]
    },
    4: {
        name: "THÙNG RÁC THÂN THIỆN",
        icon: "🗑️",
        status: "not-started",
        description: "Học cách phân loại rác bảo vệ môi trường",
        time: "16 phút",
        xp: "20 XP",
        activities: [
            { 
                type: "game", 
                name: "Trò chơi phân loại rác", 
                icon: "♻️", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/science_trash_game', 
                status: "not-started"
            }
        ]
    },
    5: {
        name: "CÁC BỘ PHẬN CỦA CÂY",
        icon: "🌱",
        status: "not-started",
        description: "Học cách nhận biết các bộ phận của cây",
        time: "10 phút",
        xp: "20 XP",
        activities: [
            { 
                type: "game", 
                name: "Trò chơi lắp ghép", 
                icon: "🌿", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/science_plant_game', 
                status: "not-started"
            }
        ]
    }
};

// Sử dụng localStorage để tránh delay
const STORAGE_KEY = 'science_planet_status';

// Hàm lưu tất cả trạng thái vào localStorage
function saveAllPlanetStatuses() {
    try {
        const statuses = {};
        for (const id in planets) {
            statuses[id] = planets[id].status;
        }
        localStorage.setItem(STORAGE_KEY, JSON.stringify(statuses));
        console.log('💾 All planet statuses saved to localStorage');
        return true;
    } catch (e) {
        console.error('❌ Error saving to localStorage:', e);
        return false;
    }
}

// Hàm load trạng thái từ localStorage
function loadPlanetStatuses() {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const statuses = JSON.parse(saved);
            for (const id in statuses) {
                if (planets[id]) {
                    planets[id].status = statuses[id];
                    // Update activities status
                    planets[id].activities.forEach(act => {
                        act.status = statuses[id];
                    });
                }
            }
            console.log('📥 Planet statuses loaded from localStorage:', statuses);
        }
        updatePlanetDisplay();
    } catch (e) {
        console.warn('⚠️ Could not load from localStorage:', e);
        updatePlanetDisplay();
    }
}

// Hàm đánh dấu planet là current
function markPlanetAsCurrent(planetId) {
    const planet = planets[planetId];
    if (!planet) {
        console.error(`❌ Planet ${planetId} not found`);
        return false;
    }
    
    // Chỉ chuyển nếu đang là not-started
    if (planet.status === 'not-started') {
        console.log(`🔄 Marking planet ${planetId} as current...`);
        
        // Cập nhật ngay lập tức
        planet.status = 'current';
        planet.activities.forEach(act => {
            act.status = 'current';
        });
        
        // Cập nhật hiển thị ngay
        updatePlanetDisplay();
        
        // Lưu vào localStorage ngay
        saveAllPlanetStatuses();
        
        console.log(`✅ Planet ${planetId} marked as current`);
        return true;
    }
    
    return false;
}

// Hàm cập nhật hiển thị planet
function updatePlanetDisplay() {
    console.log('🔄 Updating planet display...');
    
    document.querySelectorAll('.planet').forEach(el => {
        const pid = el.getAttribute('data-planet');
        const pdata = planets[pid];
        if (!pdata) return;
        
        // Remove all status classes
        el.classList.remove('completed', 'current', 'not-started', 'locked');
        
        // Add the correct status class
        if (pdata.status === 'completed') {
            el.classList.add('completed');
            el.style.opacity = '';
            el.style.filter = '';
            console.log(`🌍 Planet ${pid}: COMPLETED ✓`);
        } else if (pdata.status === 'current') {
            el.classList.add('current');
            el.style.opacity = '';
            el.style.filter = '';
            console.log(`🌍 Planet ${pid}: CURRENT ●`);
        } else if (pdata.status === 'not-started') {
            el.classList.add('not-started');
            el.style.opacity = '0.5';
            el.style.filter = 'grayscale(0.7)';
            console.log(`🌍 Planet ${pid}: NOT STARTED (dimmed)`);
        } else {
            el.classList.add('locked');
            console.log(`🌍 Planet ${pid}: LOCKED`);
        }
    });
}

// Main initialization
function initScienceSystem() {
    console.log('🚀 Initializing Science System...');
    
    // Load trạng thái từ localStorage ngay lập tức
    loadPlanetStatuses();
    
    const planetInfoOverlay = document.getElementById('planetInfoOverlay');
    const infoIcon = document.getElementById('infoIcon');
    const infoName = document.getElementById('infoName');
    const infoStatus = document.getElementById('infoStatus');
    const infoDescription = document.getElementById('infoDescription');
    const activitiesGrid = document.getElementById('activitiesGrid');
    const closeInfo = document.getElementById('closeInfo');
    const characterBtn = document.getElementById('characterBtn');

    const elements = {
        planetInfoOverlay, infoIcon, infoName, infoStatus, infoDescription,
        activitiesGrid, closeInfo, characterBtn
    };

    for (const [name, element] of Object.entries(elements)) {
        if (!element) {
            console.error(`❌ Không tìm thấy element: ${name}`);
            return false;
        }
    }

    console.log('✅ Tất cả elements đã được tìm thấy');

    // Xử lý click vào planet
    document.querySelectorAll('.planet').forEach(planet => {
        planet.addEventListener('click', function() {
            const planetId = this.getAttribute('data-planet');
            console.log(`🪐 Planet clicked: ${planetId}`);
            
            const currentPlanetData = planets[planetId];
            
            if (!currentPlanetData) {
                console.error('❌ Không tìm thấy dữ liệu cho planet:', planetId);
                return;
            }
            
            // QUAN TRỌNG: Đánh dấu là current NGAY KHI CLICK
            const wasMarked = markPlanetAsCurrent(planetId);
            
            // Hiển thị thông tin
            infoIcon.textContent = currentPlanetData.icon;
            infoName.textContent = currentPlanetData.name;
            infoDescription.textContent = currentPlanetData.description;
            
            let statusText = '';
            let statusClass = '';
            
            // Sử dụng trạng thái mới (sau khi đã update)
            const displayStatus = planets[planetId].status;
            
            if (displayStatus === 'completed') {
                statusText = 'Đã hoàn thành';
                statusClass = 'status-completed';
            } else if (displayStatus === 'current') {
                statusText = 'Đang học';
                statusClass = 'status-current';
            } else if (displayStatus === 'not-started') {
                statusText = 'Chưa học';
                statusClass = 'status-not-started';
            } else {
                statusText = 'Chờ mở khóa';
                statusClass = 'status-locked';
            }
            
            infoStatus.textContent = statusText;
            infoStatus.className = 'status ' + statusClass;
            
            // Hiển thị activities
            activitiesGrid.innerHTML = '';
            planets[planetId].activities.forEach(activity => {
                const activityElement = document.createElement('div');
                activityElement.className = 'activity-item';
                
                if (activity.status === 'completed') {
                    activityElement.classList.add('activity-completed');
                } else if (activity.status === 'current') {
                    activityElement.classList.add('activity-current');
                } else if (activity.status === 'not-started') {
                    activityElement.classList.add('activity-not-started');
                } else if (activity.status === 'locked') {
                    activityElement.classList.add('activity-locked');
                }
                
                // Tất cả hoạt động đều có thể click
                if (activity.link) {
                    activityElement.classList.add('activity-clickable');
                    activityElement.style.cursor = 'pointer';
                } else {
                    activityElement.style.cursor = 'pointer';
                }
                
                let statusBadge = '';
                if (activity.status === 'completed') {
                    statusBadge = '<div class="activity-status-badge completed-badge">✓</div>';
                } else if (activity.status === 'current') {
                    statusBadge = '<div class="activity-status-badge current-badge">●</div>';
                }
                
                activityElement.innerHTML = `
                    ${statusBadge}
                    <div class="activity-icon">${activity.icon}</div>
                    <div class="activity-info">
                        <div class="activity-name">${activity.name}</div>
                        <div class="activity-type">${activity.type === 'game' ? 'Trò chơi' : 'Câu hỏi'}</div>
                    </div>
                    <div class="activity-xp">${activity.xp}</div>
                `;
                
                if (activity.link) {
                    activityElement.addEventListener('click', function(e) {
                        e.stopPropagation();
                        console.log(`🎮 Navigating to: ${activity.link}`);
                        window.location.href = activity.link;
                    });
                }
                
                activitiesGrid.appendChild(activityElement);
            });

            // Hiển thị panel
            planetInfoOverlay.classList.add('show');
            console.log('📱 Info panel shown');
            
            // Thông báo nếu đã chuyển trạng thái
            if (wasMarked) {
                console.log(`🌟 Planet ${planetId} is now marked as "đang học"`);
            }
            
            // Hiệu ứng click
            this.style.transform = 'scale(1.3)';
            setTimeout(() => {
                this.style.transform = '';
            }, 300);
        });
    });

    function closeInfoPanel() {
        planetInfoOverlay.classList.remove('show');
        console.log('📱 Info panel closed');
    }

    closeInfo.addEventListener('click', closeInfoPanel);

    characterBtn.addEventListener('click', function() {
        console.log('🦖 Character clicked');
        alert('Chào nhà khoa học nhí! Mình là Khủng Long Vũ Trụ! 🦖\nHãy chọn một hành tinh để bắt đầu khám phá!');
    });

    planetInfoOverlay.addEventListener('click', function(e) {
        if (e.target === this) {
            closeInfoPanel();
        }
    });

    document.querySelectorAll('.planet').forEach(planet => {
        planet.addEventListener('mouseenter', function() {
            this.style.animationPlayState = 'paused';
        });
        
        planet.addEventListener('mouseleave', function() {
            this.style.animationPlayState = 'running';
        });
    });

    console.log('🎉 Science System initialized successfully!');
    return true;
}

// Thêm: Xử lý khi tải lại trang
window.addEventListener('beforeunload', function() {
    saveAllPlanetStatuses();
});

// Thêm: Tự động lưu mỗi 5 giây để đảm bảo không mất dữ liệu
setInterval(saveAllPlanetStatuses, 5000);

// Start the system when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScienceSystem);
} else {
    initScienceSystem();
}