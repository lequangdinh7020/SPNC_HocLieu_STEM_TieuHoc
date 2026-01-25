console.log('technology.js loaded');
console.log('baseUrl(from DOM):', baseUrl, ' window.baseUrl:', window.baseUrl);

const planets = {
    1: {
        name: "CÂY GIA ĐÌNH",
        icon: "🌳",
        status: "not-started", // Đổi thành not-started
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
                status: "not-started" // Đổi thành not-started
            }
        ]
    },
    2: {
        name: "EM LÀ HỌA SĨ MÁY TÍNH",
        icon: "🎨",
        status: "not-started", // Đổi thành not-started
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
                status: "not-started" // Đổi thành not-started
            }
        ]
    },
    3: {
        name: "EM LÀ NGƯỜI ĐÁNH MÁY",
        icon: "⌨️",
        status: "not-started",
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
                status: "not-started" 
            }
        ]
    },
    4: {
        name: "SƠN TINH (LẬP TRÌNH KHỐI)",
        icon: "🧩",
        status: "not-started",
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
                status: "not-started" 
            }
        ]
    },
    5: {
        name: "CÁC BỘ PHẬN CỦA MÁY TÍNH",
        icon: "💻",
        status: "not-started",
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
                status: "not-started" 
            }
        ]
    }
};

// Storage key cho technology
const TECH_STORAGE_KEY = 'tech_planet_status';

// Load trạng thái từ session nếu có
if (window.techPlanetStatuses) {
    for (const id in planets) {
        if (window.techPlanetStatuses[id]) {
            planets[id].status = window.techPlanetStatuses[id];
            if (planets[id].status === 'current' || planets[id].status === 'completed') {
                planets[id].activities.forEach(act => {
                    act.status = planets[id].status;
                });
            }
        }
    }
}

// Hàm lưu tất cả trạng thái vào localStorage
function saveAllTechStatuses() {
    try {
        const statuses = {};
        for (const id in planets) {
            statuses[id] = planets[id].status;
        }
        localStorage.setItem(TECH_STORAGE_KEY, JSON.stringify(statuses));
        console.log('💾 All tech planet statuses saved to localStorage');
        return true;
    } catch (e) {
        console.error('❌ Error saving to localStorage:', e);
        return false;
    }
}

// Hàm load trạng thái từ localStorage
function loadTechStatuses() {
    try {
        const saved = localStorage.getItem(TECH_STORAGE_KEY);
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
            console.log('📥 Tech planet statuses loaded:', statuses);
        }
        updateTechPlanetDisplay();
    } catch (e) {
        console.warn('⚠️ Could not load from localStorage:', e);
        updateTechPlanetDisplay();
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
        console.log(`🔄 Marking tech planet ${planetId} as current...`);
        
        planet.status = 'current';
        planet.activities.forEach(act => {
            // Chỉ cập nhật nếu chưa hoàn thành
            if (act.status !== 'completed') {
                act.status = 'current';
            }
        });
        
        updateTechPlanetDisplay();
        saveAllTechStatuses();
        
        console.log(`✅ Tech planet ${planetId} marked as current`);
        return true;
    }
    
    return false;
}

// Hàm cập nhật hiển thị planet
function updateTechPlanetDisplay() {
    console.log('🔄 Updating tech planet display...');
    
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
            console.log(`🌍 Tech Planet ${pid}: COMPLETED ✓`);
        } else if (pdata.status === 'current') {
            el.classList.add('current');
            el.style.opacity = '';
            el.style.filter = '';
            console.log(`🌍 Tech Planet ${pid}: CURRENT ● (màu xanh dương)`);
        } else if (pdata.status === 'not-started') {
            el.classList.add('not-started');
            el.style.opacity = '0.5';
            el.style.filter = 'grayscale(0.7)';
            console.log(`🌍 Tech Planet ${pid}: NOT STARTED (dimmed)`);
        } else {
            el.classList.add('locked');
            console.log(`🌍 Tech Planet ${pid}: LOCKED`);
        }
    });
}

// Fetch completed games và cập nhật trạng thái
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
                    
                    // Chỉ cập nhật nếu là "not-started" và đã hoàn thành
                    if ((matchedByName || matchedBySlug) && p.status === 'not-started') {
                        p.status = 'completed';
                    }

                    p.activities.forEach(a => {
                        const aName = (a.name || '').toLowerCase();
                        const aSlugFromName = slugify(a.name || '');
                        const matchedAByName = completedNames.findIndex(g => g && (aName.includes(g) || g.includes(aName))) !== -1;
                        const matchedABySlug = completedSlugs.indexOf(aSlugFromName) !== -1;
                        
                        // Chỉ cập nhật nếu là "not-started" và đã hoàn thành
                        if ((matchedAByName || matchedABySlug) && a.status === 'not-started') {
                            a.status = 'completed';
                        }
                        
                        // Nếu activity hoàn thành và planet là not-started, chuyển planet sang current
                        if (a.status === 'completed' && p.status === 'not-started') {
                            p.status = 'current';
                        }
                    });

                    // If all activities are completed, mark the whole planet as completed
                    if (Array.isArray(p.activities) && p.activities.length > 0 && 
                        p.activities.every(act => act.status === 'completed')) {
                        p.status = 'completed';
                    }
                }
                
                console.log('✅ Tech planet statuses updated from server');
                updateTechPlanetDisplay();
            })
            .catch(err => {
                console.warn('⚠️ Could not load game statuses:', err);
                updateTechPlanetDisplay();
            });
    } catch (e) {
        console.warn('⚠️ updatePlanetStatuses error:', e);
        updateTechPlanetDisplay();
    }
})();

function initTechnologySystem() {
    console.log('🚀 Initializing Technology System...');
    
    // Load trạng thái từ localStorage
    loadTechStatuses();
    
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
            console.log(`🪐 Tech Planet clicked: ${planetId}`);
            
            currentPlanetId = planetId;
            const currentPlanetData = planets[planetId];
            
            if (!currentPlanetData) {
                console.error('❌ Không tìm thấy dữ liệu cho planet:', planetId);
                return;
            }
            
            // QUAN TRỌNG: Đánh dấu là current NGAY KHI CLICK (nếu là not-started)
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
                
                // Tất cả hoạt động đều có thể click (kể cả not-started)
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
                        <div class="activity-type">${activity.type === 'game' ? 'Trò chơi' : 
                                                     activity.type === 'video' ? 'Video' : 
                                                     activity.type === 'tutorial' ? 'Hướng dẫn' : 
                                                     activity.type === 'share' ? 'Chia sẻ' : 'Câu hỏi'}</div>
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
            console.log('📱 Tech Info panel shown');
            
            // Thông báo nếu đã chuyển trạng thái
            if (wasMarked) {
                console.log(`🌟 Tech Planet ${planetId} is now marked as "đang học" (màu xanh dương)`);
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
        console.log('📱 Tech Info panel closed');
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

    // Tự động lưu mỗi 5 giây
    setInterval(saveAllTechStatuses, 5000);

    console.log('🎉 Technology System initialized successfully!');
    return true;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTechnologySystem);
} else {
    initTechnologySystem();
}