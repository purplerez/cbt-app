#!/bin/bash

# VPS Configuration
HOST_VPS="43.157.213.192"
USER_VPS="ubuntu"
PASSWORD_VPS="ocean-22#-shadow"
APP_PATH="/home/ubuntu/public_html/cbt-app"  # Sesuaikan path aplikasi Anda di VPS

echo "🚀 Memulai deployment ke VPS..."
echo "Host: $HOST_VPS"
echo "User: $USER_VPS"
echo ""

# Function untuk menjalankan perintah SSH
run_ssh_command() {
    local description=$1
    local command=$2
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "📌 $description"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    sshpass -p "$PASSWORD_VPS" ssh -o StrictHostKeyChecking=no "$USER_VPS@$HOST_VPS" "$command"
    
    if [ $? -eq 0 ]; then
        echo "✅ $description - BERHASIL"
    else
        echo "❌ $description - GAGAL"
    fi
    echo ""
}

# Step 1: Git Pull
run_ssh_command "Step 1: Git Pull" "cd $APP_PATH && git pull"

# Step 2: Update .env dengan PUBLIC_STORAGE_ROOT
run_ssh_command "Step 2: Update .env" "cd $APP_PATH && echo 'PUBLIC_STORAGE_ROOT=../../public_html/cbt-app/public/storage' >> .env"

# Step 3: Clear Config
run_ssh_command "Step 3: Clear Config" "cd $APP_PATH && php artisan config:clear"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✨ Deployment selesai!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
