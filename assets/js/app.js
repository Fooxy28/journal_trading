// Tanggal default hari ini
document.getElementById('tradeDate').valueAsDate = new Date();

let currentPage = 1;
let pieChartInstance = null;
let barChartInstance = null;
let growthOnlyChartInstance = null;

// Custom Toast Notification
function showToast(message, isError = false) {
    const toast = document.getElementById('toast');
    const toastContent = document.getElementById('toastContent');
    const toastMsg = document.getElementById('toastMsg');

    toastMsg.innerText = message;
    toastContent.className = isError
        ? 'bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg font-semibold flex items-center gap-3'
        : 'bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg font-semibold flex items-center gap-3';

    toast.classList.remove('translate-y-20', 'opacity-0');
    setTimeout(() => { toast.classList.add('translate-y-20', 'opacity-0'); }, 3000);
}

// Modal Handlers
function openModal(modalID) { document.getElementById(modalID).classList.remove('opacity-0', 'pointer-events-none'); }
function closeModal(modalID) { document.getElementById(modalID).classList.add('opacity-0', 'pointer-events-none'); }

function handleSelectChange(selectEl, modalID) {
    if (selectEl.value === 'add_new') {
        selectEl.value = '';
        openModal(modalID);
    }
}

function handleAccountChange(selectEl) {
    if (selectEl.value === 'add_new') {
        selectEl.value = '';
        openModal('modal-account');
    } else if (selectEl.value !== '') {
        loadData(selectEl.value);
    }
}

function switchAnalysisTab(tab) {
    const chartBtn = document.getElementById('tabChartBtn');
    const growthBtn = document.getElementById('tabGrowthBtn');
    const chartView = document.getElementById('analysisChartView');
    const growthView = document.getElementById('analysisGrowthView');

    if (tab === 'growth') {
        chartView.classList.add('hidden');
        growthView.classList.remove('hidden');

        growthBtn.className = 'font-bold text-black border-b-2 border-black pb-2 -mb-[10px]';
        chartBtn.className = 'font-semibold text-gray-400 hover:text-gray-600 pb-2';
    } else {
        growthView.classList.add('hidden');
        chartView.classList.remove('hidden');

        chartBtn.className = 'font-bold text-black border-b-2 border-black pb-2 -mb-[10px]';
        growthBtn.className = 'font-semibold text-gray-400 hover:text-gray-600 pb-2';
    }
}

async function deleteSelectedAccount() {
    const accountId = document.getElementById('accountSelect').value;
    if (!accountId) return showToast('Pilih akun yang ingin dihapus!', true);

    const ok = confirm('Yakin ingin menghapus akun ini? Semua data trade akun ini juga akan terhapus.');
    if (!ok) return;

    const formData = new FormData();
    formData.append('action', 'delete_account');
    formData.append('account_id', accountId);

    try {
        const res = await fetch('ajax_handler.php', { method: 'POST', body: formData });
        const json = await res.json();

        if (json.status === 'success') {
            showToast(json.message);
            currentPage = 1;
            loadDropdowns();
        } else {
            showToast(json.message || 'Gagal menghapus akun.', true);
        }
    } catch (err) {
        showToast('Terjadi kesalahan server saat menghapus akun.', true);
    }
}

// --- AJAX DATA FETCHING ---

// 1. Memuat semua dropdown di awal
async function loadDropdowns() {
    try {
        const res = await fetch('ajax_handler.php?action=get_dropdowns');
        const json = await res.json();

        if (json.status === 'success') {
            const accSelect = document.getElementById('accountSelect');
            accSelect.innerHTML = `<option value="">Pilih Akun...</option>` +
                json.data.accounts.map(a => `<option value="${a.id}">${a.account_name}</option>`).join('') +
                `<option value="add_new" class="font-bold text-blue-600">+ Tambah Akun</option>`;

            const pairSelect = document.getElementById('pairSelect');
            pairSelect.innerHTML = `<option value="">Pilih...</option>` + json.data.pairs.map(p => `<option value="${p.id}">${p.pair_code}</option>`).join('') + `<option value="add_new" class="font-bold text-blue-600">+ Tambah Pair</option>`;

            const stratSelect = document.getElementById('strategySelect');
            stratSelect.innerHTML = `<option value="">Pilih...</option>` + json.data.strategies.map(s => `<option value="${s.id}">${s.strategy_name}</option>`).join('') + `<option value="add_new" class="font-bold text-blue-600">+ Tambah Strategi</option>`;

            if (json.data.accounts.length > 0) {
                accSelect.value = json.data.accounts[0].id;
                loadData(json.data.accounts[0].id);
            } else {
                accSelect.value = '';
                showToast('Silakan tambah akun terlebih dahulu', true);
            }
        }
    } catch (err) {
        console.error(err);
    }
}

// Helper untuk memanggil submit popup (Tambah Akun/Pair/Strategy)
async function submitData(action, inputId, selectId, modalId) {
    const inputVal = document.getElementById(inputId).value;
    if (!inputVal) return showToast('Data tidak boleh kosong!', true);

    const formData = new FormData();
    formData.append('action', action);
    if (action === 'add_account') formData.append('account_name', inputVal);
    if (action === 'add_pair') formData.append('pair_code', inputVal);
    if (action === 'add_strategy') formData.append('strategy_name', inputVal);

    try {
        const res = await fetch('ajax_handler.php', { method: 'POST', body: formData });
        const json = await res.json();

        if (json.status === 'success') {
            showToast(json.message);
            closeModal(modalId);
            document.getElementById(inputId).value = '';
            loadDropdowns();
        } else {
            showToast(json.message, true);
        }
    } catch (err) {
        showToast('Terjadi kesalahan server', true);
    }
}

// 2. Load Tabel & Analytics
function loadData(accountId) {
    loadTrades(accountId, currentPage);
    loadAnalytics(accountId);
}

// Render Tabel
async function loadTrades(accountId, page) {
    try {
        const res = await fetch(`ajax_handler.php?action=get_trades&account_id=${accountId}&page=${page}`);
        const json = await res.json();

        const tbody = document.getElementById('tradeTableBody');
        if (json.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="14" class="text-center py-4 text-gray-400">Belum ada history trading.</td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(t => {
            const isProfit = t.trading_status === 'profit';
            const rowClass = isProfit ? 'bg-emerald-50/60 hover:bg-emerald-100/60' : 'bg-rose-50/60 hover:bg-rose-100/60';
            const badge = isProfit ? 'bg-[#2ecc71]' : 'bg-[#ff4757]';
            const posBadge = t.position_type === 'buy' ? 'bg-blue-500' : 'bg-[#ff4757]';
            const noteIcon = t.note ? `<i class="fa-regular fa-copy text-gray-500" title="${t.note}"></i>` : '-';

            let linkIcon = '-';
            if (t.analysis_link && t.analysis_link !== '-') {
                const url = t.analysis_link.startsWith('http') ? t.analysis_link : `https://${t.analysis_link}`;
                linkIcon = `<a href="${url}" target="_blank" class="text-blue-500"><i class="fa-solid fa-link"></i></a>`;
            }

            return `<tr class="border-b border-white ${rowClass} transition-colors">
                <td class="py-2 px-2">${t.trade_date}</td>
                <td class="py-2 px-2 font-semibold">${t.pair_code || '-'}</td>
                <td class="py-2 px-2 text-center"><span class="${badge} text-white text-[10px] font-bold px-3 py-0.5 rounded-full capitalize">${t.trading_status}</span></td>
                <td class="py-2 px-2 font-medium text-black">$${Math.abs(t.amount)} ${!isProfit ? '<span class="text-red-500 ml-[-4px]">-</span>' : ''}</td>
                <td class="py-2 px-2 text-center">${parseFloat(t.lot).toString()}</td>
                <td class="py-2 px-2 text-center"><span class="${posBadge} text-white text-[10px] font-bold px-3 py-0.5 rounded-full capitalize">${t.position_type}</span></td>
                <td class="py-2 px-2 text-right">${parseFloat(t.entry_price).toString()}</td>
                <td class="py-2 px-2 text-right">${parseFloat(t.exit_price).toString()}</td>
                <td class="py-2 px-2 capitalize">${t.tp_sl_status}</td>
                <td class="py-2 px-2 text-center"><span class="${badge} text-white text-[10px] font-bold px-2 py-0.5 rounded">${parseFloat(t.net_percent).toFixed(2)}%</span></td>
                <td class="py-2 px-2">${t.strategy_name || '-'}</td>
                <td class="py-2 px-2 text-center cursor-pointer">${noteIcon}</td>
                <td class="py-2 px-2 text-center">${linkIcon}</td>
                <td class="py-2 px-2 text-center">
                    <button type="button" onclick="deleteTrade(${t.id})" class="text-red-600 hover:text-red-700 font-semibold text-xs">Delete</button>
                </td>
            </tr>`;
        }).join('');
    } catch (err) {
        console.error(err);
    }
}

// 2b. Hapus Trade
async function deleteTrade(tradeId) {
    const accountId = document.getElementById('accountSelect').value;
    if (!accountId) return showToast('Pilih akun terlebih dahulu!', true);

    const ok = confirm('Yakin ingin menghapus trade ini?');
    if (!ok) return;

    const formData = new FormData();
    formData.append('action', 'delete_trade');
    formData.append('trade_id', tradeId);
    formData.append('account_id', accountId);

    try {
        const res = await fetch('ajax_handler.php', { method: 'POST', body: formData });
        const json = await res.json();

        if (json.status === 'success') {
            showToast(json.message);
            loadData(accountId);
        } else {
            showToast(json.message || 'Gagal menghapus trade.', true);
        }
    } catch (err) {
        showToast('Terjadi kesalahan server saat menghapus trade.', true);
    }
}

// Render Analytics & Charts
async function loadAnalytics(accountId) {
    try {
        const res = await fetch(`ajax_handler.php?action=get_analytics&account_id=${accountId}`);
        const json = await res.json();

        if (json.status === 'success') {
            const d = json.data;
            const stats = d.stats;

            document.getElementById('displayBalance').innerText = `$${parseFloat(d.balance).toFixed(2)}`;
            document.getElementById('statBalance').innerText = `$${parseFloat(d.balance).toFixed(2)}`;

            document.getElementById('statTotalTrades').innerText = stats.total_trades;
            document.getElementById('statProfitCount').innerText = stats.profit_count || 0;
            document.getElementById('statLossCount').innerText = stats.loss_count || 0;

            const ratio = stats.total_trades > 0 ? ((stats.profit_count / stats.total_trades) * 100).toFixed(0) : 0;
            document.getElementById('statProfitRatio').innerText = `${ratio}%`;

            document.getElementById('statTotalProfit').innerText = `$${parseFloat(stats.total_profit || 0).toFixed(2)}`;
            document.getElementById('statTotalLoss').innerText = `-$${Math.abs(stats.total_loss || 0).toFixed(2)}`;

            document.getElementById('statDeposits').innerText = `$${parseFloat(d.logs.total_deposits || 0).toFixed(2)}`;
            document.getElementById('statWithdrawals').innerText = `$${parseFloat(d.logs.total_withdrawals || 0).toFixed(2)}`;
            document.getElementById('statTotalDeposits').innerText = `$${parseFloat(d.logs.total_deposits || 0).toFixed(2)}`;
            document.getElementById('statTotalWithdrawals').innerText = `$${parseFloat(d.logs.total_withdrawals || 0).toFixed(2)}`;

            renderCharts(stats.profit_count || 0, stats.loss_count || 0, d.chart);
        }
    } catch (err) {
        console.error(err);
    }
}

function renderCharts(profitCount, lossCount, barData) {
    if (pieChartInstance) pieChartInstance.destroy();
    if (barChartInstance) barChartInstance.destroy();
    if (growthOnlyChartInstance) growthOnlyChartInstance.destroy();

    const pieCtx = document.getElementById('profitRatioChart').getContext('2d');
    pieChartInstance = new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Profit', 'Loss'],
            datasets: [{
                data: [profitCount, lossCount],
                backgroundColor: ['#2ecc71', '#ff4757'], borderWidth: 0, cutout: '40%'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    const labels = barData.map(b => b.chart_date.substring(5));
    const dailyChange = barData.map(b => parseFloat(b.daily_change || 0));
    let runningTotal = 0;
    const dataPts = dailyChange.map(val => {
        runningTotal += val;
        return runningTotal;
    });

    const bgColors = dataPts.map((val, idx) => {
        if (idx === 0) return '#2ecc71';
        return val >= dataPts[idx - 1] ? '#2ecc71' : '#ff4757';
    });

    const barCtx = document.getElementById('growthBarChart').getContext('2d');
    barChartInstance = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{ data: dataPts, backgroundColor: bgColors, barThickness: 30 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#f3f4f6' }, border: { display: false } },
                x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });

    const growthOnlyCtx = document.getElementById('growthOnlyChart').getContext('2d');
    growthOnlyChartInstance = new Chart(growthOnlyCtx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: dataPts,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.35,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#f3f4f6' }, border: { display: false } },
                x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
}

// 3. Simpan Trading Baru
async function saveTrade(e) {
    e.preventDefault();
    const accountId = document.getElementById('accountSelect').value;
    if (!accountId) return showToast('Pilih atau buat akun terlebih dahulu!', true);

    const formData = new FormData();
    formData.append('action', 'save_trade');
    formData.append('account_id', accountId);
    formData.append('trade_date', document.getElementById('tradeDate').value);
    formData.append('pair_id', document.getElementById('pairSelect').value);
    formData.append('position_type', document.getElementById('positionType').value);
    formData.append('entry_price', document.getElementById('entryPrice').value);
    formData.append('exit_price', document.getElementById('exitPrice').value);
    formData.append('amount', document.getElementById('amountVal').value);
    formData.append('lot', document.getElementById('lotVal').value);
    formData.append('tp_sl_status', document.getElementById('tpslStatus').value);
    formData.append('strategy_id', document.getElementById('strategySelect').value);
    formData.append('analysis_link', document.getElementById('analysisLink').value);
    formData.append('note', document.getElementById('noteVal').value);

    const btn = document.getElementById('btnSaveTrade');
    btn.innerText = 'Menyimpan...';
    btn.disabled = true;

    try {
        const res = await fetch('ajax_handler.php', { method: 'POST', body: formData });
        const json = await res.json();

        if (json.status === 'success') {
            showToast(json.message);
            document.getElementById('tradeForm').reset();
            document.getElementById('tradeDate').valueAsDate = new Date();
            loadData(accountId);
        } else {
            showToast(json.message, true);
        }
    } catch (err) {
        showToast('Gagal menyimpan data.', true);
    }

    btn.innerText = 'Save';
    btn.disabled = false;
}

// Init App
window.onload = () => {
    loadDropdowns();
};

// Tambahan Fungsi Submit Adjust Balance
async function submitAdjustBalance() {
    const accountId = document.getElementById('accountSelect').value;
    if (!accountId) return showToast('Pilih akun terlebih dahulu!', true);

    const type = document.getElementById('adjustType').value;
    const amount = document.getElementById('adjustAmount').value;
    const note = document.getElementById('adjustNote').value;

    if (!amount || amount <= 0) return showToast('Jumlah uang harus lebih dari 0', true);

    const formData = new FormData();
    formData.append('action', 'adjust_balance');
    formData.append('account_id', accountId);
    formData.append('type', type);
    formData.append('amount', amount);
    formData.append('note', note);

    try {
        const res = await fetch('ajax_handler.php', { method: 'POST', body: formData });
        const json = await res.json();

        if (json.status === 'success') {
            showToast(json.message);
            closeModal('modal-adjust');
            document.getElementById('adjustAmount').value = '';
            document.getElementById('adjustNote').value = '';
            loadData(accountId);
        } else {
            showToast(json.message, true);
        }
    } catch (err) {
        showToast('Gagal memproses request adjust balance', true);
    }
}
