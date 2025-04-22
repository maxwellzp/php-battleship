export class Ship {
    constructor(name, size, count = 1) {
        this.name = name;
        this.size = size;
        this.count = count;
        this.placed = 0;
    }

    getCoordinates(x, y, orientation) {
        const coords = [];
        for (let i = 0; i < this.size; i++) {
            const dx = orientation === "horizontal" ? x + i : x;
            const dy = orientation === "vertical" ? y + i : y;

            if (dx > 9 || dy > 9) return null;
            coords.push({ x: dx, y: dy });
        }
        return coords;
    }

    canPlace() {
        return this.placed < this.count;
    }

    place() {
        this.placed++;
    }

    reset() {
        this.placed = 0;
    }
}