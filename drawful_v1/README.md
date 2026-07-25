# Drawful v1

A [Drawful](https://jackboxgames.com/games/drawful)-style party game. One screen is the
**host/presenter** (laptop or TV); up to **8 players** join from their **phones**, draw a
secret prompt, then try to fool each other with fake answers.

## How a round works

1. **Draw** — every player gets a different weird prompt and draws it on their phone.
2. **Bluff** — each drawing is shown one at a time. Everyone *except the artist* invents a
   fake answer (a lie) for what it is.
3. **Vote** — the real prompt is mixed in with all the lies. Everyone guesses which one is
   the truth (you can't vote for your own lie).
4. **Score**
   - Guess the truth → **+1000**
   - Someone falls for *your* lie → **+500** each
   - The artist earns **+500** for every correct guess (+**1000** bonus if *everyone* gets it)

Play 1–3 rounds, then a winner is crowned.

## Run it

```bash
cd drawful_v1
npm install      # installs the one dependency (ws)
npm start        # or: node server.js
```

Then:

- **Big screen:** open <http://localhost:3000> and click **“Present on a big screen.”**
  A 4-letter game code appears.
- **Phones:** on the same network, open the host machine's address
  (e.g. `http://192.168.1.20:3000`), tap **“Join a game,”** enter the code and a nickname.

> Phones must be able to reach the host machine. On a LAN, use the host's local IP instead
> of `localhost`. To play over the internet, run it behind a tunnel/host that supports
> WebSockets (e.g. `ngrok http 3000`) and share that URL.

Set a different port with `PORT=8080 npm start`.

## Deploy on a Windows server (same Wi-Fi / LAN)

For a living-room or office game where everyone is on the same network, no domain or
HTTPS is needed — just run it on the Windows machine and have phones open its IP.

1. **Install Node.js** (once) — download the **LTS** build from <https://nodejs.org> and
   run the installer with the defaults.
2. **Copy the `drawful_v1` folder** onto the server (clone the repo, or copy the folder).
3. **Open the firewall** (once) — right-click **`allow-firewall-windows.bat`** and choose
   **“Run as administrator.”** This lets phones on your Wi-Fi reach the game.
4. **Start the game** — double-click **`start-windows.bat`**. The first run installs the one
   dependency automatically, then a window opens showing something like:

   ```
   On PHONES connected to the same Wi-Fi, open:
     http://192.168.1.20:3000
   ```

5. **Big screen:** on the server (or any PC on the network) open that address and click
   **“Present on a big screen”** to get the game code.
6. **Phones:** everyone opens the same `http://192.168.1.20:3000` address, taps
   **“Join a game,”** and enters the code. Up to 8 players.

Keep the `start-windows.bat` window open while you play; close it to stop the game.

**Tips**
- The IP shown is your server's LAN address — phones must be on the **same Wi-Fi/router**.
- To change the port, edit `set PORT=3000` in **both** `start-windows.bat` and
  `allow-firewall-windows.bat`.
- If phones can't connect: confirm they're on the same network, re-run the firewall `.bat`
  as administrator, and make sure the server window is still open.
- Want it to auto-start on boot / run as a Windows service, or let people play over the
  internet? That's a different setup (a service manager like NSSM/pm2, plus port-forwarding
  and HTTPS) — ask and I'll add it.

## Tech

- **Zero build step.** Node's built-in `http` server serves the static `public/` client;
  real-time play runs over a single WebSocket (`ws`).
- `server.js` — HTTP + WebSocket wiring, room routing, reconnection, keep-alive.
- `game.js` — the `Room` state machine (phases: lobby → drawing → answering → voting →
  reveal → scores → gameover), scoring, and per-role state serialization.
- `prompts.js` — the prompt bank.
- `public/` — `index.html` (landing), `host.html` (big screen), `play.html` (phone client
  with the drawing canvas), `style.css`.

## Notes

- 2 players minimum (3+ is more fun), 8 max.
- Phones remember their seat — a dropped player can reload and rejoin the same game.
- Rounds auto-advance on a timer, but the host can also push things along with the on-screen
  buttons. Timers: 100s to draw, 55s to bluff, 35s to vote.
