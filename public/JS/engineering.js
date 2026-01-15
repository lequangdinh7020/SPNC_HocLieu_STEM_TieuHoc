console.log('engineering.js loaded');
console.log('baseUrl(from DOM):', baseUrl, ' window.baseUrl:', window.baseUrl);

const planets = {
    1: {
        name: "XÂY THÁP (CÂY TRE TRĂM ĐỐT)",
        icon: "🎋",
        status: "completed",
        description: "Học cách xây tháp vững chắc từ câu chuyện Cây tre trăm đốt",
        time: "25 phút",
        xp: "20 XP",
        activities: [
            { 
                type: "tutorial", 
                name: "Thử thách xây tháp", 
                icon: "🏗️", 
                xp: "20 XP", 
                link: baseUrl + '/views/lessons/engineering_tower_game', 
                status: "completed" 
            }
        ]
    },
    2: {
        name: "SẮP XẾP CĂN PHÒNG CỦA EM",
        icon: "🏠",
        status: "current",
        description: "Thiết kế và sắp xếp không gian sống gọn gàng, hợp lý",
        time: "30 phút",
        xp: "20 XP",
        activities: [
            { 
                type: "tutorial", 
                name: "Thiết kế không gian", 
                icon: "🎨", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/engineering_room_decor', 
                status: "current" 
            }
        ]
    },
    3: {
        name: "XÂY CẦU", 
        icon: "🌉",
        status: "current",
        description: "Thiết kế và xây dựng cầu từ giấy A4 chịu lực",
        time: "35 phút", 
        xp: "20 XP",
        activities: [
            { 
                type: "challenge", 
                name: "Thử thách cầu giấy", 
                icon: "🌉", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/engineering_bridge_game', 
                status: "current" 
            }
        ]
    },
    4: {
        name: "HỆ THỐNG DẪN NƯỚC",
        icon: "🚰",
        status: "current",
        description: "Tìm hiểu và thiết kế hệ thống dẫn nước đơn giản",
        time: "28 phút",
        xp: "20 XP",
        activities: [
            { 
                type: "experiment", 
                name: "Trò chơi dẫn nước", 
                icon: "🧪", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/engineering_water_pipe', 
                status: "current" 
            }
        ]
    },
    5: {
        name: "HỆ THỐNG LỌC NƯỚC CƠ BẢN",
        icon: "💧",
        status: "current",
        description: "Tìm hiểu và chế tạo hệ thống lọc nước đơn giản từ vật liệu dễ kiếm",
        time: "40 phút",
        xp: "20 XP",
        activities: [
            { 
                type: "experiment", 
                name: "Chế tạo bộ lọc", 
                icon: "🧪", 
                xp: "20 XP",
                link: baseUrl + '/views/lessons/engineering_water_filter', 
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

                    // JS slugify (remove diacritics, replace non-alnum with hyphens)
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
                            // attempt match by name or slug
                            const matchedAByName = completedNames.findIndex(g => g && (aName.includes(g) || g.includes(aName))) !== -1;
                            const matchedABySlug = completedSlugs.indexOf(aSlugFromName) !== -1;
                            a.status = (matchedAByName || matchedABySlug) ? 'completed' : 'current';
                        });

                        // If any activity is completed, mark the whole planet as completed
                        if (Array.isArray(p.activities) && p.activities.some(act => act.status === 'completed')) {
                            p.status = 'completed';
                        }
                    }
                console.log('✅ Planet statuses updated from server');

                // Apply classes to planet DOM elements so CSS rules take effect
                document.querySelectorAll('.planet').forEach(el => {
                    const pid = el.getAttribute('data-planet');
                    const pdata = planets[pid];
                    if (!pdata) return;
                    el.classList.remove('completed', 'current', 'locked');
                    if (pdata.status === 'completed') {
                        el.classList.add('completed');
                        el.style.opacity = '';
                        el.style.filter = '';
                    } else if (pdata.status === 'current') {
                        el.classList.add('current');
                    } else {
                        el.classList.add('locked');
                    }
                });
            })
            .catch(err => {
                console.warn('⚠️ Could not load game statuses:', err);
            });
    } catch (e) {
        console.warn('⚠️ updatePlanetStatuses error:', e);
    }
})();
function initEngineeringSystem() {
    console.log('🚀 Initializing Engineering System...');
    
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
                } else if (activity.status === 'upcoming') {
                    activityElement.classList.add('activity-locked');
                }
                
                if (activity.link && activity.status !== 'upcoming') {
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
                } else if (activity.status === 'upcoming') {
                    statusBadge = '<div class="activity-status-badge locked-badge">🔒</div>';
                }
                
                activityElement.innerHTML = `
                    ${statusBadge}
                    <div class="activity-icon">${activity.icon}</div>
                    <div class="activity-info">
                        <div class="activity-name">${activity.name}</div>
                        <div class="activity-type">${
                            activity.type === 'tutorial' ? 'Hướng dẫn' : 
                            activity.type === 'challenge' ? 'Thử thách' : 
                            activity.type === 'experiment' ? 'Thí nghiệm' : 
                            activity.type === 'competition' ? 'Cuộc thi' : 
                            activity.type === 'craft' ? 'Thủ công' : 'Câu hỏi'
                        }</div>
                    </div>
                    <div class="activity-xp">${activity.xp}</div>
                `;
                
                if (activity.link && activity.status !== 'upcoming') {
                    activityElement.addEventListener('click', function(e) {
                        e.stopPropagation();
                        console.log(`🔧 Navigating to: ${activity.link}`);
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
        console.log('👷‍♂️ Character clicked');
        alert('Chào nhà kỹ sư nhí! Mình là Thợ Máy Thông Thái! 👷‍♂️\nCùng mình chế tạo 5 dự án siêu thú vị nhé!');
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

    console.log('🎉 Engineering System initialized successfully!');
    return true;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEngineeringSystem);
} else {
    initEngineeringSystem();
}