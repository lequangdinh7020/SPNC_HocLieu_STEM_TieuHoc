const BASE_URL = window.location.origin + '/SPNC_HocLieu_STEM_TieuHoc';
const basePath = BASE_URL + '/public/images/certificate';

const CERT_IMAGES = {
    'Khoa học':  basePath + '/certificateScience.png',
    'Công nghệ': basePath + '/certificateTechnology.png',
    'Kỹ thuật':  basePath + '/certificateEngineering.png',
    'Toán học':  basePath + '/certificateMath.png',
};

const TOPIC_ICONS = {
    'Khoa học':  '🔬',
    'Công nghệ': '💻',
    'Kỹ thuật':  '⚙️',
    'Toán học':  '📐',
};

let topicsData   = [];
let currentTopic = null;
let studentName  = '';

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch (e) { return dateStr; }
}

function showToast(message, type, duration) {
    type     = type     || 'info';
    duration = duration || 3500;
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        document.body.appendChild(container);
    }
    const icons = { error: '✖', warning: '⚠', success: '✔', info: 'ℹ' };
    const t = document.createElement('div');
    t.className = 'toast-notification ' + type;
    t.innerHTML = '<div class="toast-icon">' + (icons[type] || 'ℹ') + '</div>' +
                  '<div class="toast-message">' + escapeHtml(message) + '</div>';
    container.appendChild(t);
    requestAnimationFrame(function() { t.classList.add('show'); });
    setTimeout(function() {
        t.classList.remove('show');
        t.addEventListener('transitionend', function() { t.remove(); }, { once: true });
    }, duration);
}

var topicIndex = 0;
var VISIBLE = 3; 

function renderTopics(topics) {
    const track = document.getElementById('topicsGrid');
    if (!track) return;

    if (!topics || topics.length === 0) {
        track.innerHTML = '<div class="topic-empty">Chưa có dữ liệu lĩnh vực.</div>';
        hideDots();
        return;
    }

    track.innerHTML = topics.map(function(t) { return buildTopicCard(t); }).join('');

    buildDots(topics.length);
    goToTopic(topicIndex, false);
}

function buildDots(count) {
    const dotsEl = document.getElementById('topicsDots');
    if (!dotsEl) return;
    dotsEl.innerHTML = '';
    var dotCount = Math.max(1, count - VISIBLE + 1);
    if (dotCount <= 1) return; 
    for (var i = 0; i < dotCount; i++) {
        var d = document.createElement('div');
        d.className = 'topics-dot' + (i === topicIndex ? ' active' : '');
        d.dataset.idx = i;
        d.addEventListener('click', (function(idx) {
            return function() { goToTopic(idx); };
        })(i));
        dotsEl.appendChild(d);
    }
}

function hideDots() {
    var dotsEl = document.getElementById('topicsDots');
    if (dotsEl) dotsEl.innerHTML = '';
}

function goToTopic(idx, animate) {
    var track = document.getElementById('topicsGrid');
    if (!track) return;
    var cards = track.querySelectorAll('.topic-card');
    if (!cards.length) return;

    var gap = 25;
    var viewportW = track.parentElement ? track.parentElement.offsetWidth : 0;
    var cardW = Math.floor((viewportW - (VISIBLE - 1) * gap) / VISIBLE);

    Array.prototype.forEach.call(cards, function(c) {
        c.style.width = cardW + 'px';
    });

    var maxIdx = Math.max(0, cards.length - VISIBLE);
    topicIndex = Math.max(0, Math.min(idx, maxIdx));

    var offset = topicIndex * (cardW + gap);

    if (animate === false) {
        track.style.transition = 'none';
        track.style.transform  = 'translateX(-' + offset + 'px)';
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                track.style.transition = '';
            });
        });
    } else {
        track.style.transform = 'translateX(-' + offset + 'px)';
    }

    var dots = document.querySelectorAll('.topics-dot');
    dots.forEach(function(d, i) { d.classList.toggle('active', i === topicIndex); });

    var prevBtn = document.getElementById('topicPrev');
    var nextBtn = document.getElementById('topicNext');
    if (prevBtn) prevBtn.classList.toggle('disabled', topicIndex === 0);
    if (nextBtn) nextBtn.classList.toggle('disabled', topicIndex >= maxIdx);
}

function shiftTopic(dir) {
    goToTopic(topicIndex + dir);
}

window.addEventListener('resize', function() {
    var track = document.getElementById('topicsGrid');
    if (track && track.querySelectorAll('.topic-card').length) {
        goToTopic(topicIndex, false);
    }
});

function buildTopicCard(t) {
    const icon  = TOPIC_ICONS[t.topic_name] || '📚';
    const pct   = t.completion_percent;
    const barColor = t.eligible ? '#22c55e' : (pct >= 60 ? '#f59e0b' : '#ef4444');
    const progressLabel = t.completed_games + '/' + t.total_games + ' bài học (' + pct + '%)';

    let statusHtml = '';
    let actionHtml = '';

    if (!t.eligible) {
        statusHtml = '<div class="topic-status locked">' +
            '🔒 Cần hoàn thành thêm <strong>' + t.games_needed + '</strong> bài học nữa' +
            '</div>';
        actionHtml = '<div class="cert-locked-msg">' +
            'Hoàn thành <strong>' + t.games_needed + '</strong> bài học nữa để mở khóa chứng nhận' +
            '</div>';
    } else {
        const certBadge = t.has_certificate
            ? '<span class="cert-badge earned">✅ Đã nhận chứng nhận (' + formatDate(t.certificate_issued_at) + ')</span>'
            : '<span class="cert-badge eligible">🏆 Đủ điều kiện nhận chứng nhận!</span>';
        statusHtml = '<div class="topic-status eligible">' + certBadge + '</div>';
        actionHtml =
            '<div class="cert-form" id="certForm_' + t.topic_id + '">' +
                '<div class="cert-form-actions">' +
                    '<button class="btn-create-cert" onclick="openCertModal(' + t.topic_id + ')">' +
                        '<i class="fas fa-certificate"></i> Tạo chứng nhận' +
                    '</button>' +
                '</div>' +
            '</div>';
    }

    return '<div class="topic-card ' + (t.eligible ? 'eligible' : 'locked') + '" data-topic-id="' + t.topic_id + '">' +
        '<div class="topic-card-header">' +
            '<span class="topic-icon">' + icon + '</span>' +
            '<div class="topic-info">' +
                '<h3 class="topic-name">' + escapeHtml(t.topic_name) + '</h3>' +
                '<div class="topic-progress-label">' + progressLabel + '</div>' +
            '</div>' +
        '</div>' +
        '<div class="topic-progress-bar-wrap">' +
            '<div class="topic-progress-bar" style="width:' + pct + '%; background:' + barColor + '"></div>' +
        '</div>' +
        statusHtml +
        actionHtml +
    '</div>';
}

async function openCertModal(topicId) {
    const topic = topicsData.find(function(t) { return t.topic_id === topicId; });
    if (!topic) return;

    const name = studentName || 'Học Viên';

    currentTopic = topic;

    const modal      = document.getElementById('certModal');
    const modalTitle = document.getElementById('certModalTitle');

    if (modalTitle) modalTitle.textContent = 'Chứng nhận - ' + topic.topic_name;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    await drawCertificate(topic, name);
}

function closeCertModal() {
    const modal = document.getElementById('certModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    currentTopic = null;
}

function loadImage(url) {
    return new Promise(function(resolve, reject) {
        const img  = new Image();
        img.onload  = function() { resolve(img); };
        img.onerror = function() { reject(new Error('Cannot load: ' + url)); };
        img.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_t=' + Date.now();
    });
}

async function drawCertificate(topic, name) {
    const canvas  = document.getElementById('certCanvas');
    const loading = document.getElementById('certLoading');
    if (!canvas) return;

    if (loading) loading.style.display = 'flex';
    canvas.style.display = 'none';

    const imgUrl = CERT_IMAGES[topic.topic_name];
    if (!imgUrl) {
        showToast('Không tìm thấy mẫu chứng nhận', 'error');
        if (loading) loading.style.display = 'none';
        return;
    }

    try {
        const img    = await loadImage(imgUrl);
        canvas.width  = img.naturalWidth  || 1200;
        canvas.height = img.naturalHeight || 840;

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);

        const fontSize = Math.max(44, Math.floor(canvas.width * 0.052));
        const fontSpec = fontSize + 'px "Amoresa", "Dancing Script", cursive';

        try {
            const promises = [];
            if (!document._amorosaLoaded) {
                const face = new FontFace('Amoresa',
                    'url(' + BASE_URL + '/public/fonts/Amoresa.otf) format("opentype")');
                promises.push(face.load().then(function(f) {
                    document.fonts.add(f);
                    document._amorosaLoaded = true;
                }));
            }
            if (!document._dancingLoaded) {
                const viet = 'àáâãèéêìíòóôõùúăđơưạảấầẩẫậắằẳẵặẹẻẽếềểễệ';
                promises.push(document.fonts.load(fontSize + 'px "Dancing Script"', name + viet).then(function() {
                    document._dancingLoaded = true;
                }));
            }
            if (promises.length) await Promise.all(promises);
        } catch (fontErr) {
            console.warn('Font load warning:', fontErr);
        }

        ctx.font = fontSpec;

        const topicColors = {
            'Khoa học':  '#6ee7b7', 
            'Công nghệ': '#93c5fd', 
            'Kỹ thuật':  '#fde68a', 
            'Toán học':  '#c4b5fd', 
        };
        ctx.fillStyle    = topicColors[topic.topic_name] || '#ffffff';
        ctx.textAlign    = 'center';
        ctx.textBaseline = 'middle';

        ctx.shadowColor   = 'rgba(255,255,255,0.85)';
        ctx.shadowBlur    = 8;
        ctx.shadowOffsetX = 2;
        ctx.shadowOffsetY = 2;

        ctx.fillText(name, canvas.width / 2, canvas.height * 0.65);

        ctx.shadowColor = 'transparent';
        ctx.shadowBlur  = 0;

        canvas.style.display = 'block';
        if (loading) loading.style.display = 'none';

        await issueCertificate(topic.topic_id);

    } catch (err) {
        console.error('drawCertificate error:', err);
        showToast('Không thể tạo chứng nhận. Vui lòng thử lại.', 'error');
        if (loading) loading.style.display = 'none';
    }
}

async function issueCertificate(topicId) {
    try {
        var issueUrl = (typeof ISSUE_CERT_URL !== 'undefined')
            ? ISSUE_CERT_URL
            : BASE_URL + '/api/issue-certificate';
        const res  = await fetch(issueUrl, {
            method:      'POST',
            headers:     { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body:        JSON.stringify({ topic_id: topicId }),
        });
        const data = await res.json();
        if (data.success && !data.already_existed) {
            showToast('Chứng nhận đã được ghi nhận! 🎉', 'success');
            const topic = topicsData.find(function(t) { return t.topic_id === topicId; });
            if (topic) {
                topic.has_certificate       = true;
                topic.certificate_issued_at = new Date().toISOString();
                renderTopics(topicsData);
            }
        }
    } catch (e) {
        console.warn('issueCertificate error:', e);
    }
}

function downloadCanvas() {
    const canvas = document.getElementById('certCanvas');
    if (!canvas || canvas.style.display === 'none') {
        showToast('Vui lòng tạo chứng nhận trước khi tải xuống', 'warning');
        return;
    }

    const input    = document.getElementById('modalNameInput');
    const name     = input ? (input.value.trim() || 'hocsinh') : 'hocsinh';
    const safeName = name.replace(/\s+/g, '_');
    const safeTopic = currentTopic
        ? currentTopic.topic_name.replace(/[^a-zA-Z0-9]/g, '_')
        : 'chung_nhan';

    try {
        canvas.toBlob(function(blob) {
            if (!blob) { showToast('Không thể xuất ảnh', 'error'); return; }
            const a    = document.createElement('a');
            a.href     = URL.createObjectURL(blob);
            a.download = 'chung_nhan_' + safeTopic + '_' + safeName + '.png';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(a.href);
            showToast('Đã tải xuống chứng nhận!', 'success');
        }, 'image/png');
    } catch (e) {
        console.error('downloadCanvas error:', e);
        showToast('Không thể tải xuống. Thử lại sau.', 'error');
    }
}

window.addEventListener('load', function() {
    const grid = document.getElementById('topicsGrid');

    if (typeof achievementsData === 'undefined') {
        if (grid) grid.innerHTML = '<div class="topic-empty">Không thể tải dữ liệu trang. Vui lòng tải lại.</div>';
        return;
    }

    if (!achievementsData.loggedIn) {
        if (grid) {
            grid.innerHTML =
                '<div class="topic-empty">' +
                    '<a href="' + (typeof baseUrl !== 'undefined' ? baseUrl : '') + '/views/signin.php" style="color:#60a5fa">Đăng nhập</a>' +
                    ' để xem tiến độ và nhận chứng nhận của bạn.' +
                '</div>';
        }
        return;
    }

    studentName = achievementsData.studentName || '';
    topicsData  = achievementsData.topics      || [];

    renderTopics(topicsData);

    const modal = document.getElementById('certModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeCertModal();
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeCertModal();
    });
});