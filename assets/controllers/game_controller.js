import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static values = {
        gameId: String
    }

    fire(event) {
        const cell = event.currentTarget
        const x = parseInt(cell.dataset.gameXValue);
        const y = parseInt(cell.dataset.gameYValue);

        fetch(`/api/game/${this.gameIdValue}/fire`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ x: x, y: y })
        })
            .then(res => res.json())
            .then(data => {
                if (data.hit) {
                    cell.classList.remove('bg-light')
                    cell.classList.add('bg-danger')
                } else {
                    cell.classList.remove('bg-light')
                    cell.classList.add('bg-secondary')
                }
                cell.removeEventListener('click', this.fire)
            })
    }
}
