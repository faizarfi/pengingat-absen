/**
 * WA Desktop Agent v3.0 — Professional Background Mode
 * 
 * Fitur:
 * - 100% background (headless Chromium via whatsapp-web.js)
 * - Logging ke file (rotasi harian)
 * - Notifikasi Telegram saat disconnect/reconnect
 * - Retry cerdas (bedakan error nomor invalid vs koneksi)
 * - Anti-ban: jeda acak + batch cooldown
 * - Session persist (scan QR sekali)
 * - Graceful shutdown
 */

const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const http = require('http');
const https = require('https');
const fs = require('fs');
const path = require('path');

// ==============================================================================
// LOAD .ENV (Jika ada di folder agent atau root project)
// ==============================================================================
function loadEnvFile(envPath) {
    try {
        if (fs.existsSync(envPath)) {
            const content = fs.readFileSync(envPath, 'utf8');
            content.split(/\r?\n/).forEach(line => {
                const match = line.match(/^\s*([\w.-]+)\s*=\s*(.*)?\s*$/);
                if (match) {
                    const key = match[1];
                    let value = (match[2] || '').trim();
                    if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
                        value = value.slice(1, -1);
                    }
                    if (!process.env[key]) {
                        process.env[key] = value;
                    }
                }
            });
        }
    } catch (_) {}
}
loadEnvFile(path.join(__dirname, '.env'));
loadEnvFile(path.join(__dirname, '..', '.env'));

function resolveApiBaseUrl() {
    if (process.env.WA_API_URL) {
        return process.env.WA_API_URL;
    }
    const appUrl = process.env.APP_URL;
    if (appUrl) {
        // Jika ada port eksplisit atau domain produksi (bukan localhost murni)
        if (appUrl.includes(':8000') || (!appUrl.includes('localhost') && !appUrl.includes('127.0.0.1'))) {
            return appUrl;
        }
    }
    return 'http://localhost:8000';
}

const API_BASE_URL = resolveApiBaseUrl().replace(/\/+$/, '');
const AGENT_TOKEN = process.env.WA_AGENT_TOKEN || 'change-this-token-to-something-secure';
const AGENT_NAME = 'default';

const POLL_INTERVAL = 5;         // Detik — interval polling saat tidak ada antrean

// Jeda Acak Antar Pesan (Anti-Ban) — 20-40 detik lebih aman untuk 50+ kontak
const DELAY_MIN = parseInt(process.env.WA_DELAY_MIN || '20', 10);
const DELAY_MAX = parseInt(process.env.WA_DELAY_MAX || '40', 10);

// Batch Cooldown — batch kecil (3 pesan) + istirahat lebih lama
const BATCH_SIZE = 3;
const COOLDOWN_MIN = 60;
const COOLDOWN_MAX = 120;

// ==============================================================================
// LOGGING SYSTEM — Log ke file + console (rotasi harian)
// ==============================================================================
const LOG_DIR = path.join(__dirname, 'logs');

// Buat folder logs jika belum ada
if (!fs.existsSync(LOG_DIR)) {
    fs.mkdirSync(LOG_DIR, { recursive: true });
}

function getLogFileName() {
    const now = new Date();
    const date = now.toISOString().split('T')[0]; // YYYY-MM-DD
    return path.join(LOG_DIR, `agent-${date}.log`);
}

function log(level, message) {
    const ts = new Date().toLocaleTimeString('id-ID', { hour12: false });
    const dateStr = new Date().toISOString().split('T')[0];
    const line = `[${dateStr} ${ts}] [${level}] ${message}`;
    
    // Console output (dengan emoji untuk readability)
    console.log(message);
    
    // File output (tanpa emoji agar mudah di-grep)
    const cleanLine = line.replace(/[\u{1F300}-\u{1FAFF}\u{2600}-\u{27BF}\u{FE00}-\u{FEFF}]/gu, '').trim();
    try {
        fs.appendFileSync(getLogFileName(), cleanLine + '\n');
    } catch { /* ignore write errors */ }
}

function logInfo(msg)  { log('INFO', msg); }
function logWarn(msg)  { log('WARN', msg); }
function logError(msg) { log('ERROR', msg); }

// Bersihkan log lama (simpan 30 hari terakhir)
function cleanOldLogs() {
    try {
        const files = fs.readdirSync(LOG_DIR);
        const cutoff = Date.now() - (30 * 24 * 60 * 60 * 1000);
        for (const file of files) {
            if (!file.startsWith('agent-') || !file.endsWith('.log')) continue;
            const filePath = path.join(LOG_DIR, file);
            const stat = fs.statSync(filePath);
            if (stat.mtimeMs < cutoff) {
                fs.unlinkSync(filePath);
                logInfo(`[LOG] File log lama dihapus: ${file}`);
            }
        }
    } catch { /* ignore */ }
}

// ==============================================================================
// UTILITAS
// ==============================================================================
function timestamp() {
    return new Date().toLocaleTimeString('id-ID', { hour12: false });
}

function sleep(seconds) {
    return new Promise(resolve => setTimeout(resolve, seconds * 1000));
}

function randomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function normalizePhone(phone) {
    const digits = String(phone).replace(/\D/g, '');
    if (digits.startsWith('0')) return '62' + digits.slice(1);
    if (digits.startsWith('8')) return '62' + digits;
    return digits;
}

// ==============================================================================
// HTTP REQUEST
// ==============================================================================
function httpRequest(requestUrl, method = 'GET', data = null, timeout = 10000) {
    return new Promise((resolve) => {
        const parsed = new URL(requestUrl);
        const isHttps = parsed.protocol === 'https:';
        const transport = isHttps ? https : http;

        const options = {
            hostname: parsed.hostname,
            port: parsed.port || (isHttps ? 443 : 80),
            path: parsed.pathname + parsed.search,
            method: method,
            timeout: timeout,
            headers: {
                'Authorization': `Bearer ${AGENT_TOKEN}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'User-Agent': 'WaDesktopAgent-NodeJS/3.0',
            },
        };

        const req = transport.request(options, (res) => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                try {
                    resolve(body ? JSON.parse(body) : {});
                } catch {
                    resolve(null);
                }
            });
        });

        req.on('error', (err) => {
            logWarn(`[${timestamp()}] ⚠️ Request failed to ${requestUrl}: ${err.message}`);
            resolve(null);
        });

        req.on('timeout', () => {
            req.destroy();
            logWarn(`[${timestamp()}] ⚠️ Request timeout: ${requestUrl}`);
            resolve(null);
        });

        if (data !== null) {
            req.write(JSON.stringify(data));
        }
        req.end();
    });
}

// ==============================================================================
// API FUNCTIONS
// ==============================================================================
async function sendHeartbeat(whatsappReady) {
    const res = await httpRequest(`${API_BASE_URL}/api/agent/heartbeat`, 'POST', {
        agent_name: AGENT_NAME,
        whatsapp_ready: whatsappReady,
        metadata: {
            runner: 'nodejs-whatsapp-web-js-v3',
            delay_range: `${DELAY_MIN}-${DELAY_MAX}s`,
            mode: 'background',
            uptime_seconds: Math.floor((Date.now() - startTime) / 1000),
        },
    });
    if (res && res.success) {
        logInfo(`[${timestamp()}] ❤️ Heartbeat OK`);
    }
}

async function getPendingMessages() {
    const res = await httpRequest(`${API_BASE_URL}/api/agent/messages?limit=10`, 'GET');
    if (res && res.success) {
        return res.data || [];
    }
    return [];
}

async function markStatus(msgId, status, error = '') {
    const data = status === 'failed' ? { error } : null;
    await httpRequest(`${API_BASE_URL}/api/agent/messages/${msgId}/${status}`, 'POST', data);
}

// ==============================================================================
// NOTIFIKASI TELEGRAM (via Laravel API)
// ==============================================================================
async function notifyTelegram(message) {
    const res = await httpRequest(`${API_BASE_URL}/api/agent/notify`, 'POST', {
        message: message,
    });
    if (res && res.success) {
        logInfo(`[${timestamp()}] 📢 Notifikasi Telegram terkirim`);
    } else {
        logWarn(`[${timestamp()}] ⚠️ Gagal kirim notifikasi Telegram`);
    }
}

// ==============================================================================
// STATISTIK RUNTIME
// ==============================================================================
const startTime = Date.now();
const stats = {
    sent: 0,
    failed: 0,
    invalidNumbers: 0,
    disconnects: 0,
    lastDisconnect: null,
    lastReconnect: null,
};

// ==============================================================================
// DETEKSI OTOMATIS BROWSER (Chrome / Edge / Linux Chromium)
// ==============================================================================
function findBrowserPath() {
    if (process.env.PUPPETEER_EXECUTABLE_PATH) {
        return process.env.PUPPETEER_EXECUTABLE_PATH;
    }
    const candidates = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        path.join(process.env.LOCALAPPDATA || '', 'Google\\Chrome\\Application\\chrome.exe'),
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/google-chrome-stable',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
    ];
    for (const p of candidates) {
        if (p && fs.existsSync(p)) return p;
    }
    return undefined; // Puppeteer fallback
}

// ==============================================================================
// WHATSAPP CLIENT SETUP
// ==============================================================================
const browserExecutable = findBrowserPath();
const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: '.wwebjs_auth',
    }),
    puppeteer: {
        headless: true,
        ...(browserExecutable ? { executablePath: browserExecutable } : {}),
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--disable-extensions',
        ],
    },
});

let isWhatsAppReady = false;
let pollingStarted = false;

client.on('qr', (qr) => {
    logInfo('\n==================================================');
    logInfo('📱 SCAN QR CODE INI DENGAN WHATSAPP DI HP:');
    logInfo('==================================================');
    qrcode.generate(qr, { small: true });
    logInfo('==================================================');
    logInfo('💡 Buka WhatsApp di HP → Menu (⋮) → Linked Devices → Link a Device');
    logInfo('💡 Setelah scan berhasil, session tersimpan otomatis.');
    logInfo('==================================================\n');
});

client.on('ready', () => {
    isWhatsAppReady = true;
    logInfo('\n==================================================');
    logInfo('✅ WhatsApp Web SIAP! Agent berjalan di BACKGROUND.');
    logInfo('   Tidak perlu WhatsApp Desktop. Tidak ada jendela terbuka.');
    logInfo('==================================================\n');

    // Kirim notifikasi reconnect jika ini bukan startup pertama
    if (stats.disconnects > 0) {
        stats.lastReconnect = new Date().toLocaleString('id-ID');
        notifyTelegram(
            `✅ <b>WhatsApp Agent RECONNECTED</b>\n\n` +
            `🕐 <b>Waktu:</b> ${stats.lastReconnect}\n` +
            `📊 <b>Total disconnect:</b> ${stats.disconnects}x\n` +
            `💡 <i>Agent kembali online dan siap mengirim pesan.</i>`
        );
    }

    if (!pollingStarted) {
        pollingStarted = true;
        startPolling();
    }
});

client.on('authenticated', () => {
    logInfo(`[${timestamp()}] 🔐 Autentikasi berhasil — session tersimpan.`);
});

client.on('auth_failure', (msg) => {
    logError(`[${timestamp()}] ❌ Autentikasi GAGAL: ${msg}`);
    logError('   Hapus folder .wwebjs_auth/ lalu jalankan ulang untuk scan QR baru.');
    notifyTelegram(
        `❌ <b>WhatsApp Agent AUTH GAGAL</b>\n\n` +
        `🕐 <b>Waktu:</b> ${new Date().toLocaleString('id-ID')}\n` +
        `📛 <b>Error:</b> ${msg}\n` +
        `💡 <i>Perlu scan QR code ulang. Hapus folder .wwebjs_auth/ lalu restart agent.</i>`
    );
    process.exit(1);
});

client.on('disconnected', (reason) => {
    isWhatsAppReady = false;
    stats.disconnects++;
    stats.lastDisconnect = new Date().toLocaleString('id-ID');

    logError(`[${timestamp()}] ⚠️ WhatsApp TERPUTUS: ${reason}`);
    logInfo('   Mencoba reconnect otomatis...');

    // Kirim notifikasi disconnect ke Telegram
    notifyTelegram(
        `⚠️ <b>WhatsApp Agent DISCONNECTED</b>\n\n` +
        `🕐 <b>Waktu:</b> ${stats.lastDisconnect}\n` +
        `📛 <b>Alasan:</b> ${reason}\n` +
        `📊 <b>Total disconnect:</b> ${stats.disconnects}x\n` +
        `🔄 <i>Mencoba reconnect otomatis...</i>\n\n` +
        `💡 <i>Jika tidak reconnect dalam 5 menit, restart agent manual.</i>`
    );

    // Coba reconnect otomatis setelah delay
    setTimeout(() => {
        if (!isWhatsAppReady) {
            logInfo(`[${timestamp()}] 🔄 Mencoba initialize ulang...`);
            try { client.initialize(); } catch { /* ignore */ }
        }
    }, 30000);
});

// ==============================================================================
// KIRIM PESAN — DENGAN RETRY CERDAS
// ==============================================================================
async function sendViaWhatsApp(phone, message) {
    const cleanPhone = normalizePhone(phone);
    const chatId = `${cleanPhone}@c.us`;

    logInfo(`[${timestamp()}] 💬 Mengirim pesan ke: ${cleanPhone} (background)`);

    try {
        // Cek apakah nomor terdaftar di WhatsApp
        const isRegistered = await client.isRegisteredUser(chatId);
        if (!isRegistered) {
            stats.invalidNumbers++;
            logWarn(`[${timestamp()}] ⚠️ Nomor ${cleanPhone} TIDAK terdaftar di WhatsApp`);
            return { 
                success: false, 
                error: `Nomor ${cleanPhone} tidak terdaftar di WhatsApp`,
                errorType: 'INVALID_NUMBER', // Tidak perlu retry
            };
        }

        // Kirim pesan langsung — tanpa membuka jendela apapun
        await client.sendMessage(chatId, message);
        stats.sent++;
        logInfo(`[${timestamp()}] ✅ Pesan terkirim ke ${cleanPhone} (background)`);
        return { success: true };
    } catch (err) {
        stats.failed++;
        logError(`[${timestamp()}] ❌ Gagal kirim ke ${cleanPhone}: ${err.message}`);

        // Klasifikasi error untuk retry cerdas
        let errorType = 'UNKNOWN';
        if (err.message.includes('not found') || err.message.includes('invalid')) {
            errorType = 'INVALID_NUMBER';
        } else if (err.message.includes('timeout') || err.message.includes('ECONNREFUSED')) {
            errorType = 'CONNECTION';
        } else if (err.message.includes('rate') || err.message.includes('spam')) {
            errorType = 'RATE_LIMIT';
        }

        return { success: false, error: err.message, errorType };
    }
}

// ==============================================================================
// MAIN POLLING LOOP
// ==============================================================================
async function startPolling() {
    logInfo('==================================================');
    logInfo('🛡️  WA Desktop Agent v3.0 (Professional Background Mode)');
    logInfo(`🌐 Backend API   : ${API_BASE_URL}`);
    logInfo(`⏳ Jeda Acak      : ${DELAY_MIN} s.d. ${DELAY_MAX} detik per pesan`);
    logInfo(`☕ Cooldown Batch : Tiap ${BATCH_SIZE} pesan istirahat ${COOLDOWN_MIN}-${COOLDOWN_MAX} detik`);
    logInfo(`📝 Log File       : ${LOG_DIR}/`);
    logInfo('🔇 Mode           : BACKGROUND (tidak ada jendela terbuka)');
    logInfo('==================================================');

    let lastHeartbeat = 0;
    let consecutiveSentCount = 0;

    // Bersihkan log lama saat startup
    cleanOldLogs();

    while (true) {
        try {
            const now = Date.now() / 1000;

            // Heartbeat setiap 30 detik (dikurangi dari 15 agar tidak terlalu sering)
            if (now - lastHeartbeat >= 30) {
                await sendHeartbeat(isWhatsAppReady);
                lastHeartbeat = now;
            }

            if (!isWhatsAppReady) {
                logInfo(`[${timestamp()}] ⏳ Menunggu koneksi WhatsApp Web...`);
                await sleep(POLL_INTERVAL);
                continue;
            }

            // Ambil pesan pending dari Laravel API
            const messages = await getPendingMessages();

            if (messages.length > 0) {
                logInfo(`[${timestamp()}] 📬 Ditemukan ${messages.length} pesan pending...`);

                for (const msg of messages) {
                    const { id: msgId, phone_number: phone, message: text } = msg;

                    // Tandai sedang diproses
                    await markStatus(msgId, 'processing');

                    // Kirim pesan via WhatsApp Web (background)
                    const result = await sendViaWhatsApp(phone, text);

                    if (result.success) {
                        await markStatus(msgId, 'sent');
                        consecutiveSentCount++;
                    } else {
                        // Retry cerdas berdasarkan jenis error
                        let errorMsg = result.error || 'Gagal kirim via WhatsApp Web';
                        
                        if (result.errorType === 'INVALID_NUMBER') {
                            // Nomor tidak valid — langsung failed, jangan retry
                            errorMsg = `[NO RETRY] ${errorMsg}`;
                        } else if (result.errorType === 'RATE_LIMIT') {
                            // Rate limited — tunggu lebih lama
                            logWarn(`[${timestamp()}] 🚫 Rate limited! Istirahat 3 menit...`);
                            await sleep(180);
                        }
                        
                        await markStatus(msgId, 'failed', errorMsg);
                    }

                    // Batch cooldown
                    if (consecutiveSentCount >= BATCH_SIZE) {
                        const cooldown = randomInt(COOLDOWN_MIN, COOLDOWN_MAX);
                        logInfo(`[${timestamp()}] ☕ Sudah kirim ${consecutiveSentCount} pesan. Istirahat ${cooldown} detik...`);
                        await sleep(cooldown);
                        consecutiveSentCount = 0;
                    } else {
                        // Jeda acak antar pesan (anti-ban)
                        const delay = randomInt(DELAY_MIN, DELAY_MAX);
                        logInfo(`[${timestamp()}] ⏳ Jeda acak ${delay} detik...`);
                        await sleep(delay);
                    }
                }
            }
        } catch (err) {
            if (err.message && err.message.includes('SIGINT')) break;
            logError(`[${timestamp()}] ❌ Error: ${err.message}`);
        }

        await sleep(POLL_INTERVAL);
    }
}

// ==============================================================================
// GRACEFUL SHUTDOWN
// ==============================================================================
let isShuttingDown = false;

async function gracefulShutdown() {
    if (isShuttingDown) return;
    isShuttingDown = true;

    const uptime = Math.floor((Date.now() - startTime) / 1000);
    const uptimeStr = `${Math.floor(uptime/3600)}j ${Math.floor((uptime%3600)/60)}m ${uptime%60}d`;

    logInfo('\n🛑 Agent dihentikan. Menutup Chrome...');
    logInfo(`📊 Statistik sesi: Terkirim=${stats.sent} Gagal=${stats.failed} NomorInvalid=${stats.invalidNumbers} Disconnect=${stats.disconnects} Uptime=${uptimeStr}`);
    
    try {
        await client.destroy();
        logInfo('✅ Chrome berhasil ditutup.');
    } catch {
        try {
            const { execSync } = require('child_process');
            execSync('taskkill /F /IM chrome.exe /T 2>nul', { stdio: 'ignore' });
        } catch { /* ignore */ }
    }
    process.exit(0);
}

process.on('SIGINT', gracefulShutdown);
process.on('SIGTERM', gracefulShutdown);
setTimeout(() => { /* keep alive */ }, 2147483647);

// ==============================================================================
// START
// ==============================================================================
logInfo('==================================================');
logInfo('🚀 WA Desktop Agent v3.0 — Professional Background Mode');
logInfo('   whatsapp-web.js + Logging + Telegram Alerts');
logInfo('   100% background. Auto-reconnect. Smart retry.');
logInfo('==================================================');
logInfo(`[${timestamp()}] Memulai koneksi WhatsApp Web...`);

client.initialize();
