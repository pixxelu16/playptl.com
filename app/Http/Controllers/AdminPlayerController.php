<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Helpers\LeagueMenuHelper;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\User;
use App\Support\AdminPlayerLeagueRegistrationService;
use App\Support\LeagueRegistrationFlow;
use App\Support\LeagueSeasonWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\PlayerAccountCreatedMail;

class AdminPlayerController extends Controller
{
    public function index(Request $request): View
    {
        $leagues = LeagueMenuHelper::registrationLeagues(latestFirst: true);

        $leagueIdParam = (string) $request->query('league_id', '');
        $leagueIdInt = $leagueIdParam !== '' && ctype_digit($leagueIdParam) ? (int) $leagueIdParam : null;

        $skillSort = strtolower((string) $request->query('skill_sort', 'asc'));
        if (! in_array($skillSort, ['asc', 'desc'], true)) {
            $skillSort = 'asc';
        }

        $search = trim((string) $request->query('search', ''));

        $skillFieldList = implode("', '", AdminPlayerLeagueRegistrationService::skillLevelValues());

        $playersQuery = User::query()
            ->where('users.role', UserRole::Player);

        if ($search !== '') {
            $playersQuery->where(function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->where('users.name', 'like', $like)
                    ->orWhere('users.first_name', 'like', $like)
                    ->orWhere('users.last_name', 'like', $like)
                    ->orWhere('users.email', 'like', $like)
                    ->orWhere('users.phone', 'like', $like)
                    ->orWhere('users.city', 'like', $like)
                    ->orWhere('users.state', 'like', $like);
            });
        }

        if ($leagueIdInt !== null) {
            $playersQuery
                ->whereHas('leagueRegistrations', fn ($query) => $query->where('league_id', $leagueIdInt))
                ->joinSub(
                    LeagueRegistration::query()
                        ->selectRaw('user_id, MAX(id) as latest_reg_id')
                        ->where('league_id', $leagueIdInt)
                        ->groupBy('user_id'),
                    'player_league_regs',
                    'users.id',
                    '=',
                    'player_league_regs.user_id'
                )
                ->join('league_registrations as tournament_reg', 'tournament_reg.id', '=', 'player_league_regs.latest_reg_id')
                ->select('users.*')
                ->orderByRaw(
                    "FIELD(COALESCE(NULLIF(users.skill_level, ''), NULLIF(tournament_reg.skill_level, ''), 'not-sure'), '{$skillFieldList}') ".($skillSort === 'desc' ? 'DESC' : 'ASC')
                )
                ->orderBy('users.id');
        } else {
            $playersQuery
                ->orderByRaw(
                    "FIELD(COALESCE(NULLIF(users.skill_level, ''), 'not-sure'), '{$skillFieldList}') ".($skillSort === 'desc' ? 'DESC' : 'ASC')
                )
                ->orderByDesc('users.id');
        }

        $players = $playersQuery
            ->with(['leagueRegistrations' => fn ($query) => $query
                ->when($leagueIdInt !== null, fn ($inner) => $inner->where('league_id', $leagueIdInt))
                ->orderByDesc('id')])
            ->paginate(10)
            ->withQueryString();

        $playerActiveTournaments = [];
        $playerIds = $players->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($playerIds !== []) {
            $registrations = LeagueRegistration::query()
                ->whereIn('user_id', $playerIds)
                ->whereHas('league', fn ($query) => $query
                    ->whereNull('finished_at')
                    ->whereIn('stats', LeagueMenuHelper::REGISTRATION_STATUSES))
                ->with([
                    'league:id,name,start_date,end_date,stats,finished_at',
                    'groupCard:id,name',
                    'group:id,name',
                ])
                ->orderByDesc('id')
                ->get();

            foreach ($registrations as $registration) {
                $league = $registration->league;
                if (! $league instanceof League || ! LeagueSeasonWindow::isListedForAdminPlayers($league)) {
                    continue;
                }

                $userId = (int) $registration->user_id;
                $leagueId = (int) $league->id;

                if (! isset($playerActiveTournaments[$userId][$leagueId])) {
                    $playerActiveTournaments[$userId][$leagueId] = [
                        'tournament' => trim((string) $league->name) ?: '—',
                        'window' => LeagueSeasonWindow::label($league),
                        'status_label' => LeagueSeasonWindow::adminPlayerListStatusLabel($league),
                        'registrations' => [],
                    ];
                }

                $playerActiveTournaments[$userId][$leagueId]['registrations'][] = [
                    'group' => trim((string) ($registration->groupCard?->name ?? '')) ?: '—',
                    'subgroup' => trim((string) ($registration->group?->name ?? '')) ?: 'Unassigned',
                    'format' => ucfirst((string) ($registration->registration_type ?? 'singles')),
                ];
            }

            foreach ($playerActiveTournaments as $userId => $byLeague) {
                $playerActiveTournaments[$userId] = array_values($byLeague);
            }
        }

        return view('admin.players.index', [
            'players' => $players,
            'leagues' => $leagues,
            'leagueId' => $leagueIdInt,
            'skillSort' => $skillSort,
            'search' => $search,
            'playerActiveTournaments' => $playerActiveTournaments,
        ]);
    }

    public function create(Request $request): View
    {
        $tab = (string) $request->query('tab', 'singles');
        if (! in_array($tab, ['singles', 'doubles'], true)) {
            $tab = 'singles';
        }

        return view('admin.players.create', [
            'tab' => $tab,
            'player' => new User([
                'role' => UserRole::Player,
                'registration_type' => $tab,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $tab = (string) $request->query('tab', 'singles');
        if (! in_array($tab, ['singles', 'doubles'], true)) {
            $tab = 'singles';
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'status' => ['required', Rule::in(['active', 'pending', 'suspend'])],
            'skill_level' => ['nullable', 'string', 'max:32', Rule::in(AdminPlayerLeagueRegistrationService::skillLevelValues())],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $plainPassword = substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(12))), 0, 12);

        $skillLevel = isset($validated['skill_level']) && $validated['skill_level'] !== ''
            ? $validated['skill_level']
            : null;
        unset($validated['skill_level'], $validated['avatar']);

        $player = User::create([
            ...$validated,
            'skill_level' => $skillLevel,
            'role' => UserRole::Player,
            'registration_type' => $tab,
            'password' => $plainPassword,
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'avatar-'.$player->id.'-'.bin2hex(random_bytes(6)).'.'.$ext;
            $dir = public_path('upload/user-avatar');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $file->move($dir, $filename);
            $player->update(['avatar_path' => 'upload/user-avatar/'.$filename]);
        }

        try {
            Mail::to($player->email)->send(new PlayerAccountCreatedMail(
                userName: $player->name,
                email: $player->email,
                password: $plainPassword,
                loginUrl: route('login'),
            ));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.players.index', ['tab' => $tab])
                ->with('status', 'Player created, but email could not be sent. Please check mail settings.');
        }

        return redirect()
            ->route('admin.players.index', ['tab' => $tab])
            ->with('status', 'Player created and login details emailed successfully.');
    }

    public function edit(Request $request, User $player): View
    {
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $registration = $this->registrationForPlayerEdit($player, $request);

        return view('admin.players.edit', [
            'player' => $player,
            'indexQuery' => $this->playerIndexQuery($request),
            'ageBrackets' => AdminPlayerLeagueRegistrationService::ageBrackets(),
            'currentAgeGroupKey' => old('age_group_key', $registration?->age_group_key),
            'canEditAgeGroup' => $registration instanceof LeagueRegistration,
        ]);
    }

    public function update(Request $request, User $player)
    {
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $tab = in_array($player->registration_type, ['singles', 'doubles'], true)
            ? (string) $player->registration_type
            : 'singles';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'status' => ['required', Rule::in(['active', 'pending', 'suspend'])],
            'skill_level' => ['nullable', 'string', 'max:32', Rule::in(AdminPlayerLeagueRegistrationService::skillLevelValues())],
            'age_group_key' => ['nullable', 'string', 'max:32', Rule::in(AdminPlayerLeagueRegistrationService::ageBrackets()->keys()->all())],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['role'] = UserRole::Player;
        unset($validated['email']);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'avatar-'.$player->id.'-'.bin2hex(random_bytes(6)).'.'.$ext;
            $dir = public_path('upload/user-avatar');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $file->move($dir, $filename);

            $newPath = 'upload/user-avatar/'.$filename;
            $oldPath = (string) ($player->avatar_path ?? '');
            if ($oldPath !== '' && $oldPath !== 'upload/user-avatar/default-user-pic.png') {
                $oldFull = public_path($oldPath);
                if (File::exists($oldFull)) {
                    File::delete($oldFull);
                }
            }
            $validated['avatar_path'] = $newPath;
        }

        $skillLevel = isset($validated['skill_level']) && $validated['skill_level'] !== ''
            ? $validated['skill_level']
            : null;
        $ageGroupKey = isset($validated['age_group_key']) && $validated['age_group_key'] !== ''
            ? $validated['age_group_key']
            : null;
        unset($validated['skill_level'], $validated['age_group_key'], $validated['avatar']);

        $player->update([
            ...$validated,
            'skill_level' => $skillLevel,
        ]);

        $registration = $this->registrationForPlayerEdit($player, $request);
        if ($registration instanceof LeagueRegistration && $ageGroupKey !== null) {
            $groupCard = $registration->group_card_id
                ? GroupCard::query()->find($registration->group_card_id)
                : null;

            $groupId = $groupCard instanceof GroupCard
                ? LeagueRegistrationFlow::resolveGroupId((int) $registration->league_id, $groupCard, $tab, $ageGroupKey)
                : $registration->group_id;

            $registration->update([
                'age_group_key' => $ageGroupKey,
                'group_id' => $groupId,
            ]);
        }

        return redirect()
            ->route('admin.players.index', $this->playerIndexQuery($request))
            ->with('status', 'Player updated successfully.');
    }

    public function destroy(Request $request, User $player)
    {
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $player->delete();

        return redirect()
            ->route('admin.players.index', $this->playerIndexQuery($request))
            ->with('status', 'Player deleted successfully.');
    }

    private function registrationForPlayerEdit(User $player, Request $request): ?LeagueRegistration
    {
        $tab = in_array($player->registration_type, ['singles', 'doubles'], true)
            ? (string) $player->registration_type
            : 'singles';

        $leagueIdParam = (string) $request->query('league_id', '');
        $leagueIdInt = $leagueIdParam !== '' && ctype_digit($leagueIdParam) ? (int) $leagueIdParam : null;

        $query = $player->leagueRegistrations()
            ->where('registration_type', $tab)
            ->orderByDesc('id');

        if ($leagueIdInt !== null) {
            $query->where('league_id', $leagueIdInt);
        }

        $registration = $query->first();

        return $registration instanceof LeagueRegistration ? $registration : null;
    }

    /**
     * @return array<string, int|string>
     */
    private function playerIndexQuery(Request $request): array
    {
        $query = [];

        $leagueId = (string) $request->query('league_id', '');
        if ($leagueId !== '' && ctype_digit($leagueId)) {
            $query['league_id'] = (int) $leagueId;
        }

        $skillSort = strtolower((string) $request->query('skill_sort', ''));
        if (in_array($skillSort, ['asc', 'desc'], true)) {
            $query['skill_sort'] = $skillSort;
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query['search'] = $search;
        }

        $page = (string) $request->query('page', '');
        if ($page !== '' && ctype_digit($page) && (int) $page > 1) {
            $query['page'] = (int) $page;
        }

        return $query;
    }
}
