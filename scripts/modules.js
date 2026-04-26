const MAX_MODULE_SELECTION = 2;

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const modules = await getModules();
        if (modules) {
            modules.map(module => {
                const li = document.createElement('li');
                li.textContent = module.nom_module;
                return li;
            }).forEach(li => document.getElementById('modulesList')?.appendChild(li));
        }
    }catch (err) {
        console.error('Error loading modules:', err);
    }
});



export async function getModules() {
    const res = await fetch('/api/modules.php?action=list', {
        method: 'GET',
        credentials: 'include'
    });
    const result = await res.json();
    
    if (result.success) {
        return result.data.modules;
    } else {
        console.error('getModules failed:', result.message);
        return null;
    }
}