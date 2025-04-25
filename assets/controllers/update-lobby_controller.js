import {Controller} from '@hotwired/stimulus';

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
            console.log(data);

            switch (data.status) {
                case 'placing_ships':
                    console.log("status: placing_ships");
                    this.updateLobbyPageAfterJoin(data);
                    break;
                case 'in_progress':
                    console.log("status: in_progress");
                    this.updateLobbyPageAfterTwoPlayersPlacedShips(data);
                    break;
                case 'one_player_ready':
                    console.log("status: one_player_ready");
                    this.updateLobbyAfterAnyPlayerIsReady(data);
                    break;
            }
        }
    }

    updateLobbyPageAfterJoin({player2Username, shipPlacementUrl}) {
        console.log("-------updateLobbyPageAfterJoin: -------");
        console.log("---player2Username: ", player2Username);
        console.log("---shipPlacementUrl: ", shipPlacementUrl);
        console.log("---------------------------------------");

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

    updateLobbyPageAfterTwoPlayersPlacedShips({player, statusMsg, gameStartUrl}) {
        console.log("-------updateLobbyPageAfterPlacingShips: -------");
        console.log("---player: ", player);
        console.log("---statusMsg: ", statusMsg);
        console.log("---gameStartUrl: ", gameStartUrl);
        console.log("------------------------------------------------");

        const button = `
          <a class="btn btn-outline-primary my-3" href="${gameStartUrl}">
              Start game
         </a>
        `
        switch (player) {
            case 1:
                this.player1StatusTarget.innerHTML = statusMsg;
                break;
            case 2:
                this.player2StatusTarget.innerHTML = statusMsg;
                break;
        }

        this.lobbyButtonTarget.innerHTML = button;
    }

    updateLobbyAfterAnyPlayerIsReady({status, player, statusMsg}) {
        console.log("-------updateLobbyAfterAnyPlayerIsReady: -------");
        console.log("---status: ", status);
        console.log("---player: ", player);
        console.log("---statusMsg: ", statusMsg);
        console.log("------------------------------------------------");

        switch (player) {
            case 1:
                this.player1StatusTarget.innerHTML = statusMsg;
                break;
            case 2:
                this.player2StatusTarget.innerHTML = statusMsg;
                break;
        }
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close()
        }
    }
}
