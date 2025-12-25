// Admin JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Load dashboard stats
    if (document.getElementById('totalUsers')) {
        loadDashboardStats();
    }

    // Load calendar for menu
    if (document.getElementById('calendarWeek')) {
        loadAdminCalendar();
    }

    // Load users table
    if (document.getElementById('usersTableBody')) {
        loadUsersTable();
    }

    // Handle food form submission
    const foodForm = document.getElementById('foodForm');
    if (foodForm) {
        foodForm.addEventListener('submit', handleFoodFormSubmit);
    }

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', handleUserSearch);
    }
});

function loadDashboardStats() {
    // Fetch users from backend/database
    // For now, using dummy data
    const stats = {
        totalUsers: 5,
        activeUsers: 4,
        disabledUsers: 1
    };

    document.getElementById('totalUsers').textContent = stats.totalUsers;
    document.getElementById('activeUsers').textContent = stats.activeUsers;
    document.getElementById('disabledUsers').textContent = stats.disabledUsers;
}

function loadAdminCalendar() {
    // Get calendar element - it might not exist on dashboard page
    const calendarWeek = document.getElementById('calendarWeek');
    if (!calendarWeek) {
        return; // Calendar element doesn't exist, skip
    }

    // Create array of next 7 days
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const today = new Date();
    
    calendarWeek.innerHTML = '';

    for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        
        const dayDiv = document.createElement('div');
        dayDiv.className = 'calendar-day';
        
        const dateStr = date.toISOString().split('T')[0];
        const monthDay = (date.getMonth() + 1) + '/' + date.getDate();
        
        dayDiv.innerHTML = `
            <h3>${dayNames[date.getDay()]}</h3>
            <p>${monthDay}</p>
            <small>Click to edit</small>
        `;
        
        dayDiv.addEventListener('click', function() {
            selectDay(dateStr, this);
        });

        calendarWeek.appendChild(dayDiv);
    }
}

function selectDay(dateStr, element) {
    // Remove active class from all days
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.classList.remove('active');
    });

    // Add active class to selected day
    element.classList.add('active');

    // Set the date in the form
    const foodDateInput = document.getElementById('foodDate');
    if (foodDateInput) {
        foodDateInput.value = dateStr;
    }
}

function handleFoodFormSubmit(e) {
    e.preventDefault();

    const foodDate = document.getElementById('foodDate').value;
    const foodName = document.getElementById('foodName').value;
    const foodDesc = document.getElementById('foodDesc').value;
    const foodImage = document.getElementById('foodImage').files[0];

    if (!foodDate || !foodName) {
        alert('Please fill in all required fields');
        return;
    }

    // Create FormData for file upload
    const formData = new FormData();
    formData.append('menu_date', foodDate);
    formData.append('food_name', foodName);
    formData.append('food_description', foodDesc);
    if (foodImage) {
        formData.append('food_image', foodImage);
    }

    fetch('api-menu.php?action=add_food', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Food item added successfully!');
            e.target.reset();
            // Reload calendar to show new item
            loadAdminCalendar();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add food item. Please try again.');
    });
}

function loadUsersTable() {
    // Dummy user data - replace with backend call
    const users = [
        { id: 1, name: 'John Doe', email: 'john@example.com', phone: '123-456-7890', status: 'active' },
        { id: 2, name: 'Jane Smith', email: 'jane@example.com', phone: '098-765-4321', status: 'active' },
        { id: 3, name: 'Bob Johnson', email: 'bob@example.com', phone: '555-555-5555', status: 'disabled' },
    ];

    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';

    users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${user.phone}</td>
            <td><span class="status-${user.status}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-secondary" onclick="editUser(${user.id})">Edit</button>
                    <button class="btn btn-danger" onclick="toggleUserStatus(${user.id}, '${user.status}')">
                        ${user.status === 'active' ? 'Disable' : 'Enable'}
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function editUser(userId) {
    alert('Edit user ' + userId);
}

function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'disabled' : 'active';
    alert(`User ${userId} status changed to ${newStatus}`);
    loadUsersTable();
}

function handleUserSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTableBody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
