console.log('science.js loaded');
console.log('baseUrl(from DOM):', baseUrl, ' window.baseUrl:', window.baseUrl);

const planets = {
    1: {
        name: "THẾ GIỚI MÀU SẮC",
        icon: "🎨",
        status: "completed",
        description: "Khám phá bí mật của màu sắc qua các hoạt động thú vị",
        time: "15 phút",
        xp: "50 XP",
        activities: [
                        { type: "game", name: "Trò chơi pha màu", icon: "🎮", xp: "25 XP",
                            link: baseUrl + '/views/lessons/science_color_game', status: "completed" }
        ]
    },
    2: {
        name: "BÍ KÍP ĂN UỐNG LÀNH MẠNH",
        icon: "🍎",
        status: "completed",
        description: "Học cách chọn thực phẩm tốt cho sức khỏe",
        time: "20 phút",
        xp: "50 XP",
        activities: [
            { type: "game", name: "Trò chơi tháp dinh dưỡng", icon: "🧩", xp: "50 XP",
              link: baseUrl + '/views/lessons/science_nutrition_game', status: "completed" }
        ]
    },
    3: {
        name: "NGÀY VÀ ĐÊM", 
        icon: "🌓",
        status: "current",
        description: "Khám phá bí mật của thời gian và thiên văn",
        time: "12 phút", 
        xp: "50 XP",
        activities: [
            { 
                type: "question", 
                name: "Trả lời câu hỏi", 
                icon: "🌞", 
                xp: "50 XP",
                link: baseUrl + '/views/lessons/science_day_night',
                status: "current"
            }
        ]
    },
    4: {
        name: "THÙNG RÁC THÂN THIỆN",
        icon: "🗑️",
        status: "current",
        description: "Học cách phân loại rác bảo vệ môi trường",
        time: "16 phút",
        xp: "50 XP",
        activities: [
            { type: "game", name: "Trò chơi phân loại rác", icon: "♻️", xp: "30 XP",
                link: baseUrl + '/views/lessons/science_trash_game', status: "current" }
        ]
    },
    5: {
        name: "CÁC BỘ PHẬN CỦA CÂY",
        icon: "🌱",
        status: "current",
        description: "Học cách nhận biết các bộ phận của cây",
        time: "10 phút",
        xp: "30 XP",
        activities: [
            { type: "game", name: "Trò chơi lắp ghép", icon: "🌿", xp: "30 XP",
              link: baseUrl + '/views/lessons/science_plant_game', status: "current" }
        ]
    }
};

// Unlock all activities in the science panel so they become available
// (Converts any 'locked' status into 'current' so items become clickable)
for (const pid in planets) {
    if (!planets.hasOwnProperty(pid)) continue;
    const p = planets[pid];
    if (p.status === 'locked') p.status = 'current';
    if (Array.isArray(p.activities)) {
        p.activities.forEach(act => {
            if (act.status === 'locked') act.status = 'current';
        });
    }
}

function initScienceSystem() {
    console.log('🚀 Initializing Science System...');
    
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

    let currentPlanetData = null;

    document.querySelectorAll('.planet').forEach(planet => {
        planet.addEventListener('click', function() {
            const planetId = this.getAttribute('data-planet');
            console.log(`🪐 Planet clicked: ${planetId}`);
            
            currentPlanetData = planets[planetId];
            
            if (!currentPlanetData) {
                console.error('❌ Không tìm thấy dữ liệu cho planet:', planetId);
                return;
            }
            
            infoIcon.textContent = currentPlanetData.icon;
            infoName.textContent = currentPlanetData.name;
            infoDescription.textContent = currentPlanetData.description;
            
            let statusText = '';
            let statusClass = '';
            
            if (currentPlanetData.status === 'completed') {
                statusText = 'Đã hoàn thành';
                statusClass = 'status-completed';
            } else if (currentPlanetData.status === 'current') {
                statusText = 'Đang học';
                statusClass = 'status-current';
            } else {
                statusText = 'Chờ mở khóa';
                statusClass = 'status-locked';
            }
            
            infoStatus.textContent = statusText;
            infoStatus.className = 'status ' + statusClass;
            
            activitiesGrid.innerHTML = '';
            currentPlanetData.activities.forEach(activity => {
                const activityElement = document.createElement('div');
                activityElement.className = 'activity-item';
                
                if (activity.status === 'completed') {
                    activityElement.classList.add('activity-completed');
                } else if (activity.status === 'current') {
                    activityElement.classList.add('activity-current');
                } else if (activity.status === 'locked') {
                    activityElement.classList.add('activity-locked');
                }
                
                if (activity.link && activity.status !== 'locked') {
                    activityElement.classList.add('activity-clickable');
                    activityElement.style.cursor = 'pointer';
                } else {
                    activityElement.style.cursor = 'not-allowed';
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

            planetInfoOverlay.classList.add('show');
            console.log('📱 Info panel shown');
         
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScienceSystem);
} else {
    initScienceSystem();
}