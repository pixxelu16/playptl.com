# PTL League — Match Scheduling & Player Flow

> **Poori system guide (admin + player):** [`PTL-SYSTEM-GUIDE.md`](./PTL-SYSTEM-GUIDE.md)

Yeh document explain karta hai ke system mein **tournament → group (division) → subgroups → players → matches → playoffs** ka pura flow kaise chalta hai.

---

## 1. Structure (Hierarchy)

| Level | Admin UI name | Database / code |
|--------|----------------|-----------------|
| Tournament | **Tournament / League** | `leagues` |
| Skill + format bucket | **Group** (e.g. Voyagers Singles) | `group_cards` (tag: singles/doubles, `skill_level_match`) |
| Letter pool inside group | **Subgroup** (Group A, B, C, D) | `groups` linked to `group_card` |
| Player membership | Roster | `league_registrations` (`group_card_id`, `group_id`) |
| Group-stage match | Match card | `group_matches` |
| Playoff match | Playoff bracket | `playoff_matches` + `playoff_qualifiers` |

**Important:** Ek player **ek league** mein **alag-alag tournaments** join kar sakta hai (Choose League se), lekin **same format (singles ya doubles)** ke liye **sirf ek group card** (ek skill division).

---

## 2. Player registration — Singles vs Doubles

### 2.1 Singles

1. Player profile → **Choose League** → tab **Singles**.
2. Player ka **skill level** profile se fixed hota hai (Personal Information).
3. System skill se **group card** choose karta hai:
   - Har active group card ka `skill_level_match` hota hai (e.g. 3.5, 4.0).
   - Player skill **≤ tier skill** wala pehla tier; warna sab se highest tier.
4. Registration record banता hai: `league_id`, `group_card_id`, `registration_type = singles`.
5. **Subgroup (Group A/B/C/D)** automatically assign — Section 3.

### 2.2 Doubles

1. Tab **Doubles** → partner select (registered player).
2. Dono ka skill validate hota hai.
3. **Team skill** = dono skills ka **average** (e.g. 3.5 + 4.0 → 3.75).
4. Us **average** se group card resolve hota hai (same tier rules as singles).
5. Dono players ke liye `league_registrations` rows:
   - Same `team_key` (UUID)
   - Same `group_card_id` aur **same `group_id`** (subgroup)
   - `registration_type = doubles`

### 2.3 Admin assign

**Assign players** page se admin manually add kar sakta hai — same rules:

- Singles/doubles group card ke tag se format decide.
- `pickGroupId()` / `LeagueRegistrationFlow::resolveGroupId()` se **least-filled subgroup** auto pick.

### 2.4 Registration band kab hoti hai

- Division mein **koi bhi scheduled match** aa gayi → us skill group ke liye nayi registration band.
- Playoffs start → poori league registration band.
- Qualifier paths save / playoff bracket exist → us division par group scheduling lock (Section 8).

---

## 3. Subgroups (Group A, B, C, D) — automatic balance

**Rule:** Naya player jis subgroup mein sab se **kam roster** hai, wahan assign hota hai.

**Singles:** `league_registrations` count per `group_id`.

**Doubles:** **team slots** count (`LeagueRegistrationRoster::countSlots`) — incomplete teams bhi slot logic mein.

**Code:** `LeagueRegistrationFlow::resolveGroupId()` aur admin `pickGroupId()` — same algorithm.

**Manual change:** Admin **Subgroups & players** se player ko dusre subgroup mein move kar sakta hai; doubles team dono rows ek saath move.

**Subgroup create:** Admin **Groups** under division se Group A/B/C/D banata hai aur group card se link (pivot `group_group_card`).

---

## 4. Group-stage auto scheduling (Round-robin)

### 4.1 Admin steps

1. Tournament par **start / end date** set (Edit Tournament).
2. Division ke liye subgroups + players ready.
3. **Matches** page → **Group start date** (tournament window ke andar).
4. **Schedule matches** — **saari subgroups** (A, B, C, D) ek saath `SubgroupRoundRobinScheduler::syncDivision()`.
5. Matches banne ke baad **Group end date** field dikhti hai; **Reschedule matches** se dates update.

### 4.2 Har subgroup ke liye conditions

| Condition | Result |
|-----------|--------|
| Kam se kam **2 players** (ya 2 complete doubles teams) | Round-robin generate |
| 1 ya 0 player | Skip — message: *need at least 2 players in roster* |
| Division start date missing | Schedule nahi |
| Playoffs / qualifier lock | Schedule nahi |

### 4.3 Round-robin logic

- **Circle method** — har “week” (round) mein har player **ek match** (ya bye).
- **Odd players:** virtual **bye** slot; jis round mein bye ho, us player ki **koi match row nahi** banti — woh week **off**.
- **Doubles:** complete teams hi participants; team vs team matches.
- Duplicate pairing skip (`matchAlreadyExists`).
- Auto matches flag: `auto_scheduled = true`.

### 4.4 Kitni “weeks” / rounds

```
participants = N
if N odd → scheduling slots = N+1 (bye ke liye)
play weeks = slots - 1   →  N=5 → 5 weeks, N=6 → 5 weeks? 
Actually: slots after bye padding = even, rounds = slots - 1
N=5 → slots 6 → 5 rounds
N=6 → 6 rounds
```

Har round = UI par **WEEK 1, WEEK 2, …** (`round_number`).

---

## 5. Calendar — kaun se din match hai, kaun se din nahi

### 5.1 Play days

- Matches **Monday – Saturday** par spread.
- **Sunday par kabhi match schedule nahi** (play day nahi).
- Har play week ka **deadline Sunday** (week end) — matches us Sunday se pehle Mon–Sat mein.

### 5.2 Group start date

- Pehli week: group start se pehle ka din use nahi.
- Agar start **Sunday** ho → pehli play week **Monday se** shuru, deadline **agla Sunday**.

### 5.3 Ek week mein multiple matches

- Agar us round mein 2+ pairings hain → dates **Mon–Sat** par evenly spread (beech ke din).
- 1 pairing → week ke beech wala din.

### 5.4 “Miss” / khaali din — normal scenarios

| Scenario | Matlab |
|----------|--------|
| **Sunday** | Koi match nahi — design |
| **Bye week** (odd count) | Us player ki us round mein **koi match card nahi** |
| **Dusre players** us din khel rahe | Tumhari match dusre din — same week ke andar |
| **Week ke beech khaali din** | Spread algorithm sirf kuch weekdays use karta hai |
| **Playoffs / gap** | Group end ke baad ya playoffs window alag |

**Note:** System har calendar din par match force **nahi** karta — sirf round-robin weeks + Mon–Sat spread.

### 5.5 Tournament / group window

- Group start/end → `group_card_league` pivot (`DivisionScheduleWindow`).
- Match date tournament start/end ke andar honi chahiye.
- Group end date < kisi scheduled match se pehle nahi.

---

## 6. Reschedule & emails

### 6.1 Pehli schedule

- Har **nayi** match → home/away (± partners) ko email **queue** (`MatchScheduleMailQueue`).

### 6.2 Dubara reschedule

| Case | Email |
|------|--------|
| Sirf button dubara, **dates same** | **Nahi** |
| Start/end badla, **match date change** | Sirf changed matches |
| Subgroup pehle khali tha, ab nayi matches | Nayi matches par email |
| Purani match pehle se DB mein | Dubara create nahi |

### 6.3 Manual admin edit

- Match card se date/time/venue change → notify.
- Score-only update → schedule email nahi.

### 6.4 Queue setup

```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:work
```

Bulk stagger: `MAIL_SCHEDULE_QUEUE_STAGGER_SECONDS` (default 2 sec).

---

## 7. Same day multiple matches / conflicts

- Player **ek din multiple leagues** ke matches khel sakta hai — **allowed**.
- Admin UI par **warning** dikhti hai (`PlayerMatchDayConflict`).
- Save se pehle confirm.

---

## 8. Standings (group stage)

- **Points page** / qualifier ke liye `LeagueStandingsBuilder`.
- Scope: poora division ya **ek subgroup** filter.
- Points: `LeaguePointSystem` (straight sets 14, 3-set 12, loser max 8, walkover 10/0).
- Sort: points for → games % → wins, etc.
- **Kam se kam 1 completed group match** ke baad playoff qualifier UI unlock.

---

## 9. Playoffs — flow

### 9.1 Kab dikhe

1. League mein group matches start.
2. Us division mein **kam se kam 1 match result** (winner/score).
3. Tab **Qualifier** + **Playoffs** admin tabs active.

### 9.2 Qualifier paths

- `GroupPlayoffConfig` — group card par format:
  - Top 4 → Quarter, baaki R16
  - Direct Quarter (top 8)
  - Round of 16 only
  - Pre-Pre-Q + R16, etc.
- Standings rank se default path; admin **Qualifier** page par adjust + save.
- Save ke baad `PlayoffBracketBuilder::rebuild()` → `playoff_matches` rows.

### 9.3 Bracket rounds (typical)

`ppq` → `pq` (R16) → `qf` → `sf` → `f`

Winners advance; admin HOME/AWAY change kar sakta hai (email on change).

### 9.4 Playoff dates

- League par `playoff_start_date` / `playoff_end_date` save hoti hain (Playoffs page se).
- **Playoff start** group matches khatam hone ke **baad** honi chahiye (`group end date` ya latest group match + 1 day).
- Agar group matches tournament end ke baad jaati hain → pehle **Edit Tournament** se end date extend karo; tab tak playoffs schedule block/warning.
- **Schedule playoffs** → `PlayoffMatchScheduler` — Mon–Sat window, final round alag window logic.
- Reschedule: **sirf jinki date change** — group stage jaisa.

### 9.5 Start playoffs (league level)

- Admin **Start playoffs** → `playoffs_started_at` set.
- **Group match dates / auto reschedule lock** (league-wide).
- Group-stage scheduling band.

### 9.6 Division lock (qualifier / bracket)

Agar division ke liye:

- Qualifier paths saved, **ya**
- Playoff matches exist

→ us division par **nayi group round-robin generate / auto reschedule band** (scores ab bhi update ho sakte hain policy ke hisaab se).

---

## 10. End-to-end scenario (example)

**PTL Spring 2026 — Voyagers Singles**

1. Admin tournament dates: Jun 10 – Oct 4.
2. Group card “Voyagers Singles” (skill 4.0) + subgroups A–D.
3. Players register / admin assign → auto A/B/C/D balance.
4. Matches page: start date Jun 10 → **Schedule matches** → 4 subgroups × apna round-robin.
5. Week 1–N cards; Sundays off; odd player ko bye weeks.
6. Players results enter → standings update.
7. Qualifier paths save → bracket generate → playoff dates schedule → **Start playoffs**.
8. Group auto-schedule lock; playoff matches chalte hain.

---

## 11. Charity emails (reference)

- Alag flow: `AdminCharityDonationController::sendEmail`.
- Har send = sab filtered donors ko **naya** queued message (match reschedule rules apply nahi).

---

## 12. Quick troubleshooting

| Problem | Check |
|---------|--------|
| Sirf ek subgroup matches | Dubara **Reschedule** — ab missing subgroups bhi fill honi chahiye; roster ≥2 per subgroup |
| Email nahi | `queue:work` chal raha? `jobs` table? |
| Dubara email spam | Reschedule without date change → no email (expected) |
| Playoffs schedule nahi | Qualifier save? Playoff dates on tournament? |
| Registration band | Matches already scheduled for that division |

---

## 13. Main code files

| Topic | File |
|--------|------|
| Round-robin schedule | `app/Support/SubgroupRoundRobinScheduler.php` |
| Mon–Sat calendar | `app/Support/LeagueWeekCalendar.php` |
| Division dates | `app/Support/DivisionScheduleWindow.php` |
| Subgroup auto-pick | `app/Support/LeagueRegistrationFlow.php` |
| Player register | `app/Http/Controllers/PlayerProfileController.php` |
| Admin matches | `app/Http/Controllers/AdminGroupMatchController.php` |
| Playoff bracket | `app/Support/PlayoffBracketBuilder.php` |
| Playoff schedule | `app/Support/PlayoffMatchScheduler.php` |
| Email queue | `app/Support/MatchScheduleMailQueue.php` |
| Standings | `app/Support/LeagueStandingsBuilder.php` |

---

*Last updated: project implementation as of Spring 2026 scheduling fixes (division-wide sync, hybrid reschedule, queued mail).*
