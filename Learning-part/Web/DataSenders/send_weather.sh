#!/bin/bash

# ========== CONFIGURATION ==========
STATION_SERIAL="WST-202601-001"
PHP_URL="./DataHandler.php"
# ===================================

# Function to generate random number between min and max
rand() {
    echo $((RANDOM % ($2 - $1 + 1) + $1))
}

# Function to generate weather data
generate_data() {
    local timestamp=$(date "+%Y-%m-%d %H:%M:%S").$(printf "%03d" $(($(date +%N)/1000000)))
    local temperature=$(echo "scale=1; $(rand 150 350)/10" | bc)
    local humidity=$(echo "scale=1; $(rand 300 900)/10" | bc)
    local pressure=$(echo "scale=2; $(rand 98000 103000)/100" | bc)
    local light=$(echo "scale=1; $(rand 0 1200)" | bc)
    local gas=$(rand 100 900)
    
    # Day/Night effect (simplified)
    local hour=$(date +%H)
    if [ $hour -lt 6 ] || [ $hour -gt 18 ]; then
        light=$(echo "scale=1; $(rand 0 50)" | bc)
    fi
    
    echo "station_serial=$STATION_SERIAL&timestamp=$timestamp&temperature=$temperature&humidity=$humidity&pressure=$pressure&light=$light&gas=$gas"
}

# Send single data packet
send_single() {
    echo "Sending single data packet..."
    local data=$(generate_data)
    
    response=$(curl -s -X POST "$PHP_URL" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "$data")
    
    echo "Response: $response"
}

# Send continuous data
send_continuous() {
    local count=0
    echo "Starting continuous transmission (every 10 seconds)..."
    echo "Press Ctrl+C to stop"
    
    while true; do
        count=$((count + 1))
        local data=$(generate_data)
        local current_time=$(date "+%H:%M:%S")
        
        # Extract values for display
        temp=$(echo "$data" | grep -oP 'temperature=\K[^&]+')
        humidity=$(echo "$data" | grep -oP 'humidity=\K[^&]+')
        
        response=$(curl -s -X POST "$PHP_URL" \
            -H "Content-Type: application/x-www-form-urlencoded" \
            -d "$data")
        
        if [ $? -eq 0 ]; then
            echo "[$current_time] #$count | T:${temp}°C H:${humidity}% - OK"
        else
            echo "[$current_time] #$count | ERROR: Connection failed"
        fi
        
        sleep 10
    done
}

# Menu
echo "========================================"
echo "   WEATHER STATION DATA SIMULATOR"
echo "========================================"
echo ""
echo "Configuration:"
echo "  Station: $STATION_SERIAL"
echo "  PHP URL: $PHP_URL"
echo ""
echo "Options:"
echo "  1. Send single data packet"
echo "  2. Send continuously (every 10 seconds)"
echo "  3. Exit"
echo ""
read -p "Your choice (1-3): " choice

case $choice in
    1)
        send_single
        ;;
    2)
        send_continuous
        ;;
    3)
        echo "Goodbye!"
        exit 0
        ;;
    *)
        echo "Invalid choice"
        exit 1
        ;;
esac