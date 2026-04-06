/**
 * Tivora Custom Alerts & Modals
 * Premium, glassmorphism design for the entire website
 */

const TivoraAlert = {
    init() {
        if (document.getElementById('tivora-alert-style')) return;

        const style = document.createElement('style');
        style.id = 'tivora-alert-style';
        style.innerHTML = `
            @keyframes tivora-fade-in {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes tivora-scale-in {
                from { opacity: 0; transform: scale(0.9) translateY(20px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
            .tivora-alert-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(15, 23, 42, 0.4);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 999999;
                opacity: 0;
                transition: opacity 0.3s ease;
                padding: 20px;
                font-family: 'Inter', sans-serif;
            }
            .tivora-alert-overlay.show {
                opacity: 1;
            }
            .tivora-alert-box {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 20px;
                width: 100%;
                max-width: 420px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                border: 1px solid rgba(255, 255, 255, 0.2);
                animation: tivora-scale-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
                overflow: hidden;
            }
            .tivora-alert-content {
                padding: 32px 32px 24px;
                text-align: center;
            }
            .tivora-alert-icon {
                width: 64px;
                height: 64px;
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                transition: transform 0.3s ease;
            }
            .tivora-alert-icon:hover {
                transform: rotate(10deg) scale(1.1);
            }
            .tivora-alert-icon.success {
                background: #f0fdf4;
                color: #22c55e;
            }
            .tivora-alert-icon.error {
                background: #fef2f2;
                color: #14b8a6;
            }
            .tivora-alert-icon.info {
                background: #eff6ff;
                color: #3b82f6;
            }
            .tivora-alert-icon.warning {
                background: #fffbeb;
                color: #f59e0b;
            }
            .tivora-alert-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 8px;
            }
            .tivora-alert-message {
                font-size: 0.9375rem;
                line-height: 1.6;
                color: #64748b;
            }
            .tivora-alert-footer {
                padding: 0 32px 32px;
                display: flex;
                gap: 12px;
                justify-content: center;
            }
            .tivora-btn {
                padding: 12px 24px;
                border-radius: 12px;
                font-size: 0.875rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                border: none;
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            .tivora-btn-primary {
                background: #14b8a6;
                color: white;
                box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
            }
            .tivora-btn-primary:hover {
                background: #0d9488;
                box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3);
                transform: translateY(-1px);
            }
            .tivora-btn-secondary {
                background: #f1f5f9;
                color: #475569;
            }
            .tivora-btn-secondary:hover {
                background: #e2e8f0;
                color: #1e293b;
                transform: translateY(-1px);
            }
        `;
        document.head.appendChild(style);
    },

    show({ title, message, type = 'info', confirmText = 'OK', cancelText = null }) {
        this.init();

        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'tivora-alert-overlay';

            const icons = {
                success: '<i data-lucide="check-circle-2"></i>',
                error: '<i data-lucide="x-circle"></i>',
                info: '<i data-lucide="info"></i>',
                warning: '<i data-lucide="alert-triangle"></i>'
            };

            overlay.innerHTML = `
                <div class="tivora-alert-box">
                    <div class="tivora-alert-content">
                        <div class="tivora-alert-icon ${type}">
                            ${icons[type] || icons.info}
                        </div>
                        <div class="tivora-alert-title">${title}</div>
                        <div class="tivora-alert-message">${message}</div>
                    </div>
                    <div class="tivora-alert-footer">
                        ${cancelText ? `
                            <button class="tivora-btn tivora-btn-secondary" id="tivora-cancel">
                                ${cancelText}
                            </button>
                        ` : ''}
                        <button class="tivora-btn tivora-btn-primary" id="tivora-confirm">
                            ${confirmText}
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);

            if (window.lucide) {
                lucide.createIcons({
                    parent: overlay
                });
            }

            // Trigger animation
            requestAnimationFrame(() => {
                overlay.classList.add('show');
            });

            const confirmBtn = overlay.querySelector('#tivora-confirm');
            const cancelBtn = overlay.querySelector('#tivora-cancel');

            const close = (result) => {
                overlay.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(overlay);
                    resolve(result);
                }, 300);
            };

            confirmBtn.onclick = () => close(true);
            if (cancelBtn) {
                cancelBtn.onclick = () => close(false);
            }

            // Close on escape key
            const handleEsc = (e) => {
                if (e.key === 'Escape') {
                    close(false);
                    document.removeEventListener('keydown', handleEsc);
                }
            };
            document.addEventListener('keydown', handleEsc);
        });
    },

    alert(message, type = 'info') {
        const title = type === 'error' ? 'Oops!' : (type === 'success' ? 'Success!' : 'Notification');
        return this.show({ title, message, type });
    },

    confirm(message, title = 'Are you sure?') {
        return this.show({
            title,
            message,
            type: 'warning',
            confirmText: 'Yes, Proceed',
            cancelText: 'Cancel'
        });
    }
};

// Also expose to window
window.Tivora = TivoraAlert;
