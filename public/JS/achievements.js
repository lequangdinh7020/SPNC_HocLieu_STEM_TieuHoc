const basePath = window.location.origin + '/SPNC_HocLieu_STEM_TieuHoc/public/images/certificate';

// Base certificate templates (static artwork + titles)
const baseCertificates = [
    { title: "CHỨNG NHẬN", subtitle: "HOÀN THÀNH", topic: "Khoa học", background: `${basePath}/certificateScience.png`, signatures: [{ name: "HANNAH MORALES", title: "HIỆU TRƯỞNG" }, { name: "LARS PETERS", title: "GIÁO VIÊN" }] },
    { title: "CHỨNG NHẬN", subtitle: "HOÀN THÀNH XUẤT SẮC", topic: "Công nghệ", background: `${basePath}/certificateTechnology.png`, signatures: [{ name: "HANNAH MORALES", title: "HIỆU TRƯỞNG" }, { name: "LARS PETERS", title: "GIÁO VIÊN" }] },
    { title: "CHỨNG NHẬN", subtitle: "NHÀ SÁNG TẠO TÀI NĂNG", topic: "Kỹ thuật", background: `${basePath}/certificateEngineering.png`, signatures: [{ name: "HANNAH MORALES", title: "HIỆU TRƯỞNG" }, { name: "LARS PETERS", title: "GIÁO VIÊN" }] },
    { title: "CHỨNG NHẬN", subtitle: "NHÀ TOÁN HỌC TƯƠNG LAI", topic: "Toán học", background: `${basePath}/certificateMath.png`, signatures: [{ name: "HANNAH MORALES", title: "HIỆU TRƯỞNG" }, { name: "LARS PETERS", title: "GIÁO VIÊN" }] }
];

// Will be populated from server and combined with baseCertificates
let certificates = [];

let currentCertificateIndex = 0;

async function imageExists(url) {
    try {
        const r = await fetch(url, { method: 'HEAD' });
        console.log('HEAD', url, r.status);
        return r.ok;
    } catch (e) {
        console.error('fetch HEAD error', url, e);
        return false;
    }
}

async function updateCertificate() {
    const cert = certificates[currentCertificateIndex];
    const certificateElement = document.getElementById('currentCertificate');

    console.log('Updating certificate, attempted bg:', cert.background);
    console.log('element exists:', !!certificateElement);

    certificateElement.className = 'certificate-paper';
    certificateElement.style.minHeight = '420px';
    certificateElement.style.minWidth = '600px';
    certificateElement.style.position = 'relative';
    certificateElement.style.zIndex = '1';

    const ok = await imageExists(cert.background);
    if (!ok) {
        console.warn('Image not reachable:', cert.background);
        const alt = cert.background.replace('/SPNC_HocLieu_STEM_TieuHoc/SPNC_HocLieu_STEM_TieuHoc', '/SPNC_HocLieu_STEM_TieuHoc');
        console.log('Trying alternative path:', alt);
        certificateElement.style.setProperty('background-image', `url("${alt}")`, 'important');
    } else {
        certificateElement.style.setProperty('background-image', `url("${cert.background}")`, 'important');
    }

    certificateElement.style.setProperty('background-size', 'cover', 'important');
    certificateElement.style.setProperty('background-position', 'center', 'important');
    certificateElement.style.removeProperty('background-repeat');

    certificateElement.innerHTML = `
        <div class="certificate-content" style="position:absolute; z-index:2; background:transparent; top:50%; left:50%; transform:translate(-50%, -50%); width:90%; display:flex; align-items:center; justify-content:center;">
            <h3 class="student-name">${cert.student}</h3>
        </div>
    `;

    const cs = window.getComputedStyle(certificateElement);
    console.log('computed background-image:', cs.backgroundImage);
    console.log('width x height:', certificateElement.offsetWidth, 'x', certificateElement.offsetHeight);

    document.querySelector('.certificate-nav.prev').classList.toggle('disabled', currentCertificateIndex === 0);
    document.querySelector('.certificate-nav.next').classList.toggle('disabled', currentCertificateIndex === certificates.length - 1);
    // Toggle download/share buttons by ownership
    const downloadBtn = document.querySelector('.download-btn');
    const shareBtn = document.querySelector('.share-btn');
    if (downloadBtn) {
        downloadBtn.classList.toggle('disabled', !(cert && cert.owned));
        downloadBtn.setAttribute('aria-disabled', cert && cert.owned ? 'false' : 'true');
    }
    if (shareBtn) {
        shareBtn.classList.toggle('disabled', !(cert && cert.owned));
        shareBtn.setAttribute('aria-disabled', cert && cert.owned ? 'false' : 'true');
    }
}

function changeCertificate(direction) {
    const newIndex = currentCertificateIndex + direction;
    if (newIndex >= 0 && newIndex < certificates.length) {
        currentCertificateIndex = newIndex;
        updateCertificate();
    }
}

async function downloadCertificate() {
    console.log('downloadCertificate called, currentIndex=', currentCertificateIndex);
    const cert = certificates[currentCertificateIndex];
    if (!cert || !cert.owned) {
        showToast('Bạn chưa có chứng nhận này. Hoàn thành chủ đề để tải xuống.', 'warning');
        return;
    }

    // download background image as file
    try {
        const url = cert.background;
        const res = await fetch(url);
        if (!res.ok) throw new Error('image fetch failed');
        const blob = await res.blob();
        const a = document.createElement('a');
        const objUrl = URL.createObjectURL(blob);
        a.href = objUrl;
        const safeTopic = cert.topic.replace(/[^a-z0-9\-]/gi, '_');
        a.download = `certificate_${safeTopic}.png`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(objUrl);
    } catch (e) {
        console.error('Download failed', e);
        showToast('Không thể tải chứng nhận lúc này.', 'error');
    }
}

function shareCertificate() {
    console.log('shareCertificate called, currentIndex=', currentCertificateIndex);
    const cert = certificates[currentCertificateIndex];
    if (!cert || !cert.owned) {
        showToast('Bạn chưa có chứng nhận này. Hoàn thành chủ đề để chia sẻ.', 'warning');
        return;
    }
    showToast('Chia sẻ chứng nhận...', 'success');
}

// In-page toast notification
function ensureToastContainer() {
    let c = document.getElementById('toastContainer');
    if (!c) {
        c = document.createElement('div');
        c.id = 'toastContainer';
        document.body.appendChild(c);
    }
    return c;
}

function showToast(message, type = 'info', duration = 3500) {
    const container = ensureToastContainer();
    const t = document.createElement('div');
    t.className = `toast-notification ${type}`;
    t.innerHTML = `<div class="toast-icon">${type === 'error' ? '✖' : type === 'warning' ? '⚠' : type === 'success' ? '✔' : 'ℹ'}</div><div class="toast-message">${message}</div>`;
    container.appendChild(t);
    // trigger show
    requestAnimationFrame(() => t.classList.add('show'));
    // remove later
    setTimeout(() => {
        t.classList.remove('show');
        t.addEventListener('transitionend', () => t.remove(), { once: true });
    }, duration);
}

// Fetch user certificates and student info, then initialize
window.addEventListener('load', async () => {
    try {
        const resp = await fetch((typeof baseUrl !== 'undefined' ? baseUrl : '') + '/public/api/get_user_certificates.php', { credentials: 'same-origin' });
        const data = await resp.json();
        const ownedTopics = [];
        let studentName = '';
        if (data && data.success) {
            studentName = data.student || '';
            (data.certificates || []).forEach(c => {
                if (c.topic_name) ownedTopics.push(c.topic_name);
            });
        }

        // Build certificates array: add student name and owned flag
        certificates = baseCertificates.map(b => ({
            ...b,
            student: studentName || 'Học sinh',
            owned: ownedTopics.indexOf(b.topic) !== -1
        }));

        // If user has no certificates, still show templates but disabled for download
        updateCertificate();

        // Ensure buttons are wired (some environments rely on event listeners rather than inline onclick)
        try {
            const dl = document.querySelector('.download-btn');
            const sh = document.querySelector('.share-btn');
            if (dl) {
                dl.addEventListener('click', (e) => { e.preventDefault(); downloadCertificate(); });
            }
            if (sh) {
                sh.addEventListener('click', (e) => { e.preventDefault(); shareCertificate(); });
            }
        } catch (e) {
            console.warn('Could not attach button listeners', e);
        }
    } catch (e) {
        console.warn('Could not load user certificates', e);
        // fallback to baseCertificates with unknown student
        certificates = baseCertificates.map(b => ({ ...b, student: 'Học sinh', owned: false }));
        updateCertificate();
    }
});