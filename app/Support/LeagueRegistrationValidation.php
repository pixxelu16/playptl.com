<?php

namespace App\Support;

use App\Helpers\LeagueMenuHelper;
use App\Models\Category;
use App\Models\League;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class LeagueRegistrationValidation
{
    /**
     * Validate full registration payload for singles / doubles tournament registration.
     *
     * @param Request $request
     * @param bool $requirePaymentIntent
     * @return array
     * @throws ValidationException
     */
    public static function validate(Request $request, bool $requirePaymentIntent = false): array
    {
        $isFreeReg = (SiteSetting::getValue('enable_free_registration', '0') === '1');

        $baseRules = [
            'registration_tab' => ['required', 'string', 'in:singles,doubles'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->symbols()],
            'category' => ['required', 'integer', 'exists:categories,id'],
        ];

        if ($requirePaymentIntent && ! $isFreeReg) {
            $baseRules['payment_intent_id'] = ['required', 'string', 'max:255'];
        } else {
            $baseRules['payment_intent_id'] = ['nullable', 'string', 'max:255'];
        }

        $validator = Validator::make($request->all(), $baseRules);
        $base = $validator->validate();

        $tab = (string) $base['registration_tab'];
        $categoryModel = Category::findOrFail((int) $base['category']);
        $isDoublesCategory = ($categoryModel->name === 'Doubles');
        $isDoublesRegistration = ($tab === 'doubles' || $isDoublesCategory);

        if ($tab === 'singles') {
            $rules = [
                'phone_singles' => ['required', 'string', 'max:32', 'unique:users,phone'],
                'city_singles' => ['required', 'string', 'max:255'],
                'state_singles' => ['required', 'string', 'max:64'],
                'age_group_singles' => ['required', 'string', 'max:32'],
                'skill_singles' => ['required', 'string', 'max:32'],
                'sex_singles' => ['required', 'string', 'max:32'],
                'tournament_singles' => ['required', 'integer', 'exists:leagues,id'],
                'group_card_singles' => ['required', 'integer', 'exists:group_cards,id'],
                'singles_first' => ['nullable'],
                'singles_last' => ['nullable'],
            ];
            if ($isDoublesRegistration) {
                $rules = array_merge($rules, [
                    'd2_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                    'd2_phone' => ['required', 'string', 'max:32', 'unique:users,phone'],
                    'd2_city' => ['required', 'string', 'max:255'],
                    'd2_state' => ['required', 'string', 'max:64'],
                    'd2_age_group' => ['required', 'string', 'max:32'],
                    'd2_skill' => ['required', 'string', 'max:32'],
                    'd2_sex' => ['required', 'string', 'max:32'],
                    'd2_first' => ['nullable'],
                    'd2_last' => ['nullable'],
                ]);
            }
            $specificValidator = Validator::make($request->all(), $rules, [], [
                'phone_singles' => 'phone',
                'city_singles' => 'city',
                'state_singles' => 'state',
                'age_group_singles' => 'age group',
                'skill_singles' => 'skill level',
                'sex_singles' => 'gender',
                'tournament_singles' => 'tournament',
                'group_card_singles' => 'group',
                'd2_email' => 'Player 2 email',
                'd2_phone' => 'Player 2 phone',
            ]);
            $specific = $specificValidator->validate();
        } else {
            $rules = [
                'phone_doubles' => ['required', 'string', 'max:32', 'unique:users,phone'],
                'city_doubles' => ['required', 'string', 'max:255'],
                'state_doubles' => ['required', 'string', 'max:64'],
                'age_group_doubles' => ['required', 'string', 'max:32'],
                'skill_doubles' => ['required', 'string', 'max:32'],
                'sex_doubles' => ['required', 'string', 'max:32'],
                'tournament_doubles' => ['required', 'integer', 'exists:leagues,id'],
                'group_card_doubles' => ['required', 'integer', 'exists:group_cards,id'],
                'team_name' => ['nullable', 'string', 'max:255'],
                'd2_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'd2_phone' => ['required', 'string', 'max:32', 'unique:users,phone'],
                'd2_city' => ['required', 'string', 'max:255'],
                'd2_state' => ['required', 'string', 'max:64'],
                'd2_age_group' => ['required', 'string', 'max:32'],
                'd2_skill' => ['required', 'string', 'max:32'],
                'd2_sex' => ['required', 'string', 'max:32'],
                'd1_first' => ['nullable'],
                'd1_last' => ['nullable'],
                'd2_first' => ['nullable'],
                'd2_last' => ['nullable'],
            ];
            $specificValidator = Validator::make($request->all(), $rules, [], [
                'phone_doubles' => 'phone',
                'city_doubles' => 'city',
                'state_doubles' => 'state',
                'age_group_doubles' => 'age group',
                'skill_doubles' => 'skill level',
                'sex_doubles' => 'gender',
                'tournament_doubles' => 'tournament',
                'group_card_doubles' => 'group',
                'd2_email' => 'Player 2 email',
                'd2_phone' => 'Player 2 phone',
            ]);
            $specific = $specificValidator->validate();
        }

        if ($isDoublesRegistration) {
            $email1 = strtolower((string) $base['email']);
            $email2 = strtolower((string) $specific['d2_email']);

            if ($email2 === $email1) {
                throw ValidationException::withMessages([
                    'd2_email' => 'Second player email must be different from your email.',
                ]);
            }
        }

        $leagueId = (int) ($tab === 'singles' ? $specific['tournament_singles'] : $specific['tournament_doubles']);
        $league = League::query()->findOrFail($leagueId);

        if (! LeagueMenuHelper::acceptsRegistration($league)) {
            throw ValidationException::withMessages([
                ($tab === 'singles' ? 'tournament_singles' : 'tournament_doubles') => 'Registration is not open for this tournament.',
            ]);
        }

        $skillLevel = (string) ($tab === 'singles' ? $specific['skill_singles'] : $specific['skill_doubles']);
        $assignmentSkill = $skillLevel;

        if ($isDoublesRegistration) {
            $skillOne = (string) ($tab === 'singles' ? $specific['skill_singles'] : $specific['skill_doubles']);
            $skillTwo = (string) $specific['d2_skill'];
            if ($skillOne === 'not-sure' || $skillTwo === 'not-sure') {
                $assignmentSkill = 'not-sure';
            } else {
                $averageSkill = TournamentRegistrationOptions::averageSkillLevels($skillOne, $skillTwo);
                if ($averageSkill === null) {
                    throw ValidationException::withMessages([
                        'd2_skill' => 'Both players need a valid skill level for group assignment.',
                    ]);
                }
                $assignmentSkill = $averageSkill;
            }
        }

        $registrationClosed = LeagueRegistrationGate::closedReasonForSelection(
            $league,
            $tab,
            $assignmentSkill,
        );
        if ($registrationClosed !== null) {
            throw ValidationException::withMessages([
                ($tab === 'singles' ? 'tournament_singles' : 'tournament_doubles') => $registrationClosed,
            ]);
        }

        return array_merge($base, $specific, [
            'league' => $league,
            'is_doubles_registration' => $isDoublesRegistration,
            'assignment_skill' => $assignmentSkill,
        ]);
    }
}
