<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        .info-box {
            background: #e8f4fd;
            border: 1px solid #3498db;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .test-section {
            margin-bottom: 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        .test-section h3 {
            color: #2c3e50;
            margin-top: 0;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 8px;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }
        button:hover {
            background: #2980b9;
        }
        button:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .loading {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        pre {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            overflow-x: auto;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $title }}</h1>
        
        <div class="info-box">
            <strong>SIMPELS API URL:</strong> {{ $simpels_url }}<br>
            <strong>EPOS App:</strong> {{ $app_name }}<br>
            <strong>Test Time:</strong> <span id="current-time">{{ now()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}</span>
        </div>

        <div class="grid">
            <!-- Connection Test -->
            <div class="test-section">
                <h3>🔗 Test Koneksi SIMPELS</h3>
                <p>Cek apakah EPOS bisa terhubung ke SIMPELS (Port 8001)</p>
                <button onclick="testConnection()">Test Connection</button>
                <div id="connection-result"></div>
            </div>

            <!-- Santri Lookup Test -->
            <div class="test-section">
                <h3>👤 Test Lookup Santri by RFID</h3>
                <p>Cari data santri berdasarkan UID RFID</p>
                <div class="form-group">
                    <label for="rfid-uid">RFID UID:</label>
                    <input type="text" id="rfid-uid" placeholder="Contoh: 04A1B2C3" maxlength="50">
                </div>
                <button onclick="testSantriLookup()">Lookup Santri</button>
                <div id="santri-result"></div>
            </div>
        </div>

        <!-- Transaction Test -->
        <div class="test-section">
            <h3>💰 Test Transaksi EPOS</h3>
            <p>Simulasi transaksi pembelian di EPOS</p>
            <div style="margin-bottom: 15px;">
                <button onclick="getSampleSantri()" style="background: #27ae60;">Get Sample Santri ID</button>
                <span style="color: #666; font-size: 12px; margin-left: 10px;">← Click this first to get valid santri_id</span>
            </div>
            <div class="grid">
                <div>
                    <div class="form-group">
                        <label for="santri-id">Santri ID:</label>
                        <input type="text" id="santri-id" placeholder="UUID Santri dari lookup di atas">
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount (Rp):</label>
                        <input type="number" id="amount" value="5000" min="100" step="100">
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <input type="text" id="description" value="Test Transaction from EPOS Dashboard">
                    </div>
                    <div class="form-group">
                        <label for="items">Items (JSON):</label>
                        <textarea id="items" rows="3" placeholder='[{"product_name":"Nasi Gudeg","price":5000,"qty":1}]'>[{"product_name":"Test Item","price":5000,"qty":1}]</textarea>
                    </div>
                </div>
            </div>
            <button onclick="testTransaction()">Process Transaction</button>
            <div id="transaction-result"></div>
        </div>

        <!-- All Santri Test -->
        <div class="test-section">
            <h3>📋 Test Get All Santri (Optional)</h3>
            <p>Coba ambil semua data santri dari SIMPELS</p>
            <button onclick="testAllSantri()">Get All Santri</button>
            <div id="all-santri-result"></div>
        </div>
    </div>

    <script>
        // Update time
        function updateTime() {
            document.getElementById('current-time').textContent = new Date().toLocaleString('id-ID', {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        setInterval(updateTime, 1000);

        // Helper function to show result
        function showResult(elementId, data, isSuccess = true) {
            const element = document.getElementById(elementId);
            const className = isSuccess ? 'success' : 'error';
            element.innerHTML = `
                <div class="${className}">
                    <strong>${isSuccess ? 'SUCCESS' : 'ERROR'}:</strong> ${data.message || 'Operation completed'}
                </div>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            `;
        }

        function showLoading(elementId, message = 'Loading...') {
            document.getElementById(elementId).innerHTML = `<div class="loading">${message}</div>`;
        }

        // Test Connection
        async function testConnection() {
            showLoading('connection-result', 'Testing connection to SIMPELS...');
            
            try {
                const response = await fetch('/simpels/test-connection');
                const data = await response.json();
                showResult('connection-result', data, data.success);
            } catch (error) {
                showResult('connection-result', {
                    success: false,
                    message: error.message,
                    error: 'Network or server error'
                }, false);
            }
        }

        // Test Santri Lookup
        async function testSantriLookup() {
            const uid = document.getElementById('rfid-uid').value.trim();
            if (!uid) {
                alert('Please enter RFID UID');
                return;
            }

            showLoading('santri-result', 'Looking up santri data...');
            
            try {
                const response = await fetch(`/simpels/test-santri/${encodeURIComponent(uid)}`);
                const data = await response.json();
                
                // Auto-fill santri ID if found
                if (data.success && data.data && data.data.santri && data.data.santri.id) {
                    document.getElementById('santri-id').value = data.data.santri.id;
                }
                
                showResult('santri-result', data, data.success);
            } catch (error) {
                showResult('santri-result', {
                    success: false,
                    message: error.message,
                    error: 'Network or server error'
                }, false);
            }
        }

        // Test Transaction
        async function testTransaction() {
            const santriId = document.getElementById('santri-id').value.trim();
            const amount = document.getElementById('amount').value;
            const description = document.getElementById('description').value.trim();
            const itemsText = document.getElementById('items').value.trim();

            if (!santriId) {
                alert('Please enter Santri ID (get it from santri lookup first)');
                return;
            }

            if (!amount || amount <= 0) {
                alert('Please enter valid amount');
                return;
            }

            let items;
            try {
                items = JSON.parse(itemsText);
            } catch (e) {
                alert('Invalid JSON format for items');
                return;
            }

            showLoading('transaction-result', 'Processing transaction...');
            
            try {
                const response = await fetch('/simpels/test-transaction', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        santri_id: santriId,
                        amount: parseFloat(amount),
                        description: description,
                        items: items
                    })
                });

                const data = await response.json();
                showResult('transaction-result', data, data.success);
            } catch (error) {
                showResult('transaction-result', {
                    success: false,
                    message: error.message,
                    error: 'Network or server error'
                }, false);
            }
        }

        // Get Sample Santri
        async function getSampleSantri() {
            showLoading('transaction-result', 'Getting sample santri from SIMPELS...');
            
            try {
                const response = await fetch('/simpels/get-sample-santri');
                const data = await response.json();
                
                if (data.success && data.data && data.data.data && data.data.data.santri_id) {
                    // Auto-fill the santri ID field
                    document.getElementById('santri-id').value = data.data.data.santri_id;
                    
                    // Show success with santri info
                    const santriInfo = data.data.data;
                    const infoHtml = `
                        <div class="success">
                            <strong>Sample Santri Loaded:</strong>
                            <br>• Name: ${santriInfo.nama_santri}
                            <br>• NIS: ${santriInfo.nis}
                            <br>• Class: ${santriInfo.kelas || 'N/A'}
                            <br>• Wallet Balance: Rp ${santriInfo.wallet_balance || 0}
                            <br>• RFID: ${santriInfo.rfid_uid || 'Not set'}
                            <br><em>Santri ID has been auto-filled below ↓</em>
                        </div>
                    `;
                    document.getElementById('transaction-result').innerHTML = infoHtml;
                } else {
                    showResult('transaction-result', data, false);
                }
            } catch (error) {
                showResult('transaction-result', {
                    success: false,
                    message: error.message,
                    error: 'Network or server error'
                }, false);
            }
        }

        // Test All Santri
        async function testAllSantri() {
            showLoading('all-santri-result', 'Fetching all santri data...');
            
            try {
                const response = await fetch('/simpels/test-all-santri');
                const data = await response.json();
                showResult('all-santri-result', data, data.success);
            } catch (error) {
                showResult('all-santri-result', {
                    success: false,
                    message: error.message,
                    error: 'Network or server error'
                }, false);
            }
        }

        // Auto-test connection on page load
        document.addEventListener('DOMContentLoaded', function() {
            testConnection();
        });
    </script>
</body>
</html>