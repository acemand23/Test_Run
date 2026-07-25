'use strict';
const http = require('http');
const fs = require('fs');
const os = require('os');
const path = require('path');
const { WebSocketServer } = require('ws');
const { Room, makeCode, MAX_PLAYERS } = require('./game');

const PORT = process.env.PORT || 3000;
const PUBLIC = path.join(__dirname, 'public');
const MIME = { '.html': 'text/html', '.js': 'text/javascript', '.css': 'text/css',
  '.svg': 'image/svg+xml', '.ico': 'image/x-icon', '.json': 'application/json' };

// ---------------- static file server ----------------
const server = http.createServer((req, res) => {
  let urlPath = decodeURIComponent(req.url.split('?')[0]);
  if (urlPath === '/') urlPath = '/index.html';
  const filePath = path.join(PUBLIC, path.normalize(urlPath).replace(/^(\.\.[/\\])+/, ''));
  if (!filePath.startsWith(PUBLIC)) { res.writeHead(403); return res.end('Forbidden'); }
  fs.readFile(filePath, (err, data) => {
    if (err) { res.writeHead(404, { 'Content-Type': 'text/plain' }); return res.end('Not found'); }
    res.writeHead(200, { 'Content-Type': MIME[path.extname(filePath)] || 'application/octet-stream' });
    res.end(data);
  });
});

// ---------------- game rooms ----------------
const rooms = new Map(); // code -> Room

function broadcast(room) {
  // host(s)
  const hostMsg = JSON.stringify({ t: 'state', view: room.hostView() });
  for (const ws of hostSockets) {
    if (ws.roomCode === room.code && ws.readyState === ws.OPEN) safeSend(ws, hostMsg);
  }
  // players
  for (const p of room.players.values()) {
    if (p.ws && p.ws.readyState === p.ws.OPEN) {
      safeSend(p.ws, JSON.stringify({ t: 'state', view: room.playerView(p.pid) }));
    }
  }
}

function safeSend(ws, str) { try { ws.send(str); } catch (_) {} }

const hostSockets = new Set();

const wss = new WebSocketServer({ server });

wss.on('connection', (ws) => {
  ws.isAlive = true;
  ws.on('pong', () => { ws.isAlive = true; });

  ws.on('message', (raw) => {
    let msg;
    try { msg = JSON.parse(raw.toString()); } catch { return; }
    handle(ws, msg);
  });

  ws.on('close', () => {
    hostSockets.delete(ws);
    if (ws.roomCode) {
      const room = rooms.get(ws.roomCode);
      if (room) {
        if (ws.isHost) room.setHost(false);
        else room.markDisconnected(ws);
      }
    }
  });
});

function handle(ws, msg) {
  switch (msg.t) {
    case 'host_create': {
      const code = makeCode(new Set(rooms.keys()));
      const room = new Room(code, () => broadcast(room));
      rooms.set(code, room);
      ws.roomCode = code; ws.isHost = true; hostSockets.add(ws);
      room.setHost(true);
      safeSend(ws, JSON.stringify({ t: 'hosted', code }));
      broadcast(room);
      break;
    }
    case 'host_attach': { // reconnect a host screen to an existing room
      const room = rooms.get((msg.code || '').toUpperCase());
      if (!room) return safeSend(ws, JSON.stringify({ t: 'error', msg: 'Room not found.' }));
      ws.roomCode = room.code; ws.isHost = true; hostSockets.add(ws);
      room.setHost(true);
      safeSend(ws, JSON.stringify({ t: 'hosted', code: room.code }));
      broadcast(room);
      break;
    }
    case 'join': {
      const room = rooms.get((msg.code || '').toUpperCase());
      if (!room) return safeSend(ws, JSON.stringify({ t: 'error', msg: 'No game with that code.' }));
      const r = room.addPlayer(msg.name, ws);
      if (r.error) return safeSend(ws, JSON.stringify({ t: 'error', msg: r.error }));
      ws.roomCode = room.code; ws.isHost = false; ws.pid = r.pid;
      safeSend(ws, JSON.stringify({ t: 'joined', pid: r.pid, code: room.code, name: r.player.name }));
      broadcast(room);
      break;
    }
    case 'rejoin': {
      const room = rooms.get((msg.code || '').toUpperCase());
      if (!room) return safeSend(ws, JSON.stringify({ t: 'error', msg: 'Room closed.' }));
      const r = room.rejoin(msg.pid, ws);
      if (r.error) return safeSend(ws, JSON.stringify({ t: 'error', msg: 'rejoin-failed' }));
      ws.roomCode = room.code; ws.isHost = false; ws.pid = r.pid;
      safeSend(ws, JSON.stringify({ t: 'joined', pid: r.pid, code: room.code, name: r.player.name }));
      broadcast(room);
      break;
    }
    default: {
      const room = rooms.get(ws.roomCode);
      if (!room) return;
      switch (msg.t) {
        case 'start': if (ws.isHost) room.start(msg.settings); break;
        case 'next': if (ws.isHost) room.advance(); break;
        case 'play_again': if (ws.isHost) room.playAgain(); break;
        case 'submit_drawing': if (ws.pid) room.submitDrawing(ws.pid, msg.img); break;
        case 'submit_lie': if (ws.pid) room.submitLie(ws.pid, msg.text); break;
        case 'submit_vote': if (ws.pid) room.submitVote(ws.pid, msg.choiceId); break;
      }
    }
  }
}

// keepalive ping + reap empty rooms
setInterval(() => {
  wss.clients.forEach((ws) => {
    if (ws.isAlive === false) return ws.terminate();
    ws.isAlive = false;
    try { ws.ping(); } catch (_) {}
  });
}, 30000);

setInterval(() => {
  for (const [code, room] of rooms) {
    const anyone = room.hostConnected || [...room.players.values()].some(p => p.connected);
    if (!anyone) { room.clearTimer(); rooms.delete(code); }
  }
}, 60000);

function lanAddresses() {
  const out = [];
  for (const iface of Object.values(os.networkInterfaces())) {
    for (const a of iface || []) {
      if (a.family === 'IPv4' && !a.internal) out.push(a.address);
    }
  }
  return out;
}

server.listen(PORT, '0.0.0.0', () => {
  const ips = lanAddresses();
  console.log('\n  ================================================');
  console.log('   Drawful v1 is running!  (keep this window open)');
  console.log('  ================================================\n');
  console.log(`   On THIS computer (the big screen):`);
  console.log(`     http://localhost:${PORT}\n`);
  if (ips.length) {
    console.log(`   On PHONES connected to the same Wi-Fi, open:`);
    for (const ip of ips) console.log(`     http://${ip}:${PORT}`);
  } else {
    console.log(`   (No LAN address detected — connect this machine to your network.)`);
  }
  console.log('\n  ------------------------------------------------');
  console.log('   Big screen: click "Present on a big screen".');
  console.log('   Phones:     open the address above, tap "Join a game".');
  console.log('  ------------------------------------------------\n');
});
