#!/bin/bash

echo "🧪 Testing API Matching Logic"
echo "=============================="
echo ""

BASE_URL="http://localhost:8001"
SESSION1="test_session_$(date +%s)_1" 
SESSION2="test_session_$(date +%s)_2"

echo "👤 Session 1: $SESSION1"
echo "👤 Session 2: $SESSION2"
echo ""

# Test 1: First player
echo "🎮 Test 1: First player requests match"
RESPONSE1=$(curl -s -X POST "$BASE_URL/test-anonymous-quick-match" \
  -H "Content-Type: application/json" \
  -d "{\"session_id\": \"$SESSION1\"}")

echo "Response 1: $RESPONSE1"
echo ""

# Extract room code from response 1
ROOM1=$(echo "$RESPONSE1" | grep -o '"room_code":"[^"]*"' | cut -d'"' -f4)
echo "🏠 Room 1 Code: $ROOM1"
echo ""

# Wait a bit
sleep 2

# Test 2: Second player  
echo "🎮 Test 2: Second player requests match"
RESPONSE2=$(curl -s -X POST "$BASE_URL/test-anonymous-quick-match" \
  -H "Content-Type: application/json" \
  -d "{\"session_id\": \"$SESSION2\"}")

echo "Response 2: $RESPONSE2"
echo ""

# Extract room code from response 2
ROOM2=$(echo "$RESPONSE2" | grep -o '"room_code":"[^"]*"' | cut -d'"' -f4)
echo "🏠 Room 2 Code: $ROOM2"
echo ""

# Compare results
echo "📊 Test Results Analysis:"
echo "========================="
if [ "$ROOM1" = "$ROOM2" ] && [ ! -z "$ROOM1" ]; then
    echo "✅ SUCCESS: Both players joined the same room ($ROOM1)"
    echo "✅ Matching logic is working correctly!"
else
    echo "❌ FAIL: Players joined different rooms"
    echo "   Player 1 room: $ROOM1"
    echo "   Player 2 room: $ROOM2"
    echo "❌ Matching logic still has issues"
fi

echo ""
echo "🔍 Testing status check endpoints:"
echo "=================================="

# Check status for both players
STATUS1=$(curl -s -X POST "$BASE_URL/test-check-anonymous-match-status" \
  -H "Content-Type: application/json" \
  -d "{\"session_id\": \"$SESSION1\"}")

STATUS2=$(curl -s -X POST "$BASE_URL/test-check-anonymous-match-status" \
  -H "Content-Type: application/json" \
  -d "{\"session_id\": \"$SESSION2\"}")

echo "Status Player 1: $STATUS1"
echo "Status Player 2: $STATUS2"
echo ""

echo "✅ API Test completed!"