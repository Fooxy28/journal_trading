<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Journal Trading</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937; }
        input[type="date"]::-webkit-calendar-picker-indicator {
            color: rgba(0, 0, 0, 0); opacity: 1; display: block;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%236b7280"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>') no-repeat;
            width: 16px; height: 16px; border-width: thin; cursor: pointer;
        }
        input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .modal { transition: opacity 0.25s ease; }
        body.modal-active { overflow-x: hidden; overflow-y: hidden !important; }
    </style>
</head>
<body class="p-4 md:p-8">

    <div class="max-w-7xl mx-auto space-y-6">

        <!-- HEADER SECTION -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4 md:gap-0">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-black">Master Journal Trading</h1>
                    <div class="flex items-center gap-2">
                        <!-- Dropdown Ganti Akun -->
                        <select id="accountSelect" onchange="handleAccountChange(this)" class="bg-white border border-gray-300 text-blue-600 text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 font-bold cursor-pointer shadow-sm w-max">
                            <option value="">Memuat Akun...</option>
                        </select>
                        <button type="button" onclick="deleteSelectedAccount()" class="bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition" title="Hapus akun terpilih">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
                <p class="text-lg md:text-xl text-gray-400 font-light mt-1">Simple, Excel-like trading journal</p>
            </div>
            <div class="text-right flex flex-col items-end">
                <span class="text-xl font-bold text-black">Balance</span>
                <span class="text-2xl font-bold text-black" id="displayBalance">$0.00</span>
                <button onclick="openModal('modal-adjust')" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-1.5 rounded mt-1 shadow-sm transition">
                    Adjust
                </button>
            </div>
        </div>

        <!-- FORM SECTION -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <form id="tradeForm" onsubmit="saveTrade(event)">
                <div class="grid grid-cols-1 md:grid-cols-9 gap-4 mb-4">
                    <!-- Date -->
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Date</label>
                        <input type="date" id="tradeDate" required class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <!-- Stock/Pair -->
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Stock/Pair</label>
                        <select id="pairSelect" required onchange="handleSelectChange(this, 'modal-pair')" class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                            <option value="">Pilih...</option>
                        </select>
                    </div>
                    <!-- Position -->
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Position</label>
                        <select id="positionType" required class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                        </select>
                    </div>
                    <!-- Entry Price -->
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Entry Price</label>
                        <input type="number" step="any" id="entryPrice" required class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                    </div>
                    <!-- Exit Price -->
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Exit Price</label>
                        <input type="number" step="any" id="exitPrice" required class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                    </div>
                    <!-- Amount ($) -->
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Amount ($)</label>
                        <input type="number" step="any" id="amountVal" required placeholder="e.g. 50 or -20" class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                    </div>
                    <!-- Lot -->
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Lot</label>
                        <input type="number" step="any" id="lotVal" required class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                    </div>
                    <!-- TP/SL Status -->
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">TP/SL Status</label>
                        <select id="tpslStatus" required class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                            <option value="manual">Manual</option>
                            <option value="hit">Hit</option>
                        </select>
                    </div>
                    <!-- Strategy -->
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Strategy</label>
                        <select id="strategySelect" onchange="handleSelectChange(this, 'modal-strategy')" class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                            <option value="">Pilih...</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <!-- Analysis Link -->
                    <div class="col-span-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Analysis Link</label>
                        <!-- type="url" diubah ke "text" agar bisa diisi bebas / strip saja -->
                        <input type="text" id="analysisLink" placeholder="https://... atau -" class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                    </div>
                    <!-- Note -->
                    <div class="col-span-8">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Note</label>
                        <input type="text" id="noteVal" class="w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-blue-500">
                    </div>
                    <!-- Save Button -->
                    <div class="col-span-1 text-right">
                        <button type="submit" id="btnSaveTrade" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-1.5 rounded-md shadow-sm transition">
                            Save
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TABLE SECTION -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead>
                        <tr class="text-gray-600 border-b border-gray-200">
                            <th class="pb-3 px-2 font-semibold">Date</th>
                            <th class="pb-3 px-2 font-semibold">Stok/Pair</th>
                            <th class="pb-3 px-2 font-semibold text-center">Trade</th>
                            <th class="pb-3 px-2 font-semibold">Amount</th>
                            <th class="pb-3 px-2 font-semibold text-center">Lot</th>
                            <th class="pb-3 px-2 font-semibold text-center">Position</th>
                            <th class="pb-3 px-2 font-semibold text-right">Entry Price</th>
                            <th class="pb-3 px-2 font-semibold text-right">Exit Price</th>
                            <th class="pb-3 px-2 font-semibold">TP/SL Status</th>
                            <th class="pb-3 px-2 font-semibold text-center">Net P/L</th>
                            <th class="pb-3 px-2 font-semibold">Strategy</th>
                            <th class="pb-3 px-2 font-semibold text-center">Note</th>
                            <th class="pb-3 px-2 font-semibold text-center">Analysis</th>
                            <th class="pb-3 px-2 font-semibold text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tradeTableBody">
                        <tr><td colspan="14" class="text-center py-4 text-gray-400">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="flex justify-center items-center mt-6 space-x-2 text-sm text-gray-600" id="paginationControls">
                <!-- Diisi oleh JS -->
            </div>
        </div>

        <!-- ANALYTICS SECTION -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-12">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                Analytics <i class="fa-regular fa-calendar text-gray-400"></i>
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Stats Left Column -->
                <div class="col-span-1 space-y-6">
                    <h3 class="text-lg font-bold">Stats</h3>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Total Trades :</span> <span class="font-semibold text-black" id="statTotalTrades">0</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Profit Count :</span> <span class="font-semibold text-green-500" id="statProfitCount">0</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Loss Count :</span> <span class="font-semibold text-red-500" id="statLossCount">0</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Profit Ratio :</span> <span class="font-semibold text-green-500" id="statProfitRatio">0%</span></div>
                    </div>

                    <div class="space-y-2 text-sm pt-2">
                        <div class="flex justify-between"><span class="text-gray-600">Balance :</span> <span class="font-semibold text-black" id="statBalance">$0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Total Profit :</span> <span class="font-semibold text-black" id="statTotalProfit">$0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Total Loss :</span> <span class="font-semibold text-black" id="statTotalLoss">$0.00</span></div>
                    </div>

                    <div class="space-y-2 text-sm pt-2">
                        <div class="flex justify-between"><span class="text-gray-600">Deposits :</span> <span class="font-semibold text-black" id="statDeposits">$0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Withdrawals :</span> <span class="font-semibold text-black" id="statWithdrawals">$0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Total Deposits :</span> <span class="font-semibold text-black" id="statTotalDeposits">$0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Total Withdrawals :</span> <span class="font-semibold text-black" id="statTotalWithdrawals">$0.00</span></div>
                    </div>
                </div>

                <!-- Charts Right Column -->
                <div class="col-span-1 lg:col-span-2 border border-gray-200 rounded-xl p-6">
                    <div class="flex space-x-6 border-b border-gray-200 mb-6 pb-2">
                        <button id="tabChartBtn" type="button" onclick="switchAnalysisTab('chart')" class="font-bold text-black border-b-2 border-black pb-2 -mb-[10px]">Chart</button>
                        <button id="tabGrowthBtn" type="button" onclick="switchAnalysisTab('growth')" class="font-semibold text-gray-400 hover:text-gray-600 pb-2">Growth</button>
                    </div>
                    
                    <div id="analysisChartView" class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div class="relative h-48 w-48 mx-auto">
                            <canvas id="profitRatioChart"></canvas>
                        </div>
                        <div class="h-48 w-full">
                            <canvas id="growthBarChart"></canvas>
                        </div>
                    </div>
                    <div id="analysisGrowthView" class="hidden h-64 w-full">
                        <canvas id="growthOnlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-gray-400 text-sm mt-12 mb-8">
            <h1 class="text-3xl font-bold text-black mb-1">Master Journal Trading</h1>
            <p>Simple, Excel-like trading journal</p>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div id="toast" class="fixed bottom-5 right-5 transform translate-y-20 opacity-0 transition-all duration-300 z-50">
        <div id="toastContent" class="bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg font-semibold flex items-center gap-3">
            <i class="fa-solid fa-circle-info"></i>
            <span id="toastMsg">Message</span>
        </div>
    </div>

    <!-- MODALS -->
    <!-- Modal Add Account -->
    <div class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50" id="modal-account">
        <div class="absolute w-full h-full bg-gray-900 opacity-50" onclick="closeModal('modal-account')"></div>
        <div class="bg-white w-11/12 md:max-w-md mx-auto rounded-xl shadow-lg z-50 overflow-y-auto">
            <div class="py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold">Tambah Akun</p>
                    <div class="cursor-pointer z-50" onclick="closeModal('modal-account')"><i class="fa-solid fa-xmark"></i></div>
                </div>
                <input type="text" id="newAccountName" placeholder="Nama Akun (ex: Cent Account)" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-2 focus:outline-none focus:border-blue-500">
                <div class="flex justify-end pt-4 gap-2">
                    <button class="px-4 py-2 bg-gray-200 text-black rounded-lg hover:bg-gray-300" onclick="closeModal('modal-account')">Batal</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" onclick="submitData('add_account', 'newAccountName', 'accountSelect', 'modal-account')">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Pair -->
    <div class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50" id="modal-pair">
        <div class="absolute w-full h-full bg-gray-900 opacity-50" onclick="closeModal('modal-pair')"></div>
        <div class="bg-white w-11/12 md:max-w-md mx-auto rounded-xl shadow-lg z-50 overflow-y-auto">
            <div class="py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold">Tambah Pair</p>
                    <div class="cursor-pointer z-50" onclick="closeModal('modal-pair')"><i class="fa-solid fa-xmark"></i></div>
                </div>
                <input type="text" id="newPairCode" placeholder="Kode Pair (ex: XAUUSD)" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-2 focus:outline-none focus:border-blue-500 uppercase">
                <div class="flex justify-end pt-4 gap-2">
                    <button class="px-4 py-2 bg-gray-200 text-black rounded-lg hover:bg-gray-300" onclick="closeModal('modal-pair')">Batal</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" onclick="submitData('add_pair', 'newPairCode', 'pairSelect', 'modal-pair')">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Strategy -->
    <div class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50" id="modal-strategy">
        <div class="absolute w-full h-full bg-gray-900 opacity-50" onclick="closeModal('modal-strategy')"></div>
        <div class="bg-white w-11/12 md:max-w-md mx-auto rounded-xl shadow-lg z-50 overflow-y-auto">
            <div class="py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold">Tambah Strategi</p>
                    <div class="cursor-pointer z-50" onclick="closeModal('modal-strategy')"><i class="fa-solid fa-xmark"></i></div>
                </div>
                <input type="text" id="newStrategyName" placeholder="Nama Strategi (ex: Breakout)" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-2 focus:outline-none focus:border-blue-500">
                <div class="flex justify-end pt-4 gap-2">
                    <button class="px-4 py-2 bg-gray-200 text-black rounded-lg hover:bg-gray-300" onclick="closeModal('modal-strategy')">Batal</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" onclick="submitData('add_strategy', 'newStrategyName', 'strategySelect', 'modal-strategy')">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adjust Balance -->
    <div class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50" id="modal-adjust">
        <div class="absolute w-full h-full bg-gray-900 opacity-50" onclick="closeModal('modal-adjust')"></div>
        <div class="bg-white w-11/12 md:max-w-md mx-auto rounded-xl shadow-lg z-50 overflow-y-auto">
            <div class="py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold">Adjust Balance</p>
                    <div class="cursor-pointer z-50" onclick="closeModal('modal-adjust')"><i class="fa-solid fa-xmark"></i></div>
                </div>
                <div class="mt-2 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tipe Adjust</label>
                        <select id="adjustType" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                            <option value="deposit">Deposit (Tambah Saldo)</option>
                            <option value="withdraw">Withdraw (Tarik Saldo)</option>
                            <option value="adjustment">Set Saldo Tepat ($)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah ($)</label>
                        <input type="number" step="any" id="adjustAmount" placeholder="Contoh: 100" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan (Opsional)</label>
                        <input type="text" id="adjustNote" placeholder="Misal: Topup via Bank" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div class="flex justify-end pt-5 gap-2">
                    <button class="px-4 py-2 bg-gray-200 text-black rounded-lg hover:bg-gray-300" onclick="closeModal('modal-adjust')">Batal</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" onclick="submitAdjustBalance()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>