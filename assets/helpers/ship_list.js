import { Ship } from "./ship";
export class ShipList {
    constructor(shipDefs) {
        this.ships = shipDefs.map(s => new Ship(s.name, s.size, s.count));
        this.placements = [];
    }

    getNext() {
        return this.ships.find(s => s.canPlace());
    }

    peek() {
        return this.getNext();
    }

    savePlacement(name, orientation, coords) {
        const ship = this.ships.find(s => s.name === name && s.canPlace());
        if (ship) {
            ship.place();
            this.placements.push({ name, orientation, coords });
        }
    }

    reset() {
        this.ships.forEach(s => s.reset());
        this.placements = [];
    }

    allPlaced() {
        return this.ships.every(s => s.placed === s.count);
    }

    getPlacements() {
        return this.placements;
    }
}
