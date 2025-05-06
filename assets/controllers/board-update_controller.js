import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        mercureUrl: String,
        userId: String,
    }

    connect() {
        this.subscribeToMercure();
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }

    subscribeToMercure() {
        this.eventSource = new EventSource(this.mercureUrlValue);

        this.eventSource.onmessage = event => {
            const data = JSON.parse(event.data);

            // Ignore updates sent by the current user
            if (data.by === this.userIdValue) {
                return;
            }

            const { action } = data;

            switch (action) {
                case "update_cell":
                    this.updateCell(data);
                    break;
                case "ship_sunk":
                    this.shipSunk(data.coordinates || data.cells);
                    break;
            }
        };
    }

    updateCell(data) {
        const cell = this.element.querySelector(
            `[data-board="your"][data-game-x-value="${data.x}"][data-game-y-value="${data.y}"]`
        );

        if (!cell) return;

        // Remove any previous classes
        cell.classList.remove('bg-white', 'bg-danger', 'bg-secondary', 'text-white', 'hover:bg-light');

        // Apply new result classes
        if (data.result === 'hit') {
            cell.innerHTML = 'X';
            cell.classList.add('bg-danger', 'text-white');
        } else if (data.result === 'miss') {
            cell.innerHTML = 'O';
            cell.classList.add('bg-secondary', 'text-white');
        }

        // Prevent further interaction
        cell.removeAttribute('data-action');
    }

    shipSunk(coordinates) {
        for (const coordinate of coordinates) {
            const cell = this.element.querySelector(
                `[data-board="your"][data-game-x-value="${coordinate.x}"][data-game-y-value="${coordinate.y}"]`
            );

            if (!cell) continue;

            cell.classList.remove('bg-white', 'bg-danger', 'bg-secondary', 'text-white', 'hover:bg-light');
            cell.innerHTML = 'X';
            cell.classList.add('bg-dark', 'text-warning');

            // Prevent further interaction
            cell.removeAttribute('data-action');
        }
    }
}
