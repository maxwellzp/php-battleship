import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['orientationHorizontal', 'orientationVertical'];
    static values = {
        orientation: {type: String, default: 'horizontal'}
    };

    connect() {
        console.log('Connect!');
        this.updateRadioButtons();
        console.log('Initial orientation:', this.orientationValue);
    }

    setOrientation(event) {
        console.log("Call setOrientation");
        this.orientationValue = event.target.value;
        console.log('Orientation changed to:', this.orientationValue);
    }

    resetBoard() {
        console.log("Call resetBoard");
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

}
