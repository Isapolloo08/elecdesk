// Animate number counts
function animateValue(id, start, end, duration) {
    const obj = document.getElementById(id);
    if (!obj) return;
    
    let startTime = null;
    
    const step = (timestamp) => {
        if (!startTime) startTime = timestamp;
        const progress = Math.min((timestamp - startTime) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    
    window.requestAnimationFrame(step);
}

// Initialize stats counters
document.addEventListener('DOMContentLoaded', function() {
    // Check if elements exist before trying to animate them
    if (document.getElementById("candidates-count")) {
        animateValue("candidates-count", 0, 32, 2000);
    }
    if (document.getElementById("positions-count")) {
        animateValue("positions-count", 0, 8, 2000);
    }
    if (document.getElementById("profiles-count")) {
        animateValue("profiles-count", 0, 1548, 2000);
    }
    if (document.getElementById("days-count")) {
        animateValue("days-count", 0, 10, 2000);
    }
    
    // Category selection
    const categories = document.querySelectorAll('.category-badge');
    
    categories.forEach(category => {
        category.addEventListener('click', function() {
            categories.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
    });
});