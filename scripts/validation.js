export function validateLogin(data) {
    const errors = [];

    if (!data.identifier && !data.email) {
        errors.push('Email or CIN is required');
    }

    if (data.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
        errors.push('Invalid email');
    }

    if (!data.password) errors.push('Password is required');

    return errors;
}

export function validateRegister(data, selectedModules) {
    const errors = [];

    if (!data.nom) errors.push('Nom is required');
    else if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(data.nom)) errors.push('Nom must contain letters only');

    if (!data.prenom) errors.push('Prenom is required');
    else if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(data.prenom)) errors.push('Prenom must contain letters only');

    if (!data.cin) errors.push('CIN is required');
    else if (!/^\d{8}$/.test(data.cin)) errors.push('CIN must be exactly 8 digits');

    if (!data.email) errors.push('Email is required');
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) errors.push('Invalid email');

    if (!data.password) errors.push('Password is required');
    else if (data.password.length < 8) errors.push('Password must be at least 8 characters');

    if (!data.niveau) errors.push('Please select a niveau');

    if (!selectedModules || selectedModules.length === 0) errors.push('Please select at least one module');
    else if (selectedModules.length > 2) errors.push('Maximum 2 modules allowed');

    return errors;
}

export function validateUpdate(data, selectedModules) {
    const errors = [];

    if (!data.nom) errors.push('Nom is required');
    else if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(data.nom)) errors.push('Nom must contain letters only');

    if (!data.prenom) errors.push('Prenom is required');
    else if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(data.prenom)) errors.push('Prenom must contain letters only');

    if (!data.niveau) errors.push('Please select a niveau');

    if (!selectedModules || selectedModules.length === 0) errors.push('Please select at least one module');
    else if (selectedModules.length > 2) errors.push('Maximum 2 modules allowed');

    return errors;
}

export function showErrors(errors, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (errors.length === 0) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <ul class="error-list">
            ${errors.map(e => `<li>${e}</li>`).join('')}
        </ul>
    `;
}