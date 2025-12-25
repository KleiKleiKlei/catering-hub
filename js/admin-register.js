// Admin Registration Handler

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerAdminForm');
    if (form) {
        form.addEventListener('submit', handleAdminRegister);
    }
});

function handleAdminRegister(e) {
    e.preventDefault();
    
    const name = document.getElementById('adminName').value;
    const email = document.getElementById('adminEmail').value;
    const password = document.getElementById('adminPassword').value;
    const confirmPassword = document.getElementById('adminConfirmPassword').value;

    // Validation
    if (!name || !email || !password || !confirmPassword) {
        alert('Please fill in all fields');
        return;
    }

    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }

    // Send to backend
    const data = {
        name: name,
        email: email,
        password: password
    };

    fetch('api-admin.php?action=register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Admin registered successfully! Please login.');
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
