# Week 2 Sprint Plan

**Theme:** First Agent Stubs & Communication Flow

---

## Goals
- Move from **planning** into **hands-on prototypes**.
- Create the **first working “Agent Stub”** — a minimal program that pretends to be an AI agent.
- Establish the **logging + reporting flow** so agents can send updates (to journal, console, or file).

---

## Tasks

### 1. Setup Agent Playground
- [ ] Create a new folder `/agents` in the repo.
- [ ] Add a simple `agent_stub.php` (or `agent_stub.py` if testing Python).
- [ ] Make the stub accept a “task” string and write it into a local log file.

### 2. Define Agent Log Format
- [ ] Create `/logs/agent-log.txt`.
- [ ] Format: `[Day X | AgentName]: Task started → Task done`.

### 3. Reporting Simulation
- [ ] Each stubbed agent writes to `agent-log.txt`.
- [ ] Add a “report script” (`report.php`) that can read logs and summarize tasks.

### 4. Journal Sync
- [ ] End-of-day: manually copy summarized logs into `journal.txt` under a “Team Activity” section.

---

## Deliverables
- A working `agents/` folder with a **first agent stub**.
- A `logs/` system that shows activity.
- Journal updated with “AI Team Activity” for at least 2 tasks.

---

## Stretch Goals
- Try running **two stubs** (e.g., `dev_agent.php` and `content_agent.php`).
- Make them log independently into `agent-log.txt`.
- Imagine BrainBox reading this file in the future.

---

✅ By the end of Week 2, you’ll **see agents working (even if fake at first)** — making the project feel alive.
