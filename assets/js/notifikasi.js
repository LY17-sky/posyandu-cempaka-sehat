function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

function confirmWhatsapp(url) {
    if (confirm('Kirim notifikasi WhatsApp sekarang?')) {
        window.location.href = url;
    }
}

window.showToast = showToast;
window.confirmWhatsapp = confirmWhatsapp;
