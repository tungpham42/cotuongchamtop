#!/bin/bash
set -e

# Deployment script for cotuong.top
# NOTE: Only run this script when explicitly requested by the user.

echo "===> [1/4] Checking local Git status..."
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$CURRENT_BRANCH" != "master" ]; then
    echo "WARNING: You are on branch '$CURRENT_BRANCH', not 'master'."
fi

# HARD RULE: Code nào deploy lên production thì BẮT BUỘC phải ghi vào Git
UNCOMMITTED_CHANGES=$(git status --porcelain)
if [ -n "$UNCOMMITTED_CHANGES" ]; then
    echo "🚨 ERROR: Code nào deploy lên production thì BẮT BUỘC phải ghi vào Git!"
    echo "Các file chưa được commit:"
    echo "$UNCOMMITTED_CHANGES"
    echo "Vui lòng commit toàn bộ thay đổi vào Git trước khi deploy."
    exit 1
fi

# Check if local is ahead of remote
UNPUSHED_COMMITS=$(git log origin/master..HEAD --oneline 2>/dev/null || true)
if [ -n "$UNPUSHED_COMMITS" ]; then
    echo "🚨 ERROR: Nhánh local đang có commit chưa push lên remote GitHub:"
    echo "$UNPUSHED_COMMITS"
    echo "Vui lòng chạy 'git push origin master' trước khi deploy."
    exit 1
fi

echo "===> [2/4] Triggering deployment on server via SSH (cotuong)..."
ssh cotuong bash << 'EOF'
set -e
cd /home/cotuong/domains/cotuong.top/public_html

echo "--> Pulling latest changes from master..."
rm -f .git/index.lock
git checkout -- public/storage public/clickheat 2>/dev/null || true
git pull origin master

echo "--> Running migrations (if any)..."
php artisan migrate --force

echo "--> Clearing and rebuilding caches..."
php artisan optimize:clear
php artisan optimize
php artisan storage:link 2>/dev/null || true

echo "--> Ensuring AI Engine Pool is healthy..."
php artisan xiangqi:pool:ensure || true

echo "--> Deployment complete on server!"
EOF

echo "===> [3/4] Verifying production HTTP response..."
STATUS_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://cotuong.top)
echo "HTTP response status code from https://cotuong.top: $STATUS_CODE"

if [ "$STATUS_CODE" = "200" ] || [ "$STATUS_CODE" = "301" ] || [ "$STATUS_CODE" = "302" ]; then
    echo "===> [4/4] Deploy SUCCESSFUL!"
else
    echo "===> [4/4] Warning: Unexpected HTTP status code $STATUS_CODE"
fi
