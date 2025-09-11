# Week 3 — Agent Roles + BrainBox

✅ **Goal**: Shift from infrastructure into real agent behaviors.

## Tasks
- [ ] Extend `agent_stub.php` into multiple role-based agents:
  - [ ] DevAgent (coding, scaffolding tasks)
  - [ ] ContentAgent (writing, docs, explanations)
- [ ] Create `brainbox.php` as the orchestrator:
  - [ ] Parse command/input
  - [ ] Decide which agent to assign
  - [ ] Collect and log response
- [ ] Integrate BrainBox with existing log + report system
- [ ] Journal + roadmap updated daily

## Stretch Goals
- [ ] Add ability for BrainBox to “chain” tasks between agents
- [ ] First cross-agent test (e.g., DevAgent builds stub, ContentAgent documents it)
- [ ] Plan Week 4: expanding team roles (e.g., TestAgent, ResearchAgent)

### End of Week 3 Target
- Agents can receive tasks from BrainBox and log their outputs.
- Reports reflect role-specific activity.
- First demo: BrainBox instructs both DevAgent and ContentAgent in one flow.

# Week 3 — BrainBox Orchestration

✅ **Goal**: Move beyond simulation. BrainBox should actually call real agent scripts and coordinate them.

---

## Tasks
- [ ] Expand `DevAgent` stub:
  - [ ] Accept tasks from BrainBox
  - [ ] Log task status updates (started → completed/failed)
  - [ ] Provide dummy code output (placeholder for now)
- [ ] Expand `ContentAgent` stub:
  - [ ] Accept tasks from BrainBox
  - [ ] Log task status updates (started → completed/failed)
  - [ ] Generate placeholder text output
- [ ] Update `brainbox.php`:
  - [ ] Route tasks to agent scripts (instead of only simulating)
  - [ ] Capture their return messages
  - [ ] Log orchestration trace
- [ ] Enhance `report.php` to display:
  - [ ] BrainBox dispatches
  - [ ] Agent responses (chained logs)
- [ ] Journal + roadmap kept in sync daily

---

## Stretch Goals
- [ ] First simple multi-step chain:
  - BrainBox dispatches a **Dev task** → DevAgent logs output
  - Then BrainBox automatically dispatches a **Content task** → ContentAgent writes documentation about the Dev output
- [ ] Add support for task IDs to track related tasks
- [ ] Explore very first “BrainBox memory” concept (basic state file)

---

### End of Week 3 — Expected Outcome ✅
- BrainBox no longer just simulates, it **routes real tasks**.
- DevAgent + ContentAgent have their own scripts.
- Logs + reports show full lifecycle: dispatched → started → completed/failed.
- First glimpse of multi-agent collaboration in action.

