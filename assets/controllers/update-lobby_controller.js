import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["player1Status", "player2Username", "player2Status", "lobbyButton"];
    static values = {
        mercureUrl: String,
        isUserLoggedIn: Boolean,
    }
    connect() {
        this.subscribeToMercure();
    }

    subscribeToMercure() {
        this.eventSource = new EventSource(this.mercureUrlValue);

        this.eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data)

            switch(data.status) {
                case 'placing_ships':
                    console.log("status: placing_ships");
                    this.updateLobbyPageAfterJoin(data);
                    break;
                case 'in_progress':
                    console.log("status: in_progress");
                    this.updateLobbyPageAfterPlacingShips(data);
                    break;
            }
        }
    }

    updateLobbyPageAfterJoin({ player2Username, shipPlacementUrl }) {

        const button = `
          <a class="btn btn-outline-primary my-3" href="${shipPlacementUrl}">
              Place Your Ships
          </a>
        `;

        this.player2UsernameTarget.innerHTML = player2Username;
        this.player1StatusTarget.innerHTML = 'The player is ready to place ships';
        this.player2StatusTarget.innerHTML = 'The player is ready to place ships';
        this.lobbyButtonTarget.innerHTML = button;
    }

    updateLobbyPageAfterPlacingShips({gameStartUrl}) {

        const button2 = `
          <a class="btn btn-outline-primary my-3" href="${gameStartUrl}">
              Start game
         </a>
        `
        this.lobbyButtonTarget.innerHTML = button;
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close()
        }
    }
}
