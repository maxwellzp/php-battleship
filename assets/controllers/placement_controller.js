import { Controller } from "@hotwired/stimulus";
import { ShipList } from "../helpers/ship_list";
import { findCell, markCells, unmarkCells } from "../helpers/board_helpers";

export default class extends Controller {
    static targets = ['orientationHorizontal', 'orientationVertical'];
    static values = {
        orientation: String,
        url: String,
        gameId: String,
    };

    connect() {
        console.log('Connect!');
        this.ships = new ShipList([
            { name: "Carrier", size: 5, count: 1 },
            { name: "Battleship", size: 4, count: 1 },
            { name: "Cruiser", size: 3, count: 1 },
            { name: "Submarine", size: 3, count: 1 },
            { name: "Destroyer", size: 2, count: 2 },
        ]);

        this.updateRadioButtons();
        console.log('Orientation:', this.orientationValue);
        console.log('Api Url:', this.urlValue);

        this.boardCells = this.element.querySelectorAll("[data-board-x-value]");
        this.boardCells.forEach(cell => {
            cell.addEventListener("mouseenter", this.previewShip.bind(this));
            cell.addEventListener("mouseleave", this.clearPreview.bind(this));
        });
    }

    setOrientation(event) {
        console.log("Call setOrientation");
        this.orientationValue = event.target.value;
        console.log('Orientation changed to:', this.orientationValue);
    }


    updateRadioButtons() {
        if (this.hasOrientationHorizontalTarget) {
            this.orientationHorizontalTarget.checked = (this.orientationValue === 'horizontal');
        }
        if (this.hasOrientationVerticalTarget) {
            this.orientationVerticalTarget.checked = (this.orientationValue === 'vertical');
        }
    }


    autoPlacement() {
        console.log("Call autoPlacement");
    }

    placeShip(event) {
        const x = parseInt(event.currentTarget.dataset.boardXValue);
        const y = parseInt(event.currentTarget.dataset.boardYValue);

        const ship = this.ships.getNext();
        if (!ship) return;

        const coords = ship.getCoordinates(x, y, this.orientationValue);
        if (!coords || !this.isPlaceable(coords)) return;

        markCells(this.element, coords, "bg-primary");
        this.ships.savePlacement(ship.name, this.orientationValue, coords);
    }

    previewShip(event) {
        const ship = this.ships.peek();
        if (!ship) return;

        const x = parseInt(event.currentTarget.dataset.boardXValue);
        const y = parseInt(event.currentTarget.dataset.boardYValue);

        const coords = ship.getCoordinates(x, y, this.orientationValue);
        if (!coords || !this.isPlaceable(coords)) return;

        markCells(this.element, coords, "bg-info");
    }

    clearPreview(event) {
        const x = parseInt(event.currentTarget.dataset.boardXValue);
        const y = parseInt(event.currentTarget.dataset.boardYValue);

        const ship = this.ships.peek();
        if (!ship) return;

        const coords = ship.getCoordinates(x, y, this.orientationValue);
        if (!coords) return;

        unmarkCells(this.element, coords, "bg-info");
    }

    isPlaceable(coords) {
        return coords.every(({ x, y }) => {
            const cell = findCell(this.element, x, y);
            return cell && !cell.classList.contains("bg-primary");
        });
    }

    resetBoard() {
        this.ships.reset();
        this.element.querySelectorAll('[data-board-x-value]').forEach(cell => {
            cell.classList.remove("bg-primary", "bg-info");
        });
    }

    saveShips() {
        if (!this.ships.allPlaced()) {
            alert("Place all ships first!");
            return;
        }

        fetch(this.urlValue, {
            method: "POST",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(this.ships.getPlacements()),
        }).then(res => res.ok ? location.href = "/game/" + this.gameIdValue + "/lobby" : alert("Error saving ships"));
    }

}
