const CACHE_NAME = 'stem-app-cache-v3';
// Danh sách các file cần lưu vào bộ nhớ đệm (Cache)
// Bạn hãy kiểm tra lại tên file trong thư mục public/CSS và public/JS của bạn

const urlsToCache = [
  '/SPNC_HocLieu_STEM_TieuHoc/public/CSS/style.css',     
  '/SPNC_HocLieu_STEM_TieuHoc/public/images/logo.png'
];

// 1. Cài đặt Service Worker và lưu Cache
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Đã mở cache');
        return Promise.all(
          urlsToCache.map((url) =>
            cache.add(url).catch((err) => {
              console.warn('Bỏ qua file cache lỗi:', url, err);
            })
          )
        );
      })
  );
});

// 2. Lấy dữ liệu: Ưu tiên lấy từ Cache, nếu không có mới tải từ mạng
self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  // Chỉ xử lý cache cho request http/https. Bỏ qua chrome-extension, data, blob...
  const requestUrl = new URL(event.request.url);
  if (requestUrl.protocol !== 'http:' && requestUrl.protocol !== 'https:') {
    return;
  }

  // Với điều hướng trang (HTML), ưu tiên lấy từ mạng để tránh lặp màn do cache trang động.
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => caches.match(event.request))
    );
    return;
  }

  // Bỏ qua cache cho URL có query params (vd: ?next=1&points=10&xp=5)
  if (requestUrl.search) {
    event.respondWith(fetch(event.request));
    return;
  }

  // Không cache các file PHP (động)
  if (event.request.url.includes('.php')) {
    event.respondWith(
      fetch(event.request).catch(() => caches.match(event.request))
    );
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
        return fetch(event.request)
          .then((networkResponse) => {
            if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
              return networkResponse;
            }

            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });

            return networkResponse;
          });
      })
      .catch(() => {
        if (event.request.destination === 'image') {
          return caches.match('/SPNC_HocLieu_STEM_TieuHoc/public/images/logo.png');
        }

        return new Response('Offline', {
          status: 503,
          statusText: 'Service Unavailable'
        });
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