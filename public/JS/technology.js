console.log('technology.js loaded');
console.log('baseUrl(from DOM):', baseUrl, ' window.baseUrl:', window.baseUrl);

const planets = {
    1: {
        name: "CÂY GIA ĐÌNH",
        icon: "🌳",
        status: "completed",
        description: "Tìm hiểu về các mối quan hệ gia đình qua cây phả hệ",
        time: "20 phút",
        xp: "25 XP",
        activities: [
            { 
                type: "game", 
                name: "Trò chơi cây gia đình", 
                icon: "🎮", 
                xp: "25 XP", 
                link: baseUrl + '/views/lessons/technology_family_tree_game', 
                status: "completed" 
            }
            
        ]
    },
    2: {
        name: "EM LÀ HỌA SĨ MÁY TÍNH",
        icon: "🎨",
        status: "current",
        description: "Khám phá các công cụ vẽ đơn giản trên máy tính",
        time: "25 phút",
        xp: "50 XP",
        activities: [
            { 
                type: "share", 
                name: "Chia sẻ tác phẩm", 
                icon: "🖼️", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/technology_painter_game', 
                status: "current" 
            }
        ]
    },
    3: {
        name: "EM LÀ NGƯỜI ĐÁNH MÁY",
        icon: "⌨️",
        status: "current",
        description: "Rèn luyện kỹ năng đánh máy nhanh và chính xác",
        time: "35 phút",
        xp: "75 XP",
        activities: [
            { 
                type: "game", 
                name: "Trò chơi đánh máy", 
                icon: "🎮", 
                xp: "40 XP",
                link: baseUrl + '/views/lessons/technology_typing_thach_sanh', 
                status: "current" 
            }
        ]
    },
    4: {
        name: "SƠN TINH (LẬP TRÌNH KHỐI)",
        icon: "🧩",
        status: "current",
        description: "Làm quen với lập trình các khối lệnh",
        time: "30 phút",
        xp: "70 XP",
        activities: [
            { 
                type: "game", 
                name: "Thực hành Scratch", 
                icon: "🎮", 
                xp: "40 XP",
                link: baseUrl + '/views/lessons/technology_coding_game', 
                status: "current" 
            }
        ]
    },
    5: {
        name: "CÁC BỘ PHẬN CỦA MÁY TÍNH",
        icon: "💻",
        status: "current",
        description: "Tìm hiểu các thành phần cơ bản của máy tính",
        time: "22 phút",
        xp: "60 XP",
        activities: [
            { 
                type: "video", 
                name: "Giới thiệu bộ phận máy tính", 
                icon: "📺", 
                xp: "25 XP",
                link: baseUrl + '/views/lessons/technology_computer_parts_video', 
                status: "current" 
            },
            { 
                type: "game", 
                name: "Ghép bộ phận máy tính", 
                icon: "🧩", 
                xp: "35 XP",
                link: baseUrl + '/views/lessons/technology_computer_parts', 
                status: "current" 
            }
        ]
    }
};

function initTechnologySystem() {
    console.log('🚀 Initializing Technology System...');
    
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
                }

                if (activity.link) {
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
                        <div class="activity-type">${activity.type === 'game' ? 'Trò chơi' : 
                                                     activity.type === 'video' ? 'Video' : 
                                                     activity.type === 'tutorial' ? 'Hướng dẫn' : 
                                                     activity.type === 'share' ? 'Chia sẻ' : 'Câu hỏi'}</div>
                    </div>
                    <div class="activity-xp">${activity.xp}</div>
                `;
            
                if (activity.link && activity.status !== 'locked') {
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
        console.log('🤖 Character clicked');
        alert('Xin chào! Mình là Robot Công Nghệ! 🤖\nCùng mình khám phá 5 chủ đề công nghệ siêu thú vị nhé!');
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

    console.log('🎉 Technology System initialized successfully!');
    return true;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTechnologySystem);
} else {
    initTechnologySystem();
}
