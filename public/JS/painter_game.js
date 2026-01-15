document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById("drawing-canvas");
    const ctx = canvas.getContext("2d");
    
    // Các công cụ và nút bấm
    const toolBtns = document.querySelectorAll(".tool-btn");
    const colorSwatches = document.querySelectorAll(".color-swatch");
    const colorPicker = document.getElementById("color-picker");
    const sizeSlider = document.getElementById("size-slider");
    const clearBtn = document.getElementById("clear-btn");
    const saveBtn = document.getElementById("save-btn");
    const undoBtn = document.getElementById("undo-btn");

    // Lấy biến từ PHP thông qua window (đã sửa ở bước trước)
    const appBaseUrl = window.baseUrl || ""; 
    const timeLimitSeconds = typeof timeLimit !== 'undefined' ? parseInt(timeLimit, 10) : 300;

    // Biến trạng thái
    let isDrawing = false;
    let currentTool = "brush"; // brush, eraser, line, rect, circle, triangle, bucket
    let currentColor = "#000000";
    let currentSize = 5;
    let startX, startY;
    let snapshot; 
    
    // Lịch sử Undo
    let history = [];
    let historyStep = -1;

    // --- 1. KHỞI TẠO ---
    ctx.lineCap = "round";
    ctx.lineJoin = "round";

    function pushHistory() {
        historyStep++;
        if (historyStep < history.length) {
            history.length = historyStep;
        }
        history.push(canvas.toDataURL());
    }

    // --- 2. XỬ LÝ ẢNH NỀN ---
    if (typeof bgImageName !== 'undefined' && bgImageName !== "") {
        const img = new Image();
        img.src = `${appBaseUrl}/public/images/painter/${bgImageName}`;
        img.onload = function() {
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            pushHistory();
        };
        img.onerror = function() {
            // Nếu lỗi ảnh thì vẽ trắng
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            pushHistory();
        }
    } else {
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        pushHistory();
    }

    // --- 3. THUẬT TOÁN ĐỔ MÀU (FLOOD FILL) ---
    // Hàm chuyển đổi HEX sang RGB
    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : { r: 0, g: 0, b: 0 };
    }

    // Hàm lấy màu tại 1 điểm pixel
    function getColorAtPixel(imageData, x, y) {
        const index = (y * imageData.width + x) * 4;
        return {
            r: imageData.data[index],
            g: imageData.data[index + 1],
            b: imageData.data[index + 2],
            a: imageData.data[index + 3]
        };
    }

    // Hàm so sánh màu
    function colorsMatch(c1, c2) {
        return c1.r === c2.r && c1.g === c2.g && c1.b === c2.b && c1.a === c2.a;
    }

    // Hàm thực thi đổ màu (Sử dụng ngăn xếp để tránh đệ quy gây tràn bộ nhớ)
    function floodFill(startX, startY, fillColorHex) {
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const width = imageData.width;
        const height = imageData.height;
        const targetColor = getColorAtPixel(imageData, startX, startY);
        const fillColor = hexToRgb(fillColorHex);
        
        // Thêm kênh alpha cho màu cần tô (255 là không trong suốt)
        fillColor.a = 255; 

        // Nếu màu cần tô giống hệt màu hiện tại thì không làm gì cả
        if (colorsMatch(targetColor, fillColor)) return;

        const stack = [[startX, startY]];

        while (stack.length > 0) {
            const [x, y] = stack.pop();
            const pixelIndex = (y * width + x) * 4;

            const currentColor = {
                r: imageData.data[pixelIndex],
                g: imageData.data[pixelIndex + 1],
                b: imageData.data[pixelIndex + 2],
                a: imageData.data[pixelIndex + 3]
            };

            if (colorsMatch(currentColor, targetColor)) {
                // Tô màu mới
                imageData.data[pixelIndex] = fillColor.r;
                imageData.data[pixelIndex + 1] = fillColor.g;
                imageData.data[pixelIndex + 2] = fillColor.b;
                imageData.data[pixelIndex + 3] = fillColor.a;

                // Kiểm tra 4 hướng: Trái, Phải, Lên, Xuống
                if (x > 0) stack.push([x - 1, y]);
                if (x < width - 1) stack.push([x + 1, y]);
                if (y > 0) stack.push([x, y - 1]);
                if (y < height - 1) stack.push([x, y + 1]);
            }
        }
        ctx.putImageData(imageData, 0, 0);
    }

    // --- 4. CÁC HÀM VẼ CƠ BẢN ---
    const startDraw = (e) => {
        isDrawing = true;
        startX = e.offsetX;
        startY = e.offsetY;

        // Xử lý riêng cho công cụ Xô màu (Bucket)
        if (currentTool === "bucket") {
            // Gọi hàm đổ màu
            // Lưu ý: Cần chờ một chút để UI không bị đơ nếu ảnh quá lớn (nhưng 800x500 thì vẫn nhanh)
            setTimeout(() => {
                floodFill(startX, startY, currentColor);
                pushHistory(); // Lưu lịch sử sau khi đổ màu
            }, 0);
            
            isDrawing = false; // Xô màu chỉ là 1 click, không phải kéo chuột
            return;
        }
        
        ctx.beginPath();
        ctx.lineWidth = currentSize;
        
        if (currentTool === "eraser") {
            ctx.strokeStyle = "#ffffff"; 
        } else {
            ctx.strokeStyle = currentColor;
            ctx.fillStyle = currentColor;
        }

        snapshot = ctx.getImageData(0, 0, canvas.width, canvas.height);
    };

    const drawing = (e) => {
        if (!isDrawing) return;
        if (currentTool === "bucket") return; // Xô màu không vẽ khi di chuyển

        // Nếu là hình khối, cần xóa và vẽ lại trên snapshot cũ
        if (["line", "rect", "circle", "triangle"].includes(currentTool)) {
            ctx.putImageData(snapshot, 0, 0);
        }

        const currentX = e.offsetX;
        const currentY = e.offsetY;

        switch (currentTool) {
            case "brush":
            case "eraser":
                ctx.lineTo(currentX, currentY);
                ctx.stroke();
                break;
            case "line":
                drawLine(currentX, currentY);
                break;
            case "rect":
                drawRect(currentX, currentY);
                break;
            case "circle":
                drawCircle(currentX, currentY);
                break;
            case "triangle":
                drawTriangle(currentX, currentY);
                break;
        }
    };

    const stopDraw = () => {
        if (!isDrawing) return;
        isDrawing = false;
        // Chỉ lưu lịch sử khi không phải là bucket (bucket đã lưu lúc click rồi)
        if(currentTool !== 'bucket') {
             pushHistory();
        }
    };

    // Hàm vẽ hình khối
    const drawLine = (x, y) => {
        ctx.beginPath();
        ctx.moveTo(startX, startY);
        ctx.lineTo(x, y);
        ctx.stroke();
    };

    const drawRect = (x, y) => {
        ctx.beginPath();
        ctx.rect(startX, startY, x - startX, y - startY);
        ctx.stroke();
    };

    const drawCircle = (x, y) => {
        ctx.beginPath();
        let radius = Math.sqrt(Math.pow((startX - x), 2) + Math.pow((startY - y), 2));
        ctx.arc(startX, startY, radius, 0, 2 * Math.PI);
        ctx.stroke();
    };

    const drawTriangle = (x, y) => {
        ctx.beginPath();
        ctx.moveTo(startX, startY);
        ctx.lineTo(x, y);
        ctx.lineTo(startX * 2 - x, y);
        ctx.closePath();
        ctx.stroke();
    };

    // --- 5. SỰ KIỆN CHUỘT ---
    canvas.addEventListener("mousedown", startDraw);
    canvas.addEventListener("mousemove", drawing);
    canvas.addEventListener("mouseup", stopDraw);
    canvas.addEventListener("mouseout", stopDraw);

    // --- 6. CHỨC NĂNG CÔNG CỤ ---
    toolBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            if (btn.id === "undo-btn" || btn.id === "clear-btn") return;
            document.querySelector(".tool-btn.active").classList.remove("active");
            btn.classList.add("active");
            currentTool = btn.dataset.tool;
        });
    });

    colorSwatches.forEach(swatch => {
        swatch.addEventListener("click", () => {
            const activeSwatch = document.querySelector(".color-swatch.selected");
            if (activeSwatch) {
                activeSwatch.classList.remove("selected");
            }

            // document.querySelector(".color-swatch.selected").classList.remove("selected");
            swatch.classList.add("selected");
            currentColor = swatch.dataset.color;
            colorPicker.value = currentColor;
        });
    });

    colorPicker.addEventListener("input", (e) => {
        currentColor = e.target.value;
        const selectedSwatch = document.querySelector(".color-swatch.selected");
        if(selectedSwatch) selectedSwatch.classList.remove("selected");
    });

    sizeSlider.addEventListener("change", (e) => ctx.lineWidth = e.target.value);
    sizeSlider.addEventListener("input", (e) => currentSize = e.target.value);

    clearBtn.addEventListener("click", () => {
        if(confirm("Bạn có chắc muốn xóa hết không?")) {
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            pushHistory();
        }
    });

    undoBtn.addEventListener("click", () => {
        if (historyStep > 0) {
            historyStep--;
            const canvasPic = new Image();
            canvasPic.src = history[historyStep];
            canvasPic.onload = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(canvasPic, 0, 0);
            };
        }
    });

    saveBtn.addEventListener("click", () => {
        const link = document.createElement("a");
        link.download = `tranh-ve-cua-em-${Date.now()}.png`;
        link.href = canvas.toDataURL();
        link.click();
    });

    // --- TIMER + SUBMIT HANDLING ---
    const timeDisplay = document.getElementById('time-display');
    const submitBtn = document.getElementById('submit-btn');
    const homeBtn = document.getElementById('home-btn');

    let timerInterval = null;
    let remainingSeconds = timeLimitSeconds;

    function formatTime(sec) {
        const m = Math.floor(sec / 60).toString().padStart(2, '0');
        const s = (sec % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    }

    function updateTimeDisplay() {
        if (timeDisplay) timeDisplay.textContent = formatTime(remainingSeconds);
    }

    function startTimer() {
        // do not start again if already running
        if (timerInterval) return;
        remainingSeconds = timeLimitSeconds;
        updateTimeDisplay();
        timerInterval = setInterval(() => {
            remainingSeconds--;
            updateTimeDisplay();
            if (remainingSeconds <= 0) {
                clearInterval(timerInterval);
                timerInterval = null;
                remainingSeconds = 0;
                updateTimeDisplay();
                // Auto-submit when time ends (no alert by default)
                try { performSubmit(false); } catch (e) { console.error('Auto-submit failed', e); }
            }
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }

    function resetTimer() {
        stopTimer();
        remainingSeconds = timeLimitSeconds;
        updateTimeDisplay();
    }

    // Initialize display and start timer immediately on page load
    updateTimeDisplay();
    // Start timer automatically when user opens the page
    startTimer();

    // Reset timer when user clears the canvas
    clearBtn.addEventListener('click', () => {
        // After canvas cleared in existing handler, reset timer too
        resetTimer();
    });

    // Reset when clicking home (navigation will happen, but reset for UX)
    if (homeBtn) {
        homeBtn.addEventListener('click', (e) => {
            resetTimer();
            // allow navigation to proceed
        });
    }

    // Helper: perform submit (used by button and auto-submit)
    async function performSubmit(showAlerts = true) {
        // disable submit while in progress
        if (submitBtn) submitBtn.disabled = true;
        try {
            const resp = await fetch(`${appBaseUrl}/views/lessons/update-painter-score`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'commit' })
            });
            const j = await resp.json();
            if (j && j.success) {
                if (showAlerts) alert('Đã nộp bài và lưu điểm: ' + (j.message || 'Thành công'));
            } else {
                if (showAlerts) alert('Kết quả nộp bài: ' + (j.message || JSON.stringify(j)));
            }
        } catch (err) {
            console.error('Submit error', err);
            if (showAlerts) alert('Lỗi khi nộp bài. Vui lòng thử lại.');
        } finally {
            // stop timer after submit
            stopTimer();
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    // Submit button triggers performSubmit (but does not start timer now)
    if (submitBtn) {
        submitBtn.addEventListener('click', (e) => {
            performSubmit(true);
        });
    }

});