#!/bin/bash
cd /Users/Shared/Soft.io.vn/Code/cotuongchamtop

echo "🧪 Testing Matching Logic via Web Interface"
echo "============================================="
echo ""

# Start server in background if not already running
if ! curl -s http://localhost:8001 >/dev/null 2>&1; then
    echo "🚀 Starting Laravel server..."
    php artisan serve --host=0.0.0.0 --port=8001 &
    SERVER_PID=$!
    sleep 3
else
    echo "✅ Server already running on port 8001"
fi

echo ""
echo "📋 MANUAL TEST INSTRUCTIONS:"
echo "============================="
echo ""
echo "1. 🌐 Open TWO browser tabs/windows"
echo "2. 📱 Go to: http://localhost:8001"
echo "3. 🎮 Tab 1: Click 'Chơi nhanh' (Quick Match)"
echo "   ⏳ Should show 'Đang tìm đối thủ...' (Looking for opponent)"
echo ""
echo "4. 🎮 Tab 2: Click 'Chơi nhanh' (Quick Match)" 
echo "   ✅ EXPECTED: Both tabs should join the SAME room"
echo "   ❌ BUG: Tab 2 creates new empty room"
echo ""
echo "5. 🔍 Check the room URLs - they should be identical!"
echo ""

# Check database state
echo "🗄️  Current Database State:"
echo "=========================="
php artisan tinker --execute="
\$rooms = App\Models\Room::whereNull('result')->latest()->limit(5)->get();
if(\$rooms->count() > 0) {
    echo 'Recent rooms:\n';
    foreach(\$rooms as \$room) {
        echo '  Room ' . \$room->code . ': host_session=' . (\$room->host_session ?: 'null') . ', guest_session=' . (\$room->guest_session ?: 'null') . '\n';
    }
} else {
    echo 'No active rooms found\n';
}
"

echo ""
echo "💡 TIP: If both players end up in the same room = FIX WORKS! ✅"
echo "💡 TIP: If players are in different rooms = Still broken ❌"
echo ""