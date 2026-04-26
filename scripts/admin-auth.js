import { validateLogin, validateRegister, showErrors } from './validation.js';

document.getElementById('adminLoginForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };

    const errors = validateLogin(data);
    if (errors.length > 0) { showErrors(errors, 'feedback'); return; }

    const res = await fetch('/api/admin.php?action=login', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
        window.location.href = 'admin.html';
    } else {
        showErrors([result.message], 'feedback');
    }
});

document.getElementById('adminRegisterForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
        nom: document.getElementById('nom').value,
        prenom: document.getElementById('prenom').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };

    const errors = [];
    if (!data.nom) errors.push('Nom is required');
    if (!data.prenom) errors.push('Prenom is required');
    if (!data.email) errors.push('Email is required');
    if (!data.password || data.password.length < 8) errors.push('Password must be at least 8 characters');

    if (errors.length > 0) { showErrors(errors, 'feedback'); return; }

    const res = await fetch('/api/admin.php?action=register', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
        window.location.href = 'admin-login.html';
    } else {
        showErrors([result.message], 'feedback');
    }
});