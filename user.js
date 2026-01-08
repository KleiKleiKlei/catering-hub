// User JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Load user calendar
    if (document.getElementById('userCalendarWeek')) {
        loadUserCalendar();
    }

    // Load user profile
    if (document.getElementById('profileName')) {
        loadUserProfile();
    }
});

function loadUserCalendar() {
    const week = getCurrentWeek();
    const userCalendarWeek = document.getElementById('userCalendarWeek');
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    userCalendarWeek.innerHTML = '';

    // Dummy food data - replace with backend call
    const foodData = {
        '2025-12-14': { name: 'Grilled Chicken', desc: 'Tender grilled chicken with herbs' },
        '2025-12-15': { name: 'Fish Tacos', desc: 'Fresh fish tacos with lime sauce' },
        '2025-12-16': { name: 'Beef Steak', desc: 'Premium beef steak with garlic butter' },
        '2025-12-17': { name: 'Pasta Carbonara', desc: 'Classic Italian pasta' },
        '2025-12-18': { name: 'Vegetarian Bowl', desc: 'Fresh organic vegetables' }
    };

    week.forEach((date, index) => {
        const dateString = date.toISOString().split('T')[0];
        const food = foodData[dateString];

        const dayDiv = document.createElement('div');
        dayDiv.className = 'calendar-day';
        dayDiv.innerHTML = `
            <h3>${dayNames[index]}</h3>
            <p>${formatDate(date)}</p>
            ${food ? `
                <div style="margin-top: 1rem; font-size: 0.9rem;">
                    <strong>${food.name}</strong>
                    <p style="margin-top: 0.5rem; color: #888;">${food.desc}</p>
                    <button class="btn btn-primary" style="margin-top: 0.5rem; padding: 0.5rem 1rem; font-size: 0.85rem;">Select</button>
                </div>
            ` : '<p style="color: #ccc;">No menu available</p>'}
        `;
        
        if (food) {
            dayDiv.addEventListener('click', function() {
                selectFood(food.name, date, this);
            });
        }

        userCalendarWeek.appendChild(dayDiv);
    });
}

function selectFood(foodName, date, element) {
    element.classList.toggle('active');
    
    // Add to selected foods
    const selectedFoods = document.getElementById('selectedFoods');
    const foodItem = document.createElement('div');
    foodItem.className = 'food-item';
    foodItem.innerHTML = `
        <div class="food-info">
            <h3>${foodName}</h3>
            <p>${formatDate(date)}</p>
        </div>
        <button class="btn btn-danger" onclick="this.parentElement.remove()">Remove</button>
    `;
    
    selectedFoods.appendChild(foodItem);
}

function loadUserProfile() {
    const user = UserSession.getUser();
    
    // Load profile data - replace with backend call
    const profile = {
        name: 'John Doe',
        email: 'john@example.com',
        phone: '123-456-7890'
    };

    document.getElementById('profileName').textContent = profile.name;
    document.getElementById('profileEmail').textContent = profile.email;
    document.getElementById('profilePhone').textContent = profile.phone;
}

function editProfile() {
    alert('Edit profile functionality coming soon');
    // Implement edit profile modal
}
