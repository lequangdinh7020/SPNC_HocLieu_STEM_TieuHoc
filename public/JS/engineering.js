console.log('engineering.js loaded');
console.log('baseUrl(from DOM):', baseUrl, ' window.baseUrl:', window.baseUrl);

const planets = {
    1: {
        name: "XÂY THÁP (CÂY TRE TRĂM ĐỐT)",
        icon: "🎋",
        status: "not-started", 
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
                status: "not-started" 
            }
        ]
    },
    2: {
        name: "SẮP XẾP CĂN PHÒNG CỦA EM",
        icon: "🏠",
        status: "not-started", 
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
                status: "not-started" 
            }
        ]
    },
    3: {
        name: "XÂY CẦU", 
        icon: "🌉",
        status: "not-started",
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
                status: "not-started" 
            }
        ]
    },
    4: {
        name: "HỆ THỐNG DẪN NƯỚC",
        icon: "🚰",
        status: "not-started",
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
                status: "not-started" 
            }
        ]
    },
    5: {
        name: "HỆ THỐNG LỌC NƯỚC CƠ BẢN",
        icon: "💧",
        status: "not-started",
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
                status: "not-started" 
            }
        ]
    }
};

const ENG_STORAGE_KEY = 'eng_planet_status';

if (window.engPlanetStatuses) {
    for (const id in planets) {
        if (window.engPlanetStatuses[id]) {
            planets[id].status = window.engPlanetStatuses[id];
            if (planets[id].status === 'current' || planets[id].status === 'completed') {
                planets[id].activities.forEach(act => {
                    act.status = planets[id].status;
                });
            }
        }
    }
}

function saveAllEngStatuses() {
    try {
        const statuses = {};
        for (const id in planets) {
            statuses[id] = planets[id].status;
        }
        localStorage.setItem(ENG_STORAGE_KEY, JSON.stringify(statuses));
        console.log('💾 All eng planet statuses saved to localStorage');
        return true;
    } catch (e) {
        console.error('❌ Error saving to localStorage:', e);
        return false;
    }
}

function loadEngStatuses() {
    try {
        const saved = localStorage.getItem(ENG_STORAGE_KEY);
        if (saved) {
            const statuses = JSON.parse(saved);
            for (const id in statuses) {
                if (planets[id]) {
                    planets[id].status = statuses[id];
                    planets[id].activities.forEach(act => {
                        act.status = statuses[id];
                    });
                }
            }
            console.log('📥 Eng planet statuses loaded:', statuses);
        }
        updateEngPlanetDisplay();
    } catch (e) {
        console.warn('⚠️ Could not load from localStorage:', e);
        updateEngPlanetDisplay();
    }
}

function markPlanetAsCurrent(planetId) {
    const planet = planets[planetId];
    if (!planet) {
        console.error(`❌ Planet ${planetId} not found`);
        return false;
    }
    
    if (planet.status === 'not-started') {
        console.log(`🔄 Marking eng planet ${planetId} as current...`);
        
        planet.status = 'current';
        planet.activities.forEach(act => {
            if (act.status !== 'completed') {
                act.status = 'current';
            }
        });
        
        updateEngPlanetDisplay();
        saveAllEngStatuses();
        
        console.log(`✅ Eng planet ${planetId} marked as current (màu cam)`);
        return true;
    }
    
    return false;
}

function updateEngPlanetDisplay() {
    console.log('🔄 Updating eng planet display...');
    
    document.querySelectorAll('.planet').forEach(el => {
        const pid = el.getAttribute('data-planet');
        const pdata = planets[pid];
        if (!pdata) return;
        
        el.classList.remove('completed', 'current', 'not-started', 'locked');
        
        if (pdata.status === 'completed') {
            el.classList.add('completed');
            el.style.opacity = '';
            el.style.filter = '';
            console.log(`🌍 Eng Planet ${pid}: COMPLETED ✓`);
        } else if (pdata.status === 'current') {
            el.classList.add('current');
            el.style.opacity = '';
            el.style.filter = '';
            console.log(`🌍 Eng Planet ${pid}: CURRENT ● (màu cam)`);
        } else if (pdata.status === 'not-started') {
            el.classList.add('not-started');
            el.style.opacity = '0.5';
            el.style.filter = 'grayscale(0.7)';
            console.log(`🌍 Eng Planet ${pid}: NOT STARTED (dimmed)`);
        } else {
            el.classList.add('locked');
            console.log(`🌍 Eng Planet ${pid}: LOCKED`);
        }
    });
}

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
                    
                    if ((matchedByName || matchedBySlug) && p.status === 'not-started') {
                        p.status = 'completed';
                    }

                    p.activities.forEach(a => {
                        const aName = (a.name || '').toLowerCase();
                        const aSlugFromName = slugify(a.name || '');
                        const matchedAByName = completedNames.findIndex(g => g && (aName.includes(g) || g.includes(aName))) !== -1;
                        const matchedABySlug = completedSlugs.indexOf(aSlugFromName) !== -1;
                        
                        if ((matchedAByName || matchedABySlug) && a.status === 'not-started') {
                            a.status = 'completed';
                        }
                        
                        if (a.status === 'completed' && p.status === 'not-started') {
                            p.status = 'current';
                        }
                    });

                    if (Array.isArray(p.activities) && p.activities.length > 0 && 
                        p.activities.every(act => act.status === 'completed')) {
                        p.status = 'completed';
                    }
                }
                
                console.log('✅ Eng planet statuses updated from server');
                updateEngPlanetDisplay();
            })
            .catch(err => {
                console.warn('⚠️ Could not load game statuses:', err);
                updateEngPlanetDisplay();
            });
    } catch (e) {
        console.warn('⚠️ updatePlanetStatuses error:', e);
        updateEngPlanetDisplay();
    }
})();

function initEngineeringSystem() {
    console.log('🚀 Initializing Engineering System...');
    
    loadEngStatuses();
    
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

    let currentPlanetId = null;

    document.querySelectorAll('.planet').forEach(planet => {
        planet.addEventListener('click', function() {
            const planetId = this.getAttribute('data-planet');
            console.log(`🪐 Eng Planet clicked: ${planetId}`);
            
            currentPlanetId = planetId;
            const currentPlanetData = planets[planetId];
            
            if (!currentPlanetData) {
                console.error('❌ Không tìm thấy dữ liệu cho planet:', planetId);
                return;
            }
            
            const wasMarked = markPlanetAsCurrent(planetId);
            
            infoIcon.textContent = currentPlanetData.icon;
            infoName.textContent = currentPlanetData.name;
            infoDescription.textContent = currentPlanetData.description;
            
            let statusText = '';
            let statusClass = '';
            
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
                
                if (activity.link) {
                    activityElement.addEventListener('click', function(e) {
                        e.stopPropagation();
                        console.log(`🔧 Navigating to: ${activity.link}`);
                        window.location.href = activity.link;
                    });
                }
                
                activitiesGrid.appendChild(activityElement);
            });

            planetInfoOverlay.classList.add('show');
            console.log('📱 Eng Info panel shown');
            
            if (wasMarked) {
                console.log(`🌟 Eng Planet ${planetId} is now marked as "đang học" (màu cam)`);
            }
            
            this.style.transform = 'scale(1.3)';
            setTimeout(() => {
                this.style.transform = '';
            }, 300);
        });
    });

    function closeInfoPanel() {
        planetInfoOverlay.classList.remove('show');
        console.log('📱 Eng Info panel closed');
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

    setInterval(saveAllEngStatuses, 5000);

    console.log('🎉 Engineering System initialized successfully!');
    return true;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEngineeringSystem);
} else {
    initEngineeringSystem();
}