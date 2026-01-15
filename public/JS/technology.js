console.log('technology.js loaded');
console.log('baseUrl(from DOM):', baseUrl, ' window.baseUrl:', window.baseUrl);

const planets = {
    1: {
        name: "CÂY GIA ĐÌNH",
        icon: "🌳",
        status: "completed",
        description: "Tìm hiểu về các mối quan hệ gia đình qua cây phả hệ",
        time: "20 phút",
        xp: "20 XP",
        activities: [
            { 
                type: "game", 
                name: "Trò chơi cây gia đình", 
                icon: "🎮", 
                xp: "20 XP", 
                link: baseUrl + '/views/lessons/technology_family_tree_game', 
                status: "current" 
            }
            
        ]
    },
    2: {
        name: "EM LÀ HỌA SĨ MÁY TÍNH",
        icon: "🎨",
        status: "current",
        description: "Khám phá các công cụ vẽ đơn giản trên máy tính",
        time: "25 phút",
        xp: "20 XP",
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
        xp: "20 XP",
        activities: [
            { 
                type: "game", 
                name: "Trò chơi đánh máy", 
                icon: "🎮", 
                xp: "20 XP",
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
        xp: "20 XP",
        activities: [
            { 
                type: "game", 
                name: "Thực hành Scratch", 
                icon: "🎮", 
                xp: "20 XP",
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
        xp: "20 XP",
        activities: [
            { 
                type: "game", 
                name: "Ghép bộ phận máy tính", 
                icon: "🧩", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/technology_computer_parts', 
                status: "current" 
            }
        ]
    }
};

// Fetch completed games for current user and update planet/activity statuses
(function updatePlanetStatuses() {
    try {
        const endpoint = (typeof baseUrl !== 'undefined' ? baseUrl : '') + '/public/api/get_topic_status.php';
        fetch(endpoint, { credentials: 'same-origin' })
            .then(response => response.json())
            .then(data => {
                const completedItems = (data && data.completed_games) ? data.completed_games : [];

                const slugify = (s) => {
                    if (!s) return '';
                    return s.toString().normalize('NFD').replace(/\p{Diacritic}/gu, '')
                        .replace(/[^a-zA-Z0-9]+/g, '-')
                        .replace(/(^-|-$)/g, '').toLowerCase();
                };

                const completedNames = completedItems.map(ci => (typeof ci === 'string' ? ci : (ci.name || '')).toLowerCase());
                const completedSlugs = completedItems.map(ci => (typeof ci === 'string' ? slugify(ci) : (ci.slug || slugify(ci.name || ''))));

                for (const id in planets) {
                    if (!Object.prototype.hasOwnProperty.call(planets, id)) continue;
                    const p = planets[id];
                    const pName = (p.name || '').toLowerCase();
                    const pSlug = slugify(p.name || '');

                    const matchedByName = completedNames.indexOf(pName) !== -1;
                    const matchedBySlug = completedSlugs.indexOf(pSlug) !== -1;
                    p.status = (matchedByName || matchedBySlug) ? 'completed' : 'current';

                    p.activities.forEach(a => {
                        const aName = (a.name || '').toLowerCase();
                        const aSlugFromName = slugify(a.name || '');
                        const matchedAByName = completedNames.findIndex(g => g && (aName.includes(g) || g.includes(aName))) !== -1;
                        const matchedABySlug = completedSlugs.indexOf(aSlugFromName) !== -1;
                        a.status = (matchedAByName || matchedABySlug) ? 'completed' : 'current';
                    });

                    // If any activity is completed, mark the whole planet as completed
                    if (Array.isArray(p.activities) && p.activities.some(act => act.status === 'completed')) {
                        p.status = 'completed';
                    }
                }

                // Apply classes to planet DOM elements so CSS rules take effect
                document.querySelectorAll('.planet').forEach(el => {
                    const pid = el.getAttribute('data-planet');
                    const pdata = planets[pid];
                    if (!pdata) return;
                    el.classList.remove('completed', 'current', 'locked');
                    // add class matching status
                    if (pdata.status === 'completed') {
                        el.classList.add('completed');
                        // clear any dimming if present via attribute selectors
                        el.style.opacity = '';
                        el.style.filter = '';
                    } else if (pdata.status === 'current') {
                        el.classList.add('current');
                    } else {
                        el.classList.add('locked');
                    }
                });
                console.log('✅ Planet statuses updated from server');
            })
            .catch(err => {
                console.warn('⚠️ Could not load game statuses:', err);
            });
    } catch (e) {
        console.warn('⚠️ updatePlanetStatuses error:', e);
    }
})();

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
