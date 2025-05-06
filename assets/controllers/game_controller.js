import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static values = {
        gameId: String,
        lobbyUrl: String
    }
    connect() {

    }

    fire(event) {
        const cell = event.currentTarget;
        const x = parseInt(cell.dataset.gameXValue);
        const y = parseInt(cell.dataset.gameYValue);

        fetch(`/api/game/${this.gameIdValue}/fire`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ x, y })
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    this.dispatchToast('danger', `❌ ${data.message}`);
                    return;
                }

                const { result } = data.data;
                if (result !== null) {
                    this.handleShot(cell, result);
                    this.displayShot(result);
                }

                const {sunkCoordinates} = data.data;
                if (Array.isArray(sunkCoordinates)) {
                    this.handleSunkShip(sunkCoordinates);
                }

                const {winner} = data.data;
                if (winner) {
                    this.displayWinner(winner);
                }
            })
            .catch(error => {
                this.dispatchToast('danger', `❌ Error: ${error.message}`);
            });
    }

    handleShot(cell, result) {
        cell.classList.remove(
            'bg-light', 'bg-dark', 'bg-danger', 'bg-secondary',
            'bg-white', 'text-warning', 'text-white', 'hover:bg-light'
        );

        if (result === 'sunk') {
            cell.classList.add('bg-dark', 'text-warning');
        } else if (result === 'hit') {
            cell.innerHTML = 'X';
            cell.classList.add('bg-danger', 'text-white');
        } else if (result === 'miss') {
            cell.innerHTML = 'O';
            cell.classList.add('bg-secondary', 'text-white');
        } else {
            cell.innerHTML = ' ';
            cell.classList.add('bg-white', 'hover:bg-light');
        }

        cell.removeAttribute('data-action');
    }

    dispatchToast(type, message) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { type, message }
        }));
    }

    displayWinner(winner) {
        this.dispatchToast('success', `🏆 ${winner} won the game! Click to return to lobby.`);

        // Disable all opponent board cells (no more firing)
        const opponentCells = this.element.querySelectorAll('[data-board="opponent"]');
        opponentCells.forEach(cell => {
            cell.removeAttribute('data-action');
            cell.classList.add('disabled'); // Optional: style inactive cells
        });

        // Optionally, add a class to the whole board to fade it out or block interactions
        const opponentBoard = this.element.closest('.row').querySelector('[data-controller="game"]');
        if (opponentBoard) {
            opponentBoard.classList.add('no-click'); // Use pointer-events: none; in CSS
        }

        const returnButton = document.createElement('button');
        returnButton.classList.add('btn', 'btn-success', 'mt-2');
        returnButton.innerText = 'Back to Lobby';
        returnButton.addEventListener('click', () => {
            window.location.href = this.lobbyUrlValue;
        });

        document.body.appendChild(returnButton);
    }


    displayShot(result){
        this.dispatchToast('info', `💥 Shot result: ${result}`);
    }
    handleSunkShip(sunkCoordinates) {
        for (const coordinate of sunkCoordinates) {
            const cell = this.element.querySelector(
                `[data-board="opponent"][data-game-x-value="${coordinate.x}"][data-game-y-value="${coordinate.y}"]`
            );

            if (!cell) continue;

            cell.classList.remove('bg-white', 'bg-danger', 'bg-secondary', 'text-white', 'hover:bg-light');
            cell.innerHTML = 'X';
            cell.classList.add('bg-dark', 'text-warning');

            // Prevent user from interacting again
            cell.removeAttribute('data-action');
        }
    }

}
