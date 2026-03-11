const CACHE_NAME = 'stem-app-cache-v2';
// Danh sách các file cần lưu vào bộ nhớ đệm (Cache)
// Bạn hãy kiểm tra lại tên file trong thư mục public/CSS và public/JS của bạn

const urlsToCache = [
  '/SPNC_HocLieu_STEM_TieuHoc/public/CSS/style.css',     
  '/SPNC_HocLieu_STEM_TieuHoc/public/JS/script.js',      
  '/SPNC_HocLieu_STEM_TieuHoc/public/images/logo.png'
];

// 1. Cài đặt Service Worker và lưu Cache
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Đã mở cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// 2. Lấy dữ liệu: Ưu tiên lấy từ Cache, nếu không có mới tải từ mạng
self.addEventListener('fetch', (event) => {
  // Không cache các file PHP (động)
  if (event.request.url.includes('.php')) {
    event.respondWith(fetch(event.request));
    return;
  }
  
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Nếu tìm thấy trong cache thì trả về ngay
        if (response) {
          return response;
        }
        // Nếu không thì tải từ mạng
        return fetch(event.request);
      })
  );
});

// 3. Xóa Cache cũ khi cập nhật phiên bản mới
self.addEventListener('activate', (event) => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});