# AI RPG — Claude Code Game Master

A persistent, browser-playable RPG where **Claude Code acts as the Game Master**. No Anthropic API key needed — Claude Code itself is the backend. The game runs entirely through file-based communication between your browser and Claude Code.

---

## How It Works

```
Browser (UI) ──► post-message.php ──► chat.json ──► Claude Code (GM)
                                                            │
Browser (UI) ◄── polling every 2s ◄── chat.json ◄──────────┘
```

1. The player types an action in the browser
2. PHP writes it to `data/chat.json`
3. Claude Code reads the file, generates a GM response, and writes it back
4. The browser polls every 2 seconds and displays new messages with a typewriter animation

Everything is file-based. No WebSocket, no API, no backend server — just Claude Code reading and writing JSON files.

---

## Features

- **Persistent world** — all game state stored in JSON + Markdown files; nothing is lost between sessions
- **Full GM loop** — Claude Code narrates, makes decisions, tracks stats, manages NPCs and quests
- **Typewriter animation** — GM messages appear character by character; click to skip
- **Voice narration** — optional browser TTS reads GM messages aloud (Web Speech API, no key needed)
- **Tabbed HUD** — Stats, Lore, Characters, Quests, and Info panels
- **Save / Load system** — named save slots with narrative recap on load
- **World presets** — swap entire world settings without losing character progress
- **New Game wizard** — 3-step wizard to choose world preset or describe a new one
- **Companion system** — flexible: Demon / Spirit / Guide / None (world-defined)
- **Mental state system** — stress accumulates; at 100 the companion attempts a takeover
- **Inner Demon** — manifests on first death, built from the character's own fears and failures

---

## Setup

### Requirements

- [Claude Code](https://claude.ai/code) (the CLI tool, not the API)
- [Laragon](https://laragon.org/) or any local PHP server (Apache + PHP)

### Installation

```bash
# Clone into your web root
git clone https://github.com/hilezkorra/ai-rpg.git "AI RPG"
```

Then place the folder inside your Laragon `www/` directory (or your web server's document root).

### Start the Game

1. Open Claude Code in the `AI RPG` project directory
2. Make sure Laragon is running
3. Open `http://localhost/AI RPG/ui/index.html` in your browser
4. Type `play rpg` in Claude Code

The GM will write an opening scene to `data/chat.json`. The browser picks it up within 2 seconds.

---

## Project Structure

```
AI RPG/
├── ui/
│   └── index.html          # The entire game UI (single file)
├── data/                   # Live game state (JSON — GM reads and writes these)
│   ├── character.json
│   ├── world.json
│   ├── chat.json           # IPC between browser and Claude Code
│   ├── events.json
│   ├── factions.json
│   ├── npcs.json
│   ├── lore.json
│   └── quests.json
├── world/                  # World lore and locations (Markdown)
├── factions/               # Faction files (Markdown)
├── systems/                # Power system, rankings, companion rules (Markdown)
├── characters/             # Character sheets (Markdown)
├── memory/                 # GM memory: global summary + active threads
├── saves/                  # Named save slots (git-ignored)
├── world-presets/          # Reusable world snapshots
│   └── earth-invasion/     # Default world: alien occupation, 3 factions
├── boilerplate/            # Clean-state files for /new-game resets
├── system/                 # GM prompts and world-builder instructions
├── post-message.php        # Write player messages to chat.json
├── game-api.php            # List saves, list worlds, send GM commands
└── .claude/
    └── launch.json         # Launch config for the UI dev server
```

---

## GM Commands

Type any of these in the chat to trigger special behaviour:

| Command | What it does |
|---|---|
| `/new-game` | Opens the world + character wizard |
| `/save-game [name]` | Save to a named slot |
| `/load-game [path]` | Restore a save with a narrative recap |
| `/save-world [name]` | Save world/lore/factions as a reusable preset |
| `/rewind` | Restore last auto-snapshot (every 5 GM turns) |
| `/force-event [description]` | Inject a world event immediately |
| `/spawn [name] [role]` | Add an NPC to the current scene |
| `/set-stat [stat] [value]` | Override a character stat |
| `/set-mental [value]` | Set mental state (0–100) |
| `/simulate [scenario]` | Test a scenario without saving |
| `/reveal [lore-id]` | Reveal a hidden lore entry |

---

## Default World — Earth Invasion

The included world preset drops you into a post-contact Earth:

- **98% of humanity** vanished into a System-controlled "tutorial space"
- **You are one of the 2%** left behind — an anomaly by alien classification
- **Three alien factions** with conflicting agendas:
  - **The Vorath** — silicon hivemind harvesters, resource extraction focus
  - **The Shriven** — digital consciousness researchers, formerly biological
  - **The Pale Court** — ancient architects of the System, bound by a non-intervention compact
- **The System** — a power framework that manifests abilities drawn from the user's own psychology
- **The Inner Demon** — manifests after the player's first death, built from their fears and failures

Full world lore lives in `factions/`, `world/`, and `systems/`.

---

## Adding Your Own World

Run `/new-game` and choose **"Create a new world"**. You can either:
- Write a summary and let Claude generate everything
- Ask Claude to interview you with questions

The world builder creates all required files (`factions/`, `world/`, `systems/`) and saves a reusable preset. The companion system type (Demon / Spirit / Guide / None) is a world-level decision and drives all mental state escalation behaviour.

---

## Technical Notes

**Why no API?** Claude Code is already running locally with full filesystem access. The browser just needs to pass messages in. `post-message.php` writes to `data/chat.json`; Claude Code polls, responds, and writes back. Zero infrastructure.

**Why PHP?** Browsers can't write files. PHP is one line. Any server-side language works — the API surface is minimal (read a JSON body, write to a file).

**Why Markdown + JSON?** The GM needs to read context efficiently. Flat Markdown files are easy to read incrementally. JSON files are the live game state the UI polls. Both can be updated atomically.

---

## License

MIT
