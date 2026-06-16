# PLAYPTL — Project Flow Document

**Platform:** playptl.com  
**Document Type:** System Flow & User Guide  
**Version:** 1.1  
**Date:** June 2026  

---

## 1. Introduction

PlayPTL is a tennis league and tournament management platform. Admins run the full season — players register, group matches are scheduled, standings are calculated, and playoffs complete the tournament.

This document explains:

- The overall system flow
- What admins do and in what order
- What players do
- What happens in each tournament phase
- Important rules the system enforces

---

## 2. Platform Overview

### 2.1 Three types of users

| User | Who | What they do |
|------|-----|--------------|
| **Admin** | Tournament organizer / PTL staff | Tournament setup, roster management, match scheduling, results, playoffs |
| **Player** | Registered tennis player | Manage profile, join tournaments, play matches, enter scores |
| **Visitor (Public)** | No login required | View league standings, schedules, and charity pages |

### 2.2 Where users go after login

- **Admin** → Admin Panel (`/admin`)
- **Player** → My Profile (`/player/my-profile`)
- **Visitor** → Public pages (`/league/...`, `/charity`)

---

## 3. System Structure

The system follows a **tree structure**. Top to bottom:

```
TOURNAMENT (Season)
    │
    ├── DIVISION (Group Card)
    │       Example: "Voyagers Singles 3.5"
    │       │
    │       ├── SUBGROUP
    │       │       Example: Group A, Group B, Group C
    │       │       │
    │       │       └── PLAYERS (registered in this subgroup)
    │       │
    │       ├── GROUP MATCHES (round-robin within subgroups)
    │       │
    │       └── PLAYOFFS (knockout bracket after group stage)
    │
    └── (more divisions — 4.0 Singles, Doubles, etc.)
```

### Simple example

**Tournament:** PTL Spring 2026  
**Division:** Voyagers Singles (skill 3.5)  
**Subgroups:** Group A, Group B, Group C  
**Players:** 5–8 players per subgroup  
**Matches:** Round-robin per subgroup — Week 1, Week 2, …  
**Playoffs:** Top players enter knockout after group stage  

---

## 4. Admin Flow — Running a Full Tournament

### PHASE 1: Initial setup (once per season or as needed)

#### Step 1 — Create subgroups
Admin panel → **Groups**  
Create subgroup names such as "Group A", "Group B", "Group C". These are templates linked to divisions later.

#### Step 2 — Create divisions (Group Cards)
Admin panel → **Subgroups (Group Cards)**  
Each division defines skill level + format:

- **Name:** Voyagers Singles, Champions Doubles, etc.
- **Format:** Singles or Doubles
- **Skill tier:** 3.0, 3.5, 4.0, etc.
- **Playoff format:** Top 4 seeds to Quarterfinals, others to Round of 16, etc.
- **Attach subgroups:** Link Group A, B, C

#### Step 3 — View and manage players
Admin panel → **Players**  
- Full list of registered players (singles and doubles registrations shown together)
- **Login as** column — whether the account was created as Singles or Doubles (account type; does not change if they later play another format in a tournament)
- **Active tournaments** — tournaments the player is currently in (in-season window)
- Search, tournament filter, pagination
- Edit or delete player records

**Note:** New players register via the public website or their player profile (**Choose League**). Admin does not create new registrations from the admin panel; admin manages existing players and moves them between subgroups.

---

### PHASE 2: Create a new tournament

#### Step 4 — Create the tournament
Admin panel → **Tournaments** → Create

| Field | Meaning |
|-------|---------|
| Tournament name | PTL Spring 2026 |
| Start date | Season start |
| End date | Season end |
| Status | Active / Upcoming / Completed |
| Entry fee | Separate singles and doubles fees (online payment) |
| Attach divisions | Which group cards belong to this tournament |

**Important:** Tournament start/end dates bound the whole season. Group matches and playoffs should fall within this window (or extend the tournament).

#### Step 5 — Open Tournament Management
Admin panel → **Tournaments** → Manage  
Each attached division is listed. Work through each division separately.

---

### PHASE 3: Per division (repeat Steps 6–12)

One tournament can have multiple divisions (3.5 Singles, 4.0 Singles, Doubles, etc.). Repeat the steps below for each.

#### Step 6 — Players join via registration
Players register through:

- Public **Register** page (`/register`), or
- Player **Choose League** after login

Admin assigns registered players to subgroups from **Subgroups & Players**:

- Move players between Group A, B, C
- **Doubles only:** Assign a **partner** from players in the same subgroup who do not already have a partner
- Partners are scoped **per tournament and division** — the same person can have different partners in different tournaments at the same time
- Moving a player to a subgroup also moves their doubles partner

**Rule:** One player may be in only **one singles division** or **one doubles division** per tournament.

#### Step 7 — Review subgroups and roster
**Page:** Subgroups & Players  

- See which player is in which subgroup
- Move players between subgroups
- Add new subgroups
- For doubles, link partners before or after subgroup assignment
- Unassigned players appear in a separate section until placed in a subgroup

#### Step 8 — Schedule group matches
**Page:** Matches  

**Prerequisites:**

- Tournament start/end dates set
- At least 2 players per subgroup
- All players assigned to subgroups

**How to schedule:**

1. Select **Group start date** (within tournament window)
2. Click **Schedule matches**
3. System creates round-robin matches for **all subgroups (A, B, C)**
4. Matches are split into **Week 1, Week 2, Week 3…**
5. Set **Group end date** afterward (on or after the last match date)

**Match calendar rules:**

- Matches only **Monday through Saturday**
- **No matches on Sunday**
- Odd number of players (e.g. 5) → one bye per round — that player has no match that week
- Maximum one match per player per week

**If the tournament window is too short:**  
If matches would extend past the tournament end date, the system shows a warning — extend the tournament via **Edit Tournament** first.

**Cancel matches:**  
**Cancel this group matches** deletes all scheduled matches for the division and emails affected players. Not available after playoffs have started.

#### Step 9 — Enter match results
Results can come from:

- **Admin** on the Matches page
- **Player** via My Matches (photo upload + score)

Standings update when results are recorded.

#### Step 10 — Check standings / points
**Page:** Points  

- Full division ranking
- Filter by subgroup
- Points system:
  - Straight-sets win → Winner 14, Loser up to 8
  - Three-set win → Winner 12, Loser up to 8
  - Walkover → Winner 10, Loser 0

#### Step 11 — Qualifier (playoff paths)
**Page:** Qualifier  

**Unlocks when:** At least one group match has a result.

The system suggests paths from standings:

| Path | Meaning |
|------|---------|
| Quarter | Seed directly into quarterfinals |
| Pre-Q / Round of 16 | Play Round of 16 first |
| Pre-Pre-Q | Pre-Pre-Quarter round first |
| Eliminated | Out of playoffs |

Admin can review, adjust, and add notes. Saving generates the playoff bracket.

#### Step 12 — Playoffs
**Page:** Playoffs  

**Before scheduling:**

- Qualifier paths saved
- Tournament end date allows playoffs to fit
- Group matches completed

**Playoff schedule:**

1. **Playoff start date** — must be **after** group matches end
2. **Playoff end date** — last possible final date
3. Click **Schedule matches** → playoffs start; group scheduling locks

**Typical rounds:**  
Pre-Pre-Q → Round of 16 → Quarterfinals → Semifinals → Final

Admin enters results and uses **Advance winners** to move players to the next round.

**Close playoffs:**  
When all results are final → **Playoff close** → scores lock.

#### Step 13 — Finish tournament
From League Management, **Finish** the tournament → season officially complete.

---

## 5. Player Flow

### 5.1 New player — Registration

**Page:** `/register` (public)

1. Choose **Singles** or **Doubles**
2. Enter details — name, email, password, phone, city, skill level, age group
3. Select a **tournament**
4. System assigns a division based on skill level (tier bucket — e.g. 3.25 maps to the 3.5 tier)
5. Pay online via **Stripe**
6. Account is created — log in

**Doubles registration:**

- Partner details required
- Division uses average skill of both players
- Partner receives an email to set up their account

### 5.2 After login — My Profile

| Section | Purpose |
|---------|---------|
| Personal Information | Name, phone, photo, skill level; **Active Tournaments** with dates and division/subgroup |
| Choose League | Register for additional tournaments (with payment) |
| Password & Security | Change password |
| My Matches | View and manage scheduled matches (all active tournaments) |
| Upload Image | Upload match photos |

**Active tournaments:** All in-season tournaments the player is registered in are shown with window dates. Upcoming tournaments appear separately.

### 5.3 Join another tournament

Use **Choose League**. Skill level must be set under Personal Information first.

### 5.4 When matches appear

Matches appear in **My Matches** only after **admin has scheduled** them.

The page shows:

- Active tournaments the player is in
- Upcoming matches in calendar style
- Opponent name, date, time, venue
- Playoff matches if qualified

### 5.5 Match flow (player)

```
Admin schedules match
        ↓
Player sees match (My Matches)
        ↓
Player sets or updates venue / date / time
        ↓
After the match — PHOTO UPLOAD required
        ↓
Enter score (sets or walkover)
        ↓
Standings update (public league page)
```

**Player rules:**

- Score cannot be entered without a photo upload
- Scores stay hidden from other players until entered (privacy)
- A player may have matches in multiple active tournaments on the same day

### 5.6 View standings

No separate standings page for players. Use the public league page:

**URL:** `/league/{tournament-name}/{division-name}`

Tabs: Standings, Schedule, Playoffs bracket.

### 5.7 Playoffs (player)

Players do not self-register for playoffs. If they qualify from group stage:

- Playoff matches appear in **My Matches**
- Same flow — photo upload → enter score

---

## 6. Registration Rules

| Rule | Detail |
|------|--------|
| Skill → Division | Player skill level determines division assignment |
| Doubles skill | Average of both players' skill levels |
| One format per tournament | One singles OR one doubles division per tournament |
| Multiple tournaments | Player may join different tournaments simultaneously |
| Different partners | Different doubles partner allowed in each tournament |
| Registration closes | When matches are scheduled for that division |
| After playoffs start | No new registrations for that tournament |

**Account vs tournament:** `Login as` (Singles/Doubles) on the user account is set at registration and is not changed when playing doubles in a tournament or when admin assigns a partner.

---

## 7. Match Scheduling Rules (Summary)

| Rule | Detail |
|------|--------|
| Play days | Monday – Saturday |
| Off day | Sunday — no matches |
| Weekly structure | One round per week; max one match per player |
| Odd players | Bye week — no match that round |
| Auto schedule | One button schedules all subgroups |
| Reschedule | Date changes trigger email to players |
| Same dates re-scheduled | No duplicate email |
| Cancel all | Deletes division schedule + emails players |

---

## 8. Playoffs Rules (Summary)

| Rule | Detail |
|------|--------|
| Unlock | After at least one group match result |
| Paths | Auto from standings; admin can adjust |
| Playoff start | Must be after group matches end |
| Short tournament window | Extend tournament first or see warning |
| After playoffs start | Group match scheduling locked |
| Playoff close | Results locked |

---

## 9. Payment Flow

### Player payment
- **Stripe** on Register or Choose League
- Successful payment → registered in tournament
- Admin can view Payment History

### Admin-assigned players
- Players who registered online have payment records
- Admin subgroup assignment does not require a separate payment step

---

## 10. Charity

### Public
- `/charity` — charity page
- Donate (Stripe) or submit material/volunteer form

### Admin
- Manage charity causes
- View donations
- Send bulk email to donors

---

## 11. Email Notifications

All emails use a **branded template** (PTL logo, green header, consistent layout).

| Event | Recipient |
|-------|-----------|
| New account (admin-created) | Player — login details |
| Registration confirmed | Player |
| Doubles partner added | Partner — account setup link |
| Password reset | User — reset link |
| Match scheduled | Home + away players |
| Match rescheduled | Affected players |
| All matches cancelled | All players in division |
| Playoff match scheduled | Qualified players |
| Charity message | Donors |

**Note:** Match emails are queued — a queue worker should run on the server. Set `APP_URL` in `.env` so the logo displays correctly in emails.

---

## 12. Complete Season — Example

**Tournament: PTL Spring 2026**

| Week | Admin | Player |
|------|-------|--------|
| Week 0 | Create tournament, attach divisions, assign subgroups/partners | Players register online |
| Week 1 | Set group start date, schedule matches | Matches appear in My Matches |
| Week 2–8 | Monitor results, check Points | Play weekly; upload photo, enter score |
| Week 9 | Set group end date, review Qualifier | — |
| Week 10 | Set playoff dates, schedule playoffs | Playoff matches appear |
| Week 11–12 | Advance winners, run Final | Play playoff matches |
| Week 13 | Playoff close, finish tournament | — |

---

## 13. Common Problems & Solutions

| Problem | What to do |
|---------|------------|
| Matches won't schedule | Check: tournament dates set? 2+ players per subgroup? Group start date? |
| Group end date won't save | Tournament end date too early — extend via Edit Tournament |
| Playoffs won't schedule | Extend tournament, complete group matches, save qualifier paths |
| Player doesn't see match | Admin scheduled? Player in correct subgroup? |
| Can't enter score | Upload match photo first |
| No email received | Check mail config and queue worker on server |
| Registration closed | Matches already scheduled for that division |
| Partner not in dropdown | Partner may already be paired, or in a different subgroup |

---

## 14. Admin Panel — Page Reference

| Menu | Purpose |
|------|---------|
| Dashboard | Overview counts |
| Tournaments | Create / edit / manage tournaments |
| Groups | Subgroup templates |
| Subgroups | Divisions (skill + format) |
| Players | All players, search, active tournaments, login-as type |
| Payment History | Online payments |
| Charity Causes | Campaign management |
| Charity Donations | Donations + email |
| Announcements | Site announcements |

### Inside a tournament (per division)

| Tab | Purpose |
|-----|---------|
| Subgroups & Players | Roster, partner assignment (doubles), subgroup moves |
| All Players | Division roster view |
| Matches | Schedule + results |
| Points | Standings |
| Qualifier | Playoff paths |
| Playoffs | Bracket + results |

---

## 15. Player Portal — Page Reference

| Section | Purpose |
|---------|---------|
| Personal Information | Profile, skill level, active tournaments with dates |
| Choose League | Join more tournaments |
| Password & Security | Password change |
| My Matches | Schedule, venue, scores (active tournaments) |
| Upload Image | Match photos |

### Public (no login)

| Page | Purpose |
|------|---------|
| `/league/{name}` | Tournament overview |
| `/league/{name}/{division}` | Standings + schedule + playoffs |
| `/charity` | Donations |

---

*— End of Document —*
