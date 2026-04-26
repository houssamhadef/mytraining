import { getModules } from "./modules.js";
import { validateLogin, validateRegister, showErrors } from './validation.js';
document.addEventListener('DOMContentLoaded', async() => {
    isAuthenticated().then(authenticated => {
        if (authenticated && (window.location.pathname.endsWith('login.html') || window.location.pathname.endsWith('register.html'))){
            window.location.href = 'main.html';
        }
    });

    const registerModulesList = document.getElementById('registerModules');
    if (registerModulesList) {
        const modules = await getModules();
        if (modules) {
            modules.forEach(module => {
                const option = document.createElement('option');
                option.value = module.id;
                option.textContent = module.nom_module;
                registerModulesList.appendChild(option);
            });
        }
    }
});

document.getElementById('loginForm')?.addEventListener('submit', (event) => {
    handleAuth(event, 'login');
});

document.getElementById('registerForm')?.addEventListener('submit', (event) => {
    handleAuth(event, 'register');
});


export async function isAuthenticated() {
    const res = await fetch('/api/auth.php?action=check', {
        method: 'GET',
        credentials: 'include'
    })
    const result = await res.json();
    return result.authenticated;
}

async function handleAuth(event, action) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    if (action === 'login') {
        const errors = validateLogin(data);
        if (errors.length > 0) { showErrors(errors, 'feedback'); return; }
    }

    if (action === 'register') {
        const selectedModules = Array.from(document.getElementById('registerModules').selectedOptions)
            .map(o => o.value)
            .filter(v => v !== '');
        const errors = validateRegister(data, selectedModules);
        if (errors.length > 0) { showErrors(errors, 'feedback'); return; }
        data.modules = selectedModules;
    }

    const res = await fetch(`/api/auth.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
        window.location.href = 'main.html';
    } else {
        showErrors([result.message || 'Something went wrong'], 'feedback');
    }
}


export async function logout() {
    const res = await fetch('/api/auth.php?action=logout', {
        method: 'POST',
        credentials: 'include'
    });
    const result = await res.json();
    if(result.success){
        window.location.href = 'login.html';
    } else {
        const feedbackId = document.getElementById('mainFeedback') ? 'mainFeedback' : 'feedback';
        showErrors([result.message || 'Logout failed'], feedbackId);
    }
}

export async function getUserInfo() {
    const res = await fetch('/api/auth.php?action=profile', {
        method: 'GET',
        credentials: 'include'
    });
    const result = await res.json();
    
    if (result.success) {
        return result.data.user;
    } else {
        console.error('getUserInfo failed:', result.message);
        return null;
    }
}
