import { logout } from './auth.js';

document.addEventListener('DOMContentLoaded', async () => {
    const res = await fetch('/api/admin.php?action=check', { credentials: 'include' });
    const result = await res.json();
    if (!result.success) {
        window.location.href = 'admin-login.html';
        return;
    }

    document.getElementById('logoutBtn').addEventListener('click', async () => {
        await fetch('/api/admin.php?action=logout', { method: 'POST', credentials: 'include' });
        window.location.href = 'admin-login.html';
    });

    loadStats();
    loadUsers();
    loadInscriptions();

    document.getElementById('searchInput').addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('#usersBody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
});

async function loadStats() {
    const res = await fetch('/api/admin.php?action=stats', { credentials: 'include' });
    const result = await res.json();
    if (result.success) {
        document.getElementById('totalUsers').textContent = result.data.users;
        document.getElementById('totalInscriptions').textContent = result.data.inscriptions;
        document.getElementById('totalModules').textContent = result.data.modules;
    }
}

async function loadUsers() {
    const res = await fetch('/api/admin.php?action=users', { credentials: 'include' });
    const result = await res.json();
    if (!result.success) return;

    const tbody = document.getElementById('usersBody');
    tbody.innerHTML = '';

    result.data.forEach(user => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${user.nom}</td>
            <td>${user.prenom}</td>
            <td>${user.cin}</td>
            <td>${user.email}</td>
            <td>${user.niveu}</td>
            <td><button class="danger-btn" data-id="${user.id}">Delete</button></td>
        `;
        tr.querySelector('button').addEventListener('click', () => deleteUser(user.id));
        tbody.appendChild(tr);
    });
}

async function loadInscriptions() {
    const res = await fetch('/api/admin.php?action=inscriptions', { credentials: 'include' });
    const result = await res.json();
    if (!result.success) return;

    const tbody = document.getElementById('inscriptionsBody');
    tbody.innerHTML = '';

    result.data.forEach(ins => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${ins.nom} ${ins.prenom}</td>
            <td>${ins.nom_module}</td>
            <td><button class="danger-btn">Delete</button></td>
        `;
        tr.querySelector('button').addEventListener('click', () => deleteInscription(ins.id));
        tbody.appendChild(tr);
    });
}

async function deleteUser(id) {
    if (!confirm('Delete this user and all their inscriptions?')) return;
    const res = await fetch('/api/admin.php?action=deleteUser', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    const result = await res.json();
    alert(result.message);
    if (result.success) { loadUsers(); loadInscriptions(); loadStats(); }
}

async function deleteInscription(id) {
    if (!confirm('Delete this inscription?')) return;
    const res = await fetch('/api/admin.php?action=deleteInscription', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    const result = await res.json();
    alert(result.message);
    if (result.success) { loadInscriptions(); loadStats(); }
}