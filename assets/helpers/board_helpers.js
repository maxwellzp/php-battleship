export function findCell(container, x, y) {
    return container.querySelector(`[data-board-x-value="${x}"][data-board-y-value="${y}"]`);
}

export function markCells(container, coords, className) {
    coords.forEach(({ x, y }) => {
        const cell = findCell(container, x, y);
        if (cell) cell.classList.add(className);
    });
}

export function unmarkCells(container, coords, className) {
    coords.forEach(({ x, y }) => {
        const cell = findCell(container, x, y);
        if (cell) cell.classList.remove(className);
    });
}
