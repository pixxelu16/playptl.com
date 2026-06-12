# PLAYPTL — Project Flow Document

**Platform:** playptl.com  
**Document Type:** System Flow & User Guide  
**Version:** 1.0  
**Date:** June 2026  

---

## 1. Introduction

PlayPTL ek tennis league / tournament management platform hai. Is system se admin poora tournament chalata hai — players register karte hain, group matches schedule hoti hain, points banate hain, aur playoffs tak poora season complete hota hai.

Is document mein bataya gaya hai:

- System ka overall flow kya hai
- Admin kya-kya karta hai aur kis order mein
- Player kya-kya karta hai
- Tournament ke har phase mein kya hota hai
- Kuch important rules jo system follow karta hai

---

## 2. Platform Overview

### 2.1 Teen tarah ke users

| User | Kaun hai | Kya karta hai |
|------|----------|---------------|
| **Admin** | Tournament organizer / PTL staff | Tournament setup, players assign, matches schedule, results, playoffs |
| **Player** | Registered tennis player | Profile manage, tournament join, matches khelna, score enter karna |
| **Visitor (Public)** | Bina login ke | League standings, schedule aur charity page dekh sakta hai |

### 2.2 Login ke baad kahan jaate hain

- **Admin** → Admin Panel (`/admin`)
- **Player** → My Profile (`/player/my-profile`)
- **Visitor** → Public pages (`/league/...`, `/charity`)

---

## 3. System Structure — Samajhne ke liye zaroori

Poora system ek **tree structure** mein chalta hai. Upar se neeche samjho:

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
    └── (aur divisions ho sakti hain — 4.0 Singles, Doubles, etc.)
```

### Simple example

**Tournament:** PTL Spring 2026  
**Division:** Voyagers Singles (skill 3.5)  
**Subgroups:** Group A, Group B, Group C  
**Players:** Har subgroup mein 5–8 players  
**Matches:** Har subgroup ka apna round-robin — Week 1, Week 2, …  
**Playoffs:** Group stage ke baad top players knockout mein  

---

## 4. Admin Flow — Poora Tournament Kaise Chalta Hai

### PHASE 1: Pehli Baar Setup (jab naya season shuru ho)

Yeh steps ek baar ya jab zarurat ho karti hain:

#### Step 1 — Subgroups banana
Admin panel → **Groups**  
Yahan "Group A", "Group B", "Group C" jaisi subgroup names banate hain. Yeh templates hain jo baad mein divisions se link hote hain.

#### Step 2 — Divisions (Group Cards) banana
Admin panel → **Subgroups (Group Cards)**  
Har division ek skill level + format define karti hai:

- **Name:** Voyagers Singles, Champions Doubles, etc.
- **Format:** Singles ya Doubles
- **Skill tier:** 3.0, 3.5, 4.0, etc.
- **Playoff format:** Top 4 seed Quarter mein, baaki Round of 16, etc.
- **Subgroups attach:** Group A, B, C link karo

#### Step 3 — Players dekhna / banana
Admin panel → **Players**  
- Saare registered players ki list
- Singles / Doubles tab
- Search, tournament filter
- Naya player banana ya edit karna

---

### PHASE 2: Naya Tournament Create Karna

#### Step 4 — Tournament banayo
Admin panel → **Tournaments** → Create

| Field | Matlab |
|-------|--------|
| Tournament name | PTL Spring 2026 |
| Start date | Poori season ki shuruat |
| End date | Poori season ki last date |
| Status | Active / Upcoming / Completed |
| Entry fee | Singles aur Doubles alag fee (online payment) |
| Divisions attach | Kaun se group cards is tournament mein hain |

**Important:** Tournament ki start/end date poori season ki boundary hai. Group matches aur playoffs dono is window ke andar hone chahiye (ya tournament extend karna padega).

#### Step 5 — Tournament Management kholo
Admin panel → **Tournaments** → Manage  
Yahan har attached division dikhti hai. Har division ke liye alag se kaam karna hota hai.

---

### PHASE 3: Har Division Ke Liye (Repeat Steps 6–12)

Ek tournament mein multiple divisions ho sakti hain (3.5 Singles, 4.0 Singles, Doubles, etc.). Har division ke liye neeche wale steps repeat karo.

#### Step 6 — Players assign karo
**Page:** Assign Players  

- Sirf woh players dikhte hain jo is division mein abhi nahi hain
- Admin **Assign** button dabata hai
- System automatically player ko **sabse kam players wale subgroup** mein daal deta hai (Group A, B, ya C)
- Player ka skill level division ke skill tier se match hona chahiye

**Rule:** Ek player ek tournament mein **ek hi singles division** ya **ek hi doubles division** mein ho sakta hai.

#### Step 7 — Subgroups aur roster check karo
**Page:** Subgroups & Players  

- Dekho kaun player kis subgroup mein hai
- Zarurat ho to player ko dusre subgroup mein move karo
- Naya subgroup add kar sakte ho
- Doubles mein team dono players ek saath move hote hain

#### Step 8 — Group matches schedule karo
**Page:** Matches  

**Pehle yeh ready hona chahiye:**
- Tournament start/end date set ho
- Har subgroup mein kam se kam 2 players hon
- Sab players subgroup mein assign hon

**Schedule kaise karte hain:**

1. **Group start date** select karo (tournament window ke andar)
2. **Schedule matches** button dabao
3. System automatically **saari subgroups (A, B, C)** ke liye round-robin matches banata hai
4. Matches **Week 1, Week 2, Week 3…** mein divide hoti hain
5. Baad mein **Group end date** set karo (last match ki date ya uske baad)

**Match calendar rules:**
- Matches sirf **Monday se Saturday** tak schedule hoti hain
- **Sunday par koi match nahi**
- Agar players odd number hain (jaise 5) to ek player ko har round mein "bye" milta hai — us week uski koi match nahi hoti
- Ek week mein har player ki maximum ek match hoti hai

**Agar tournament date chhoti hai:**
Agar matches tournament end date ke baad ja rahi hain (jaise matches July 25 tak hain lekin tournament July 4 ko khatam) to system warning dikhata hai — pehle **Edit Tournament** se end date badhani padegi.

**Cancel matches:**
Agar poora schedule galat ho gaya to **Cancel this group matches** se saari subgroups ki matches delete ho jati hain aur players ko email jati hai. Playoffs shuru hone ke baad yeh option nahi milta.

#### Step 9 — Match results enter karo
Results do jagah se aa sakte hain:

- **Admin** Matches page se score enter kare
- **Player** apni My Matches se photo upload karke score enter kare

Jab results aate hain tab standings update hoti hain.

#### Step 10 — Standings / Points check karo
**Page:** Points  

- Poori division ki ranking dekho
- Ya ek subgroup filter karke dekho
- Points system:
  - Straight sets jeet → Winner 14 points, Loser max 8
  - 3 sets jeet → Winner 12 points, Loser max 8
  - Walkover → Winner 10, Loser 0

#### Step 11 — Qualifier (Playoff paths)
**Page:** Qualifier  

**Kab khulta hai:** Jab kam se kam ek group match ka result aa chuka ho.

System standings ke hisaab se automatically batata hai kaun player kahan jayega:

| Path | Matlab |
|------|--------|
| Quarter | Seed directly quarterfinals mein |
| Pre-Q / Round of 16 | Pehle Round of 16 khelega |
| Pre-Pre-Q | Sabse pehle Pre-Pre-Quarter round |
| Eliminated | Playoffs se bahar |

Admin paths review kar sakta hai aur notes add kar sakta hai. Save karne par playoff bracket automatically generate hota hai.

#### Step 12 — Playoffs
**Page:** Playoffs  

**Pehle check karo:**
- Qualifier paths set hon
- Tournament end date itni ho ki playoffs fit ho jayein
- Group matches khatam ho chuki hon

**Playoffs schedule:**

1. **Playoff start date** — group matches khatam hone ke **baad** ki date (calendar se khud pick karo)
2. **Playoff end date** — final match ki last date
3. **Schedule matches** dabao → playoffs officially start, group scheduling band ho jati hai

**Playoff rounds (typical):**
Pre-Pre-Q → Round of 16 → Quarterfinals → Semifinals → Final

Admin har round ke results enter karta hai aur **Advance winners** se agle round mein winners move karte hain.

**Playoffs band karna:**
Jab sab results final hon → **Playoff close** → scores lock ho jate hain.

#### Step 13 — Tournament finish
League Management se tournament **Finish** karo → season officially complete.

---

## 5. Player Flow — Player Ki Nazar Se

### 5.1 Naya player — Registration

**Page:** `/register` (website par public)

1. **Singles** ya **Doubles** choose karo
2. Apni details bharo — naam, email, password, phone, city, skill level, age group
3. **Tournament** select karo
4. System tumhare skill level ke hisaab se division choose karta hai
5. **Online payment** (Stripe) karo
6. Account ban jata hai — login karo

**Doubles registration:**
- Partner ki details bhi bharni hoti hain
- Dono ka skill average se division decide hoti hai
- Partner ko email jati hai login ke liye

### 5.2 Login ke baad — My Profile

Player ko **My Profile** page milta hai jisme yeh sections hain:

| Section | Kya karta hai |
|---------|---------------|
| Personal Information | Naam, phone, photo, **skill level** set karna |
| Choose League | Aur tournaments mein register hona (payment ke saath) |
| Password & Security | Password change |
| My Matches | Apni scheduled matches dekhna aur manage karna |
| Upload Image | Match ki photo upload karna |

### 5.3 Dusre tournament mein join karna

**Choose League** section se player aur active tournaments join kar sakta hai.

**Zaroori:** Pehle Personal Information mein skill level set hona chahiye.

### 5.4 Matches kaise dikhti hain

Player ko matches tab dikhti hain jab **admin ne schedule kar diya ho**.

**My Matches** page par:
- Upcoming matches calendar style mein
- Opponent ka naam
- Date, time, venue
- Playoff matches bhi yahan dikhti hain (agar qualify hue ho)

### 5.5 Match khelne ka flow (Player side)

```
Admin schedule karta hai
        ↓
Player ko match dikhti hai (My Matches)
        ↓
Player venue / date / time set ya change karta hai
        ↓
Match ke baad PHOTO UPLOAD karna zaroori hai
        ↓
Score enter karo (sets ya walkover)
        ↓
Standings update hoti hain (public page par)
```

**Important rules player ke liye:**
- Bina photo upload ke score enter nahi ho sakta
- Score enter karne tak dusre players ko score nahi dikhta (privacy)
- Ek din multiple tournaments ki matches ho sakti hain

### 5.6 Standings kahan dekhein

Player ke paas alag standings page nahi hai. Public league page par dekho:

**URL:** `/league/{tournament-name}/{division-name}`

Yahan tabs hain — Standings, Schedule, Playoffs bracket.

### 5.7 Playoffs mein player ka role

Player khud playoffs mein register nahi karta. Agar group stage mein qualify hua to:
- Playoff matches **My Matches** mein dikhengi
- Wahi flow — photo upload → score enter

---

## 6. Registration Rules

| Rule | Detail |
|------|--------|
| Skill → Division | Player ka skill level decide karta hai kaun si division milegi |
| Doubles skill | Dono players ke skill ka average |
| Ek format, ek division | Ek tournament mein ek singles YA ek doubles division |
| Multiple tournaments | Player alag-alag tournaments join kar sakta hai |
| Registration band | Jab us division ki matches schedule ho jayein |
| Playoffs ke baad | Poori tournament mein nayi registration band |

---

## 7. Match Scheduling Rules (Summary)

| Rule | Detail |
|------|--------|
| Play days | Monday – Saturday |
| Off day | Sunday — koi match nahi |
| Weekly structure | Har week = ek round, har player max 1 match |
| Odd players | Bye week — us round mein match nahi |
| Auto schedule | Admin ek button se saari subgroups schedule karta hai |
| Reschedule | Dates change karne par players ko email |
| Same dates dubara schedule | Email nahi jati (duplicate) |
| Cancel all | Poora division schedule delete + email |

---

## 8. Playoffs Rules (Summary)

| Rule | Detail |
|------|--------|
| Kab start | Group stage ke kam se kam 1 result ke baad qualifier unlock |
| Paths | Standings rank se auto — admin adjust kar sakta hai |
| Playoff start date | Group matches khatam hone ke **baad** |
| Tournament date chhoti ho | Pehle tournament extend karo, warna warning |
| Playoffs start hone par | Group match scheduling band |
| Playoffs close | Results lock — koi change nahi |

---

## 9. Payment Flow

### Player payment
- Registration ya Choose League par **Stripe** se online payment
- Payment successful → player tournament mein registered
- Payment history admin dekh sakta hai

### Admin assign (bina payment)
- Admin manually player assign kare to payment skip hota hai
- Player phir bhi tournament mein registered hota hai

---

## 10. Charity

### Public
- `/charity` — charity page
- Paise donate (Stripe) ya material/volunteer form

### Admin
- Charity causes manage karna
- Donations list dekhna
- Donors ko bulk email bhejna

---

## 11. Email Notifications

| Event | Kisko jati hai |
|-------|----------------|
| Naya account | Player ko login details |
| Registration confirm | Player ko confirmation |
| Doubles partner add | Partner ko setup email |
| Match scheduled | Home + Away players |
| Match rescheduled (date change) | Affected players |
| All matches cancelled | Saare division players |
| Playoff match scheduled | Qualified players |

**Note:** Match emails queue se jati hain — server par queue worker chalna chahiye.

---

## 12. Complete Season — Ek Example

**Tournament: PTL Spring 2026**

| Week | Admin | Player |
|------|-------|--------|
| Week 0 | Tournament create, divisions attach, players assign | Players register online |
| Week 1 | Group start date set, Schedule matches | Matches dikhti hain My Matches mein |
| Week 2–8 | Monitor results, Points check | Har week match khelo, photo upload, score enter |
| Week 9 | Group end date set, Qualifier review | — |
| Week 10 | Playoff dates set, Schedule playoffs | Playoff matches dikhti hain |
| Week 11–12 | Advance winners, Final | Playoff matches khelo |
| Week 13 | Playoff close, Tournament finish | — |

---

## 13. Common Problems & Solutions

| Problem | Kya karo |
|---------|----------|
| Matches schedule nahi ho rahi | Check: tournament dates set? 2+ players per subgroup? Group start date? |
| Group end date save nahi ho rahi | Tournament end date chhoti hai — Edit Tournament se extend karo |
| Playoffs schedule nahi ho rahe | Tournament extend karo, group matches complete karo, qualifier paths save karo |
| Player ko match nahi dikh rahi | Admin ne schedule kiya? Player sahi subgroup mein hai? |
| Score enter nahi ho raha | Pehle match photo upload karo |
| Email nahi aa rahi | Server par email queue worker check karo |
| Registration band hai | Us division ki matches already schedule ho chuki hain |

---

## 14. Admin Panel — Page Reference

| Menu | Kaam |
|------|------|
| Dashboard | Overview counts |
| Tournaments | Create / edit / manage tournaments |
| Groups | Subgroup templates |
| Subgroups | Divisions (skill + format) |
| Players | All players list, search, edit |
| Payment History | Online payments dekho |
| Charity Causes | Campaigns manage |
| Charity Donations | Donations + email |
| Announcements | Site announcements |

### Tournament ke andar (per division)

| Tab | Kaam |
|-----|------|
| Subgroups & Players | Roster manage |
| Assign Players | Naye players add |
| All Players | Division roster view |
| Matches | Schedule + results |
| Points | Standings |
| Qualifier | Playoff paths |
| Playoffs | Bracket + results |

---

## 15. Player Portal — Page Reference

| Section | Kaam |
|---------|------|
| Personal Information | Profile + skill level |
| Choose League | Aur tournaments join |
| Password & Security | Password change |
| My Matches | Schedule, venue, scores |
| Upload Image | Match photos |

### Public (bina login)

| Page | Kaam |
|------|------|
| `/league/{name}` | Tournament overview |
| `/league/{name}/{division}` | Standings + schedule + playoffs |
| `/charity` | Donations |

---

*— End of Document —*
