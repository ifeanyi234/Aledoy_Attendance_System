<script>
function updateSystemClock() {
    const timeDisplay = document.getElementById('current-system-time');
    if (timeDisplay) {
        const now = new Date();
        timeDisplay.textContent = now.toLocaleDateString('en-US', { 
            weekday: 'short', 
            month: 'short', 
            day: 'numeric' 
        }) + ' | ' + now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            hour12: true 
        });
    }
}
// Run clock instantly and keep it updating every second
updateSystemClock();
setInterval(updateSystemClock, 1000);
</script>