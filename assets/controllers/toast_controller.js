import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    connect() {
        this.boundShowToast = this.showToast.bind(this);
        window.addEventListener('toast', this.boundShowToast);
    }

    disconnect() {
        window.removeEventListener('toast', this.boundShowToast);
    }

    showToast(event) {
        const { type, message, includeRedirect = false } = event.detail;

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type} border-0 show`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
        <div class="d-flex flex-column">
            <div class="toast-body">${message}</div>
            ${includeRedirect ? `<div class="mt-2 px-2">
                <button type="button" class="btn btn-light btn-sm" id="redirect-btn">🏁 Return to Lobby</button>
            </div>` : ''}
            <button type="button" class="btn-close btn-close-white me-2 m-auto position-absolute top-0 end-0" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

        document.body.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });

        if (includeRedirect) {
            toast.querySelector('#redirect-btn')?.addEventListener('click', () => {
                window.location.href = this.lobbyUrlValue ?? '/';
            });
        }
    }


}
