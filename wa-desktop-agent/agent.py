"""
WA Desktop Agent — Lightweight Python Runner (Anti-Ban & Anti-Spam Protected)
Menggunakan random jitter delay (5-110s), batch cooldown, dan simulasi pengetikan manusia.
"""

import time
import random
import urllib.parse
import urllib.request
import json
import os
import sys
import subprocess

# ==============================================================================
# KONFIGURASI KEAMANAN & ANTI-BAN
# ==============================================================================
API_BASE_URL = os.getenv("WA_API_URL", "http://localhost:8000").rstrip("/")
AGENT_TOKEN = os.getenv("WA_AGENT_TOKEN", "change-this-token-to-something-secure")
AGENT_NAME = "default"

POLL_INTERVAL = 5        # Interval polling database/API saat tidak ada antrean (detik)

# Jeda Acak Antar Pesan (6 sampai 15 detik)
DELAY_MIN = int(os.getenv("WA_DELAY_MIN", 6))     # Detik minimal (6s)
DELAY_MAX = int(os.getenv("WA_DELAY_MAX", 15))    # Detik maksimal (15s)

# Istirahat Berkala (Batch Cooldown)
# Setiap X pesan terkirim, ambil istirahat ekstra agar mirip perilaku manusia
BATCH_SIZE = 10          # Jumlah pesan sebelum istirahat batch
COOLDOWN_MIN = 60        # Minimal istirahat batch (detik)
COOLDOWN_MAX = 120       # Maksimal istirahat batch (detik)

HEADERS = {
    "Authorization": f"Bearer {AGENT_TOKEN}",
    "Accept": "application/json",
    "Content-Type": "application/json",
    "User-Agent": "WaDesktopAgent-Python/1.0"
}

def http_request(url: str, method: str = "GET", data: dict = None, timeout: int = 5):
    """Fungsi HTTP request menggunakan urllib bawaan Python standard library."""
    try:
        req_data = None
        if data is not None:
            req_data = json.dumps(data).encode("utf-8")

        req = urllib.request.Request(url, data=req_data, headers=HEADERS, method=method)
        with urllib.request.urlopen(req, timeout=timeout) as response:
            res_body = response.read().decode("utf-8")
            if res_body:
                return json.loads(res_body)
            return {}
    except urllib.error.HTTPError as e:
        err_body = e.read().decode("utf-8")
        print(f"[{time.strftime('%H:%M:%S')}] ⚠️ HTTP {e.code}: {err_body}")
        return None
    except Exception as e:
        print(f"[{time.strftime('%H:%M:%S')}] ⚠️ Request failed to {url}: {e}")
        return None

def normalize_phone(phone: str) -> str:
    digits = "".join(filter(str.isdigit, str(phone)))
    if digits.startswith("0"):
        return "62" + digits[1:]
    if digits.startswith("8"):
        return "62" + digits
    return digits

def send_heartbeat():
    url = f"{API_BASE_URL}/api/agent/heartbeat"
    payload = {
        "agent_name": AGENT_NAME,
        "whatsapp_ready": True,
        "metadata": {
            "runner": "python-anti-ban",
            "delay_range": f"{DELAY_MIN}-{DELAY_MAX}s"
        }
    }
    res = http_request(url, method="POST", data=payload)
    if res and res.get("success"):
        print(f"[{time.strftime('%H:%M:%S')}] ❤️ Heartbeat sent OK (Agent Online)")

def get_pending_messages():
    url = f"{API_BASE_URL}/api/agent/messages?limit=10"
    res = http_request(url, method="GET")
    if res and res.get("success"):
        return res.get("data", [])
    return []

def mark_status(msg_id: int, status: str, error: str = ""):
    url = f"{API_BASE_URL}/api/agent/messages/{msg_id}/{status}"
    payload = {"error": error} if status == "failed" else None
    http_request(url, method="POST", data=payload)

def send_via_whatsapp(phone: str, message: str) -> bool:
    clean_phone = normalize_phone(phone)
    encoded_msg = urllib.parse.quote(message)
    uri = f"whatsapp://send?phone={clean_phone}&text={encoded_msg}"

    print(f"[{time.strftime('%H:%M:%S')}] 💬 Membuka WhatsApp chat: {clean_phone}")
    os.system(f'start "" "{uri}"')

    # Jeda acak (3.5 - 6.0 detik) agar WhatsApp fokus dan meniru delay ketik manusia
    focus_delay = round(random.uniform(3.5, 6.0), 2)
    time.sleep(focus_delay)

    # Gunakan WScript.Shell bawaan Windows untuk menekan ENTER
    try:
        cmd = '$wshell = New-Object -ComObject wscript.shell; Start-Sleep -Milliseconds 400; $wshell.SendKeys("{ENTER}")'
        subprocess.run(["powershell", "-NoProfile", "-Command", cmd], check=True)
        print(f"[{time.strftime('%H:%M:%S')}] ✅ Pesan terkirim ke {clean_phone}")
        return True
    except Exception as e:
        print(f"[{time.strftime('%H:%M:%S')}] ❌ Gagal menekan tombol ENTER: {e}")
        return False

def main():
    print("==================================================")
    print("🛡️  WA Desktop Agent (Mode Anti-Ban & Anti-Spam) Aktif")
    print(f"🌐 Backend API   : {API_BASE_URL}")
    print(f"⏳ Jeda Acak      : {DELAY_MIN} s.d. {DELAY_MAX} detik per pesan")
    print(f"☕ Cooldown Batch : Tiap {BATCH_SIZE} pesan istirahat {COOLDOWN_MIN}-{COOLDOWN_MAX} detik")
    print("==================================================")

    last_heartbeat = 0
    consecutive_sent_count = 0

    while True:
        try:
            now = time.time()
            if now - last_heartbeat >= 15:
                send_heartbeat()
                last_heartbeat = now

            messages = get_pending_messages()
            if messages:
                print(f"[{time.strftime('%H:%M:%S')}] 📬 Ditemukan {len(messages)} pesan pending...")
                for msg in messages:
                    msg_id = msg["id"]
                    phone = msg["phone_number"]
                    text = msg["message"]

                    mark_status(msg_id, "processing")
                    success = send_via_whatsapp(phone, text)

                    if success:
                        mark_status(msg_id, "sent")
                        consecutive_sent_count += 1
                    else:
                        mark_status(msg_id, "failed", error="Gagal automasi pengiriman WhatsApp Desktop")

                    # Cek apakah perlu istirahat batch (Cooldown)
                    if consecutive_sent_count >= BATCH_SIZE:
                        cooldown = random.randint(COOLDOWN_MIN, COOLDOWN_MAX)
                        print(f"[{time.strftime('%H:%M:%S')}] ☕ Sudah mengirim {consecutive_sent_count} pesan berturut-turut. Mengambil jeda istirahat {cooldown} detik (Anti-Spam Break)...")
                        time.sleep(cooldown)
                        consecutive_sent_count = 0
                    else:
                        # Jeda Acak (5 s.d. 110 detik) sebelum pesan berikutnya
                        random_delay = random.randint(DELAY_MIN, DELAY_MAX)
                        print(f"[{time.strftime('%H:%M:%S')}] ⏳ Jeda acak {random_delay} detik sebelum pesan berikutnya...")
                        time.sleep(random_delay)

        except KeyboardInterrupt:
            print("\n🛑 Agent dihentikan oleh user.")
            break
        except Exception as e:
            print(f"[{time.strftime('%H:%M:%S')}] ❌ Error: {e}")

        time.sleep(POLL_INTERVAL)

if __name__ == "__main__":
    main()
