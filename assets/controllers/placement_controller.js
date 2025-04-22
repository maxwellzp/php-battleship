import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['orientationHorizontal', 'orientationVertical'];
    static values = {
        orientation: {type: String, default: 'horizontal'}
    };

    connect() {
        console.log('Connect!');
        this.ships = [
            { name: "Carrier", size: 5 },
            { name: "Battleship", size: 4 },
            { name: "Cruiser", size: 3 },
            { name: "Submarine", size: 3 },
            { name: "Destroyer", size: 2 },
        ];

        this.updateRadioButtons();
        console.log('Initial orientation:', this.orientationValue);

        this.boardCells = this.element.querySelectorAll("[data-board-x-value]");
        this.boardCells.forEach((cell) => {
            console.log(cell);
        });


    }

    setOrientation(event) {
        console.log("Call setOrientation");
        this.orientationValue = event.target.value;
        console.log('Orientation changed to:', this.orientationValue);
    }

    resetBoard() {
        console.log("Call resetBoard");
        this.boardCells.forEach((cell) => {
            cell.innerHTML = "";
        })
    }

    updateRadioButtons() {
        if (this.hasOrientationHorizontalTarget) {
            this.orientationHorizontalTarget.checked = (this.orientationValue === 'horizontal');
        }
        if (this.hasOrientationVerticalTarget) {
            this.orientationVerticalTarget.checked = (this.orientationValue === 'vertical');
        }
    }

    placeShip(event) {
        const x = parseInt(event.currentTarget.dataset.boardXValue);
        const y = parseInt(event.currentTarget.dataset.boardYValue);
        console.log(x, y);
    }

    saveShips(){
        console.log("Call saveShips");
    }

    autoPlacement() {
        console.log("Call autoPlacement");
    }

}
