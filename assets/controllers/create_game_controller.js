import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["tbody"];
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
            const {gameId, player1, status, createdAt, joinPath} = JSON.parse(event.data)

            this.addGameToTable(gameId, player1, status, createdAt, joinPath);
        }
    }

    addGameToTable(gameId, player1, status, createdAt, join_path) {
        console.log("-------addGameToTable: -------");
        console.log("---gameId: ", gameId);
        console.log("---player1: ", player1);
        console.log("---status: ", status);
        console.log("---createdAt: ", createdAt);
        console.log("---join_path: ", join_path);
        console.log("-------------------------------");

        const row = document.createElement("tr")

        const form = `
        <form action="${join_path}" method="POST">
            <input class="btn btn-outline-primary" type="submit" value="Join Game">
        </form>
        `;

        row.innerHTML = `
    <td><strong>${gameId}</strong></td>
    <td>${player1}</td>
    <td>${status}</td>
    <td>${createdAt}</td>
    <td>
        ${this.isUserLoggedInValue ? form : ""}
    </td>
  `
        this.tbodyTarget.prepend(row)
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close()
        }
    }
}