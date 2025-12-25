// Authentication & Session Management

// Session Storage for user data
class UserSession {
    static setUser(user) {
        sessionStorage.setItem('user', JSON.stringify(user));
    }

    static getUser() {
        const user = sessionStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    }

    static clearUser() {
        sessionStorage.removeItem('user');
    }

    static isLoggedIn() {
        return this.getUser() !== null;
    }
}

// Protection wrapper - use this in protected pages
function protectPage(requiredUserType) {
    const user = UserSession.getUser();
    
    // If no user logged in, redirect to login
    if (!user) {
        window.location.href = 'login.html';
        return null;
    }
    
    // If user type doesn't match, redirect to login
    if (user.userType !== requiredUserType) {
        UserSession.clearUser();
        window.location.href = 'login.html';
        return null;
    }
    
    return user;
}

// Check if page is login/register/home (public pages)
function isPublicPage() {
    const pathname = window.location.pathname;
    const filename = pathname.substring(pathname.lastIndexOf('/') + 1);
    
    const publicPages = ['index.html', 'login.html', 'register.html', ''];
    return publicPages.includes(filename) || filename === 'index.html' || filename === '';
}

// Auto-check protection on page load
document.addEventListener('DOMContentLoaded', function() {
    // Don't protect public pages
    if (isPublicPage()) {
        return;
    }
    
    const pathname = window.location.pathname;
    
    // Check which protected page we're on and enforce access
    if (pathname.includes('admin')) {
        protectPage('admin');
    } else if (pathname.includes('user')) {
        protectPage('user');
    }
});
