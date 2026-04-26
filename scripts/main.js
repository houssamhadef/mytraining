import { logout, isAuthenticated, getUserInfo } from './auth.js';
import { getModules } from './modules.js';
import { validateUpdate, showErrors } from './validation.js';

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        isAuthenticated().then(authenticated => {
            if (!authenticated) window.location.href = 'login.html';
        });
    }
});

document.addEventListener('DOMContentLoaded', async () => {

    const authenticated = await isAuthenticated();
    if (!authenticated) {
        window.location.href = 'login.html';
        return;
    }

    try {
        const userInfo = await getUserInfo();
        
        if (!userInfo) {
            console.error('getUserInfo returned null/undefined');
            return;
        }
        
        const userImg = document.createElement('img');
        userImg.src = `https://ui-avatars.com/api/?name=${userInfo.nom}+${userInfo.prenom}&background=0ea5e9&color=fff`;
        userImg.alt = 'User Avatar';
        userImg.style.width = '40px';
        userImg.style.height = '40px';
        userImg.style.borderRadius = '50%';

        const link = document.createElement('a');
        link.appendChild(userImg);
        document.getElementById('navbar_items')?.appendChild(link);

       document.getElementById('nom') && (document.getElementById('nom').value = userInfo.nom);
       document.getElementById('prenom') && (document.getElementById('prenom').value = userInfo.prenom);
       document.getElementById('email') && (document.getElementById('email').value = userInfo.email);
       document.getElementById('cin') && (document.getElementById('cin').value = userInfo.cin);

       const niveauRadio = document.querySelector(`input[name="niveau"][value="${userInfo.niveu}"]`);
      if (niveauRadio) niveauRadio.checked = true;


   const modules = await getModules();
if (modules) {
    const select = document.getElementById('modules');
    const userModuleIds = userInfo.module_ids
        ? userInfo.module_ids.split(',').map(id => id.trim())
        : [];

    modules.forEach(module => {
        const option = document.createElement('option');
        option.value = module.id;
        option.textContent = module.nom_module;
        option.selected = userModuleIds.includes(String(module.id));
        select.appendChild(option);
    });

    select.addEventListener('change', () => {
        const selected = [...select.selectedOptions];
        if (selected.length > 2) {
            alert('Maximum 2 modules');
            selected[selected.length - 1].selected = false;
        }
    });
}
       
        document.getElementById('saveBtn')?.addEventListener('click', async () => {
    const selectedIds = [...document.getElementById('modules').selectedOptions].map(o => o.value);
    const data = {
        nom: document.getElementById('nom').value,
        prenom: document.getElementById('prenom').value,
        niveau: document.querySelector('input[name="niveau"]:checked')?.value
    }

    const errors = validateUpdate(data, selectedIds);
    if (errors.length > 0) { showErrors(errors, 'mainFeedback'); return; }

    const res = await fetch('/api/auth.php?action=update', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            ...data,
            modules: selectedIds
        })
    });
    const result = await res.json();
    if (result.success) {
        window.location.reload();
    }else {
        alert(result.message);
    }

});

        document.getElementById('logoutBtn')?.addEventListener('click', () => {
            logout();
        });

    } catch (err) {
        console.error('Error loading user:', err);
    }
});