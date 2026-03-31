document.addEventListener("DOMContentLoaded", () => {
    const currentParts = {
        body: null,
        engine: null,
        wheel: null,
        addon: null
    };

    let currentStats = { speed: 0, power: 0, grip: 0 };

    const carAssembly = document.getElementById('car-assembly');
    const simCar = document.getElementById('sim-car');
    const testBtn = document.getElementById('btn-test-drive');
    const modal = document.getElementById('simulation-modal');
    const simMsg = document.getElementById('sim-message');
    const simActions = document.getElementById('sim-actions');
    const nextBtn = document.getElementById('next-level-btn');
    const retryBtn = document.getElementById('retry-btn');

    window.selectPart = function(category, id) {
        const options = document.querySelectorAll(`.part-item[onclick*="'${category}'"]`);
        options.forEach(el => el.classList.remove('selected'));

        const selectedEl = document.querySelector(`.part-item[onclick*="'${category}', '${id}'"]`);
        if(selectedEl) selectedEl.classList.add('selected');

        const data = JSON.parse(selectedEl.dataset.stats);
        currentParts[category] = data;

        updatePreview(category, data.img);

        calculateStats();
    };

    function updatePreview(category, imgName) {
        if (category === 'wheel') {
            document.getElementById('preview-wheel-f').src = `${baseUrl}/public/images/car_builder/${imgName}`;
            document.getElementById('preview-wheel-b').src = `${baseUrl}/public/images/car_builder/${imgName}`;
        } else {
            const el = document.getElementById(`preview-${category}`);
            if (imgName) {
                el.src = `${baseUrl}/public/images/car_builder/${imgName}`;
                el.style.display = 'block';
            } else {
                el.style.display = 'none';
            }
        }
    }

    function calculateStats() {
        currentStats = { speed: 0, power: 0, grip: 0 };

        Object.values(currentParts).forEach(part => {
            if (part) {
                currentStats.speed += part.speed;
                currentStats.power += part.power;
                currentStats.grip += part.grip;
            }
        });

        updateStatBar('speed', currentStats.speed);
        updateStatBar('power', currentStats.power);
        updateStatBar('grip', currentStats.grip);
    }

    function updateStatBar(type, val) {
        const percent = Math.min(100, Math.max(0, val));
        document.getElementById(`bar-${type}`).style.width = `${percent}%`;
        document.getElementById(`val-${type}`).innerText = val;
    }

    testBtn.addEventListener('click', () => {
        if (!currentParts.body || !currentParts.engine || !currentParts.wheel) {
            alert("Bạn chưa lắp đủ xe! Hãy chọn Khung, Động cơ và Bánh xe.");
            return;
        }

        modal.style.display = 'flex';
        simActions.style.display = 'none';
        simMsg.innerText = "Chuẩn bị...";
        simMsg.style.color = "#333";

        simCar.innerHTML = carAssembly.innerHTML;
        simCar.style.left = '10px'; 
        simCar.style.transform = 'rotate(0deg)';

        setTimeout(() => runSimulation(), 1000);
    });

    function runSimulation() {
        const req = levelReq;
        let passed = true;
        let failReason = "";

        if (currentStats.grip < req.req_grip) {
            passed = false;
            failReason = "Xe bị trượt bánh! Cần thay bánh xe có độ bám tốt hơn.";
            animateFail("slip");
            return;
        }

        if (currentStats.power < req.req_power) {
            passed = false;
            failReason = "Động cơ quá yếu, không leo nổi dốc!";
            animateFail("stall");
            return;
        }

        if (currentStats.speed < req.req_speed) {
            passed = false;
            failReason = "Xe chạy quá chậm, không về đích kịp giờ!";
            animateFail("slow");
            return;
        }

        animateWin();
    }

    function animateWin() {
        simMsg.innerText = "Vroom vroom... Xe chạy rất tốt!";
        simCar.style.transition = "left 2s ease-in";
        simCar.style.left = "80%";

        setTimeout(() => {
            simMsg.innerText = "🎉 CHÚC MỪNG! BẠN ĐÃ THÀNH CÔNG!";
            simMsg.style.color = "#27ae60";
            simActions.style.display = 'block';
            
            if (levelReq.id < totalGameLevels) {
                nextBtn.style.display = 'inline-block';
                nextBtn.onclick = () => window.location.href = `${baseUrl}/views/lessons/tech-car-builder?level=${levelReq.id + 1}`;
            } else {
                nextBtn.style.display = 'none';
                simMsg.innerText += " Bạn là kỹ sư tài ba!";
            }
        }, 2200);
    }

    function animateFail(type) {
        if (type === 'slip') {
            simMsg.innerText = "Xe đang bị trượt...";
            simCar.style.transition = "left 1s linear, transform 1s";
            simCar.style.left = "40%";
            setTimeout(() => {
                simCar.style.transform = "rotate(20deg)"; 
                showFailUI(failReason);
            }, 1000);
        } else if (type === 'stall') {
            simMsg.innerText = "Xe đang leo dốc...";
            simCar.style.transition = "left 2s ease-out";
            simCar.style.left = "50%";
            setTimeout(() => {
                simCar.style.transition = "left 1s ease-in";
                simCar.style.left = "30%";
                showFailUI(failReason);
            }, 2000);
        } else if (type === 'slow') {
            simMsg.innerText = "Xe chạy chậm quá...";
            simCar.style.transition = "left 4s linear"; 
            simCar.style.left = "60%";
            setTimeout(() => {
                showFailUI(failReason);
            }, 3000);
        }
    }
    
    let failReason = "";
    function showFailUI(reason) {
        simMsg.innerText = "❌ THẤT BẠI: " + reason;
        simMsg.style.color = "#c0392b";
        simActions.style.display = 'block';
        nextBtn.style.display = 'none';
    }

    retryBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    selectPart('body', 'sport');
    selectPart('engine', 'v4');
    selectPart('wheel', 'small');
    selectPart('addon', 'none');
});