#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

IP="${1:-$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null || true)}"

if [ -z "$IP" ]; then
    echo "Tidak bisa mendeteksi IP LAN. Jalankan: ./scripts/setup-lan.sh 192.168.1.149"
    exit 1
fi

mkdir -p storage/certs

mkcert -install || echo "Peringatan: CA belum dipercaya di mesin ini. Instal manual jika perlu."
mkcert \
    -cert-file storage/certs/lan.crt \
    -key-file storage/certs/lan.key \
    localhost 127.0.0.1 ::1 "$IP"

cp "$(mkcert -CAROOT)/rootCA.pem" public/mkcert-rootCA.crt

if grep -q '^APP_URL=' .env; then
    perl -pi -e "s|^APP_URL=.*|APP_URL=https://$IP:8443|" .env
else
    echo "APP_URL=https://$IP:8443" >> .env
fi

php artisan config:clear >/dev/null

echo ""
echo "Sertifikat & APP_URL diperbarui untuk IP $IP"
echo "Buka: https://$IP:8443"
echo "Di HP, instal dulu sertifikat CA: https://$IP:8443/mkcert-rootCA.crt"
echo "Lalu restart proxy: node scripts/https-proxy.mjs"
