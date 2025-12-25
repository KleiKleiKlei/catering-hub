// Main JavaScript File

// Login Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
});

function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const userType = document.getElementById('userType').value;

    // Basic validation
    if (!username || !password) {
        alert('Please fill in all fields');
        return;
    }

    const data = {
        email: username,
        password: password
    };

    // Call appropriate API based on user type
    const apiUrl = userType === 'admin' 
        ? 'api-admin.php?action=login'
        : 'api-users.php?action=login';

    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            // Save user to session
            const user = {
                id: result.user ? result.user.id : result.admin.id,
                name: result.user ? result.user.name : result.admin.name,
                email: result.user ? result.user.email : result.admin.email,
                userType: userType,
                loginTime: new Date().toISOString()
            };

            UserSession.setUser(user);

            // Redirect based on user type
            if (userType === 'admin') {
                window.location.href = 'admin-dashboard.html';
            } else {
                window.location.href = 'user-calendar.html';
            }
        } else {
            alert('Login failed: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Login error. Please try again.');
    });
}

function handleRegister(e) {
    e.preventDefault();
    
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('regPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validation
    if (!name || !email || !phone || !password || !confirmPassword) {
        alert('Please fill in all fields');
        return;
    }

    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }

    const data = {
        name: name,
        email: email,
        phone: phone,
        password: password
    };

    fetch('api-users.php?action=register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Registration successful! Please login.');
            window.location.href = 'login.html';
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Registration failed. Please try again.');
    });
}

// Utility function to format dates
function formatDate(date) {
    const options = { weekday: 'short', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Utility function to get the current week
function getCurrentWeek() {
    const week = [];
    const today = new Date();
    const first = today.getDate() - today.getDay();

    for (let i = 0; i < 7; i++) {
        const date = new Date(today.setDate(first + i));
        week.push(new Date(date));
    }

    return week;
}

// Logout function
function logoutUser(e) {
    e.preventDefault();
    UserSession.clearUser();
    window.location.href = 'index.html';
}

// Simple logout function for navbar
function logout() {
    UserSession.clearUser();
    window.location.href = 'index.html';
}


