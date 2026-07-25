'use strict';
const crypto = require('crypto');
const PROMPTS = require('./prompts');

const CONTENT_MODES = ['adult', 'mixed', 'clean'];
function promptPool(mode) {
  if (mode === 'clean') return PROMPTS.clean;
  if (mode === 'mixed') return PROMPTS.clean.concat(PROMPTS.adult);
  return PROMPTS.adult; // default
}

// ---- tuning ----
const MAX_PLAYERS = 8;
const MIN_PLAYERS = 2; // 3+ is more fun, but allow 2 for testing
const TIMERS = { drawing: 100, answering: 55, voting: 35, reveal: 12 }; // seconds
const SCORE = { correctGuess: 1000, perCorrectForArtist: 500, perVoteForLie: 500, everyoneFooledBonus: 1000 };

const AVATAR_COLORS = ['#ef476f','#ffd166','#06d6a0','#118ab2','#f78c6b','#c77dff','#ff8fab','#4cc9f0'];
const norm = (s) => (s || '').trim().replace(/\s+/g, ' ').toLowerCase();

function makeCode(taken) {
  const letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // no I/O
  let code;
  do {
    code = Array.from({ length: 4 }, () => letters[crypto.randomInt(letters.length)]).join('');
  } while (taken.has(code));
  return code;
}

function shuffle(arr) {
  const a = arr.slice();
  for (let i = a.length - 1; i > 0; i--) {
    const j = crypto.randomInt(i + 1);
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

class Room {
  constructor(code, onChange) {
    this.code = code;
    this.onChange = onChange; // called whenever state changes -> triggers broadcast
    this.players = new Map(); // pid -> player
    this.hostConnected = false;
    this.phase = 'lobby';
    this.rounds = 2;
    this.round = 0;
    this.mode = 'adult';
    this.pool = promptPool(this.mode);
    this.promptDeck = shuffle(this.pool);
    this.deckIdx = 0;
    this.queue = [];        // ordered list of artist pids to present this round
    this.qi = -1;           // index into queue
    this.current = null;    // active presentation object
    this.timer = null;
    this.deadline = 0;
    this.lastResults = null;
  }

  // ---------- players ----------
  addPlayer(name, ws) {
    if (this.players.size >= MAX_PLAYERS) return { error: 'Game is full (8 players max).' };
    if (this.phase !== 'lobby') return { error: 'Game already in progress.' };
    const clean = (name || '').trim().slice(0, 16) || 'Player';
    // unique-ish name
    let finalName = clean, n = 2;
    while ([...this.players.values()].some(p => p.name.toLowerCase() === finalName.toLowerCase())) {
      finalName = `${clean} ${n++}`;
    }
    const pid = crypto.randomUUID();
    const player = {
      pid, name: finalName, ws, score: 0, connected: true,
      color: AVATAR_COLORS[this.players.size % AVATAR_COLORS.length],
      prompt: null, drawing: null, lie: null, vote: null, gotFor: false,
    };
    this.players.set(pid, player);
    this.onChange();
    return { pid, player };
  }

  rejoin(pid, ws) {
    const p = this.players.get(pid);
    if (!p) return { error: 'not-found' };
    p.ws = ws; p.connected = true;
    this.onChange();
    return { pid, player: p };
  }

  setHost(connected) { this.hostConnected = connected; this.onChange(); }

  markDisconnected(ws) {
    for (const p of this.players.values()) {
      if (p.ws === ws) { p.connected = false; p.ws = null; }
    }
    this.onChange();
  }

  activePlayers() { return [...this.players.values()].filter(p => p.connected); }

  // ---------- lifecycle ----------
  start(settings) {
    if (this.phase !== 'lobby') return;
    if (this.activePlayers().length < MIN_PLAYERS) return;
    this.rounds = Math.min(3, Math.max(1, parseInt(settings && settings.rounds, 10) || 2));
    this.mode = CONTENT_MODES.includes(settings && settings.mode) ? settings.mode : 'adult';
    this.pool = promptPool(this.mode);
    this.promptDeck = shuffle(this.pool);
    this.deckIdx = 0;
    this.round = 0;
    for (const p of this.players.values()) p.score = 0;
    this.beginRound();
  }

  beginRound() {
    this.round += 1;
    for (const p of this.players.values()) {
      p.prompt = this.players.get(p.pid) && p.connected ? this.drawPrompt() : null;
      p.drawing = null;
    }
    this.phase = 'drawing';
    this.setTimer('drawing', () => this.endDrawing());
    this.onChange();
  }

  drawPrompt() {
    if (this.deckIdx >= this.promptDeck.length) { this.promptDeck = shuffle(this.pool); this.deckIdx = 0; }
    return this.promptDeck[this.deckIdx++];
  }

  submitDrawing(pid, img) {
    const p = this.players.get(pid);
    if (!p || this.phase !== 'drawing' || p.drawing) return;
    if (typeof img !== 'string' || img.length > 600000) return; // ~600KB cap
    p.drawing = img;
    this.onChange();
    if (this.activePlayers().filter(x => x.prompt).every(x => x.drawing)) this.endDrawing();
  }

  endDrawing() {
    this.clearTimer();
    // build presentation queue from players who submitted a drawing
    this.queue = shuffle(this.activePlayers().filter(p => p.drawing && p.prompt).map(p => p.pid));
    this.qi = -1;
    if (this.queue.length === 0) return this.finishRound();
    this.nextPresentation();
  }

  nextPresentation() {
    this.clearTimer();
    this.qi += 1;
    if (this.qi >= this.queue.length) return this.finishRound();
    const artist = this.players.get(this.queue[this.qi]);
    this.current = {
      artistId: artist.pid,
      artistName: artist.name,
      artistColor: artist.color,
      img: artist.drawing,
      truth: artist.prompt,
      lies: new Map(),  // pid -> text
      choices: null,
      votes: new Map(), // pid -> choiceId
      idx: this.qi + 1,
      total: this.queue.length,
    };
    // reset per-drawing player flags
    for (const p of this.players.values()) { p.lie = null; p.vote = null; }
    this.phase = 'answering';
    this.setTimer('answering', () => this.endAnswering());
    this.onChange();
  }

  submitLie(pid, text) {
    const c = this.current;
    const p = this.players.get(pid);
    if (!c || this.phase !== 'answering' || !p) return;
    if (pid === c.artistId || p.lie) return;
    const clean = (text || '').trim().slice(0, 60);
    if (!clean) return;
    p.lie = clean;
    c.lies.set(pid, clean);
    this.onChange();
    const answerers = this.activePlayers().filter(x => x.pid !== c.artistId);
    if (answerers.length && answerers.every(x => x.lie)) this.endAnswering();
  }

  endAnswering() {
    this.clearTimer();
    const c = this.current;
    if (!c) return;
    // Build choice list. Merge duplicate lies. A lie equal to the truth = auto-correct for its author.
    const truthNorm = norm(c.truth);
    const map = new Map(); // normText -> {text, authors:[pid], isTruth}
    map.set(truthNorm, { id: 'truth', text: c.truth, authors: [], isTruth: true });
    c.autoCorrect = new Set();
    for (const [pid, text] of c.lies) {
      if (norm(text) === truthNorm) { c.autoCorrect.add(pid); continue; }
      const k = norm(text);
      if (map.has(k) && k !== truthNorm) map.get(k).authors.push(pid);
      else map.set(k, { id: crypto.randomUUID().slice(0, 8), text, authors: [pid], isTruth: false });
    }
    c.choices = shuffle([...map.values()]);
    this.phase = 'voting';
    this.setTimer('voting', () => this.endVoting());
    this.onChange();
  }

  submitVote(pid, choiceId) {
    const c = this.current;
    const p = this.players.get(pid);
    if (!c || this.phase !== 'voting' || !p) return;
    if (pid === c.artistId || p.vote) return;
    const choice = c.choices.find(ch => ch.id === choiceId);
    if (!choice) return;
    if (choice.authors.includes(pid)) return; // can't vote your own lie
    p.vote = choiceId;
    c.votes.set(pid, choiceId);
    this.onChange();
    const voters = this.activePlayers().filter(x => x.pid !== c.artistId && !c.autoCorrect.has(x.pid));
    if (voters.length && voters.every(x => x.vote)) this.endVoting();
  }

  endVoting() {
    this.clearTimer();
    const c = this.current;
    if (!c) return;
    const artist = this.players.get(c.artistId);
    const deltas = new Map(); // pid -> points this drawing
    const add = (pid, n) => deltas.set(pid, (deltas.get(pid) || 0) + n);

    // votes per choice
    const votersByChoice = new Map();
    for (const [voter, cid] of c.votes) {
      if (!votersByChoice.has(cid)) votersByChoice.set(cid, []);
      votersByChoice.get(cid).push(voter);
    }

    let correctGuessers = 0;
    const answererCount = this.activePlayers().filter(p => p.pid !== c.artistId).length;

    for (const choice of c.choices) {
      const voters = votersByChoice.get(choice.id) || [];
      if (choice.isTruth) {
        for (const v of voters) { add(v, SCORE.correctGuess); correctGuessers++; }
      } else {
        for (const a of choice.authors) add(a, voters.length * SCORE.perVoteForLie);
      }
    }
    // auto-correct: wrote the actual truth as their "lie"
    for (const pid of c.autoCorrect) { add(pid, SCORE.correctGuess); correctGuessers++; }

    // artist reward for clear drawing
    if (artist) {
      add(artist.pid, correctGuessers * SCORE.perCorrectForArtist);
      if (answererCount > 0 && correctGuessers === answererCount) add(artist.pid, SCORE.everyoneFooledBonus);
    }

    for (const [pid, n] of deltas) { const p = this.players.get(pid); if (p) p.score += n; }

    // build reveal payload
    this.lastResults = {
      artistName: c.artistName, artistColor: c.artistColor, img: c.img, truth: c.truth,
      idx: c.idx, total: c.total,
      choices: c.choices.map(ch => ({
        text: ch.text, isTruth: ch.isTruth,
        authors: ch.authors.map(pid => this.playerName(pid)),
        voters: (votersByChoice.get(ch.id) || []).map(pid => this.playerName(pid)),
      })),
      deltas: [...deltas.entries()].map(([pid, n]) => ({ name: this.playerName(pid), points: n }))
        .filter(d => d.points > 0).sort((a, b) => b.points - a.points),
    };
    this.phase = 'reveal';
    this.setTimer('reveal', () => this.nextPresentation());
    this.onChange();
  }

  finishRound() {
    this.clearTimer();
    this.current = null;
    if (this.round < this.rounds) { this.phase = 'roundscores'; this.onChange(); }
    else { this.phase = 'gameover'; this.onChange(); }
  }

  advance() { // host "Next / Continue" button
    if (this.phase === 'reveal') this.nextPresentation();
    else if (this.phase === 'roundscores') this.beginRound();
    else if (this.phase === 'drawing') this.endDrawing();
    else if (this.phase === 'answering') this.endAnswering();
    else if (this.phase === 'voting') this.endVoting();
  }

  playAgain() {
    this.clearTimer();
    this.phase = 'lobby';
    this.round = 0; this.current = null; this.lastResults = null;
    for (const p of this.players.values()) { p.score = 0; p.prompt = p.drawing = p.lie = p.vote = null; }
    this.onChange();
  }

  // ---------- timers ----------
  setTimer(kind, fn) {
    this.clearTimer();
    const secs = TIMERS[kind] || 30;
    this.deadline = Date.now() + secs * 1000;
    this.timer = setTimeout(fn, secs * 1000);
  }
  clearTimer() { if (this.timer) clearTimeout(this.timer); this.timer = null; this.deadline = 0; }

  playerName(pid) { const p = this.players.get(pid); return p ? p.name : '???'; }

  leaderboard() {
    return [...this.players.values()].sort((a, b) => b.score - a.score)
      .map((p, i) => ({ name: p.name, color: p.color, score: p.score, connected: p.connected, rank: i + 1 }));
  }

  // ---------- serialization ----------
  playerList() {
    return [...this.players.values()].map(p => ({
      pid: p.pid, name: p.name, color: p.color, score: p.score, connected: p.connected,
      done: this.playerDone(p),
    }));
  }

  playerDone(p) {
    if (this.phase === 'lobby') return false;
    if (this.phase === 'drawing') return !!p.drawing || !p.prompt;
    if (this.phase === 'answering') return this.current ? (p.pid === this.current.artistId || !!p.lie) : false;
    if (this.phase === 'voting') return this.current ? (p.pid === this.current.artistId || this.current.autoCorrect.has(p.pid) || !!p.vote) : false;
    return true;
  }

  // View sent to the HOST (big screen) — public info only.
  hostView() {
    const base = { phase: this.phase, code: this.code, round: this.round, rounds: this.rounds,
      deadline: this.deadline, players: this.playerList() };
    if (this.phase === 'answering' || this.phase === 'voting') {
      const c = this.current;
      // Artist stays anonymous until the reveal — don't leak their name/color.
      base.drawing = { img: c.img, idx: c.idx, total: c.total };
      if (this.phase === 'voting') base.choices = c.choices.map(ch => ({ text: ch.text }));
    }
    if (this.phase === 'reveal') base.results = this.lastResults;
    if (this.phase === 'roundscores' || this.phase === 'gameover') base.leaderboard = this.leaderboard();
    return base;
  }

  // View sent to a PLAYER — includes their private prompt/choices.
  playerView(pid) {
    const p = this.players.get(pid);
    if (!p) return { phase: 'kicked' };
    const c = this.current;
    const you = { pid: p.pid, name: p.name, color: p.color, score: p.score };
    const base = { phase: this.phase, code: this.code, round: this.round, rounds: this.rounds,
      deadline: this.deadline, you, players: this.playerList() };
    if (this.phase === 'drawing') { base.prompt = p.prompt; base.submitted = !!p.drawing; base.noPrompt = !p.prompt; }
    if (this.phase === 'answering' && c) {
      base.isArtist = pid === c.artistId;
      base.drawing = { img: c.img, idx: c.idx, total: c.total }; // artist anonymous until reveal
      base.submitted = !!p.lie;
    }
    if (this.phase === 'voting' && c) {
      base.isArtist = pid === c.artistId;
      base.autoCorrect = c.autoCorrect.has(pid);
      base.drawing = { img: c.img, idx: c.idx, total: c.total }; // artist anonymous until reveal
      base.submitted = !!p.vote;
      base.yourVote = p.vote;
      base.choices = c.choices
        .filter(ch => !ch.authors.includes(pid)) // hide your own lie
        .map(ch => ({ id: ch.id, text: ch.text }));
    }
    if (this.phase === 'reveal') base.results = this.lastResults;
    if (this.phase === 'roundscores' || this.phase === 'gameover') base.leaderboard = this.leaderboard();
    return base;
  }
}

module.exports = { Room, makeCode, MAX_PLAYERS, MIN_PLAYERS };
