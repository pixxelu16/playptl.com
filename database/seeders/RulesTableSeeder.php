<?php

namespace Database\Seeders;

use App\Models\RuleFaq;
use App\Models\RuleItem;
use App\Models\RuleSection;
use App\Models\RuleVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Version History
        RuleVersion::query()->delete();
        RuleVersion::create([
            'version_number' => '2.3',
            'last_updated' => 'August 1, 2026',
            'changelog' => "Version 2.3 (August 1, 2026): Clarified Foot Fault enforcement, updated walkover court fee reimbursement terms.\nVersion 2.2 (July 15, 2026): Updated match tiebreak rules and 3rd set tiebreaker format.\nVersion 2.1 (May 10, 2026): Added indoor lighting delay rules and substitute player criteria.",
            'is_current' => true,
        ]);

        // 2. Rule Sections & Items
        RuleSection::query()->delete();
        RuleItem::query()->delete();

        $sectionsData = [
            [
                'title' => 'Points System',
                'icon' => 'fa-trophy',
                'items' => [
                    [
                        'title' => 'Match Victory Points',
                        'content' => 'Players earn 3 points for winning a match in straight sets or via a match tiebreaker.',
                        'is_highlighted' => true,
                        'highlight_type' => 'success',
                    ],
                    [
                        'title' => 'Bonus & Set Points',
                        'content' => '1 point is awarded for each set won during a match, even if the player ultimately loses the match.',
                    ],
                    [
                        'title' => 'Default & Walkover Points',
                        'content' => 'A walkover victory awards 3 points and a 6-0, 6-0 scoreline to the non-defaulting player.',
                    ],
                    [
                        'title' => 'Standings Tiebreakers',
                        'content' => 'Ties in total points are broken by: 1) Head-to-Head record, 2) Set differential, 3) Game winning percentage.',
                    ],
                ],
            ],
            [
                'title' => 'Match Format',
                'icon' => 'fa-baseball',
                'items' => [
                    [
                        'title' => 'Standard Match Structure',
                        'content' => 'All matches are played as Best of 3 sets. The first two sets use standard scoring with a 7-point tiebreak at 6-6.',
                    ],
                    [
                        'title' => 'Third Set Match Tiebreaker',
                        'content' => 'In lieu of a full 3rd set, a 10-point Match Tiebreak (win by 2) is played to decide the winner.',
                        'is_highlighted' => true,
                        'highlight_type' => 'info',
                    ],
                    [
                        'title' => 'Game Scoring',
                        'content' => 'Standard deuce scoring applies unless both players mutually agree on No-Ad scoring prior to match start due to court time constraints.',
                    ],
                ],
            ],
            [
                'title' => 'Indoor Match Rules',
                'icon' => 'fa-building',
                'items' => [
                    [
                        'title' => 'Court Booking Limits',
                        'content' => 'Indoor matches must adhere strictly to reserved court time slots. Warm-up time is limited to a maximum of 10 minutes.',
                    ],
                    [
                        'title' => 'Obstruction & Overhead Hits',
                        'content' => 'If a ball hits the ceiling, roof beams, or lighting fixtures, it is immediately declared OUT against the hitter.',
                        'is_highlighted' => true,
                        'highlight_type' => 'warning',
                    ],
                    [
                        'title' => 'Noise & Distractions',
                        'content' => 'Players must respect adjacent indoor courts and keep vocalizations or music at reasonable levels.',
                    ],
                ],
            ],
            [
                'title' => 'Outdoor Match Rules',
                'icon' => 'fa-sun',
                'items' => [
                    [
                        'title' => 'Weather Delays & Rainouts',
                        'content' => 'If rain interrupts play for over 30 minutes, the match must be rescheduled unless 2 full sets were completed.',
                    ],
                    [
                        'title' => 'Wind & Light Conditions',
                        'content' => 'Play continues through normal wind unless court safety is compromised or unplayable darkness sets in.',
                    ],
                    [
                        'title' => 'Court Preparation',
                        'content' => 'Players on clay or hard courts share equal responsibility for sweeping lines or drying minor damp spots before starting.',
                    ],
                ],
            ],
            [
                'title' => 'Break Rules',
                'icon' => 'fa-clock',
                'items' => [
                    [
                        'title' => 'Changeover Duration',
                        'content' => 'Maximum allowed changeover time is 90 seconds. A 120-second rest is allowed between sets.',
                    ],
                    [
                        'title' => 'Toilet Breaks',
                        'content' => 'Each player is entitled to one 5-minute toilet break per match, preferably taken between sets.',
                    ],
                    [
                        'title' => 'Medical Timeouts',
                        'content' => 'One 3-minute medical timeout is permitted per match for acute injuries or medical evaluation.',
                        'is_highlighted' => true,
                        'highlight_type' => 'important',
                    ],
                ],
            ],
            [
                'title' => 'Late Arrival Policy',
                'icon' => 'fa-user-clock',
                'items' => [
                    [
                        'title' => 'Grace Period (5–15 Minutes)',
                        'content' => 'Arriving 5 to 15 minutes late results in loss of coin toss and a 1-game penalty awarded to the opponent.',
                    ],
                    [
                        'title' => 'Forfeit Threshold (15+ Minutes)',
                        'content' => 'Arriving more than 15 minutes past agreed match time constitutes a forfeit/walkover unless rescheduled by mutual consent.',
                        'is_highlighted' => true,
                        'highlight_type' => 'warning',
                    ],
                ],
            ],
            [
                'title' => 'Match Scheduling',
                'icon' => 'fa-calendar-days',
                'items' => [
                    [
                        'title' => 'Equal Scheduling Responsibility',
                        'content' => 'Both players share equal responsibility to initiate contact and finalize match dates within the designated round window.',
                    ],
                    [
                        'title' => 'Proof of Communication',
                        'content' => 'Retain all WhatsApp messages, emails, or PTL portal logs in case of scheduling disputes or unresponsive opponent claims.',
                    ],
                    [
                        'title' => 'Unresponsive Opponent Escalation',
                        'content' => 'If an opponent fails to reply within 72 hours, notify PTL Administration immediately for default processing.',
                    ],
                ],
            ],
            [
                'title' => 'Walkovers',
                'icon' => 'fa-flag',
                'items' => [
                    [
                        'title' => 'Late Cancellation Walkover',
                        'content' => 'Canceling a match within 24 hours of scheduled start time without valid medical documentation is treated as a walkover.',
                    ],
                    [
                        'title' => 'Walkover Scoring',
                        'content' => 'Walkovers are recorded as 6-0, 6-0 in favor of the non-defaulting player and count toward division standings.',
                    ],
                    [
                        'title' => 'Repeated Defaults',
                        'content' => 'Accumulating 2 walkovers in a season may lead to removal from playoffs and future tournament registration restrictions.',
                    ],
                ],
            ],
            [
                'title' => 'Group Placement',
                'icon' => 'fa-layer-group',
                'items' => [
                    [
                        'title' => 'NTRP & Rating Categorization',
                        'content' => 'Players are placed into groups based on verified NTRP ratings, previous PTL performance, and admin assessment.',
                    ],
                    [
                        'title' => 'Promotion & Relegation',
                        'content' => 'Top 2 finishers in each subgroup automatically qualify for higher division placement in subsequent seasons.',
                    ],
                ],
            ],
            [
                'title' => 'Injury Policy',
                'icon' => 'fa-kit-medical',
                'items' => [
                    [
                        'title' => 'In-Match Retirement',
                        'content' => 'If a player retires mid-match, the opponent receives credit for all remaining games and sets required to win.',
                    ],
                    [
                        'title' => 'Mid-Season Withdrawal',
                        'content' => 'Injured players unable to complete at least 50% of matches will have their results nullified to maintain balanced standings.',
                    ],
                ],
            ],
            [
                'title' => 'Umpiring',
                'icon' => 'fa-gavel',
                'items' => [
                    [
                        'title' => 'Self-Umpired Matches',
                        'content' => 'Matches are self-umpired. Each player makes line calls on their own side of the net in good faith.',
                    ],
                    [
                        'title' => 'Benefit of the Doubt',
                        'content' => 'Any ball that cannot be called OUT with absolute certainty must be called IN favoring the opponent.',
                        'is_highlighted' => true,
                        'highlight_type' => 'info',
                    ],
                    [
                        'title' => 'Audible Line Calls',
                        'content' => 'OUT calls must be made clearly and audibly immediately after the ball bounces.',
                    ],
                ],
            ],
            [
                'title' => 'Sportsmanship',
                'icon' => 'fa-handshake',
                'items' => [
                    [
                        'title' => 'Code of Conduct',
                        'content' => 'Racket throwing, verbal abuse, or disrespectful behavior towards opponents will result in disciplinary sanctions.',
                    ],
                    [
                        'title' => 'Post-Match Protocol',
                        'content' => 'Players shake hands at the net and mutually confirm score records prior to uploading results to the portal.',
                    ],
                ],
            ],
            [
                'title' => 'Net Rules',
                'icon' => 'fa-border-all',
                'items' => [
                    [
                        'title' => 'Net Touching',
                        'content' => 'Touching the net, net posts, or opponent court while the ball is in play results in immediate loss of point.',
                    ],
                    [
                        'title' => 'Reaching Across Net',
                        'content' => 'Hitting a ball before it crosses the net plane is illegal unless following through after legal contact.',
                    ],
                ],
            ],
            [
                'title' => 'Service Rules',
                'icon' => 'fa-volleyball',
                'items' => [
                    [
                        'title' => 'Foot Fault Restrictions',
                        'content' => 'Servers must remain behind the baseline and between the imaginary extensions of center mark and sideline until ball impact.',
                    ],
                    [
                        'title' => 'Service Let',
                        'content' => 'A served ball touching the net tape and landing in the correct service box is replayed without penalty.',
                    ],
                ],
            ],
            [
                'title' => 'No Show Policy',
                'icon' => 'fa-user-xmark',
                'items' => [
                    [
                        'title' => 'Definition of No-Show',
                        'content' => 'Failing to arrive at the venue without prior notification within 30 minutes of scheduled start time.',
                    ],
                    [
                        'title' => 'Court Fee Reimbursement',
                        'content' => 'The defaulting player is required to reimburse any non-refundable court booking fees paid by the attending player.',
                        'is_highlighted' => true,
                        'highlight_type' => 'important',
                    ],
                ],
            ],
            [
                'title' => 'Player Replacement',
                'icon' => 'fa-user-plus',
                'items' => [
                    [
                        'title' => 'Doubles Substitutions',
                        'content' => 'Emergency substitute partners are permitted in doubles subject to rating verification and admin pre-approval.',
                    ],
                    [
                        'title' => 'Singles Substitutions',
                        'content' => 'No replacement players are allowed in singles once the official group schedule window has opened.',
                    ],
                ],
            ],
            [
                'title' => 'Waiver',
                'icon' => 'fa-file-contract',
                'items' => [
                    [
                        'title' => 'Assumption of Risk',
                        'content' => 'Players participate voluntarily and assume full responsibility for any physical injuries or property loss incurred.',
                    ],
                    [
                        'title' => 'Media & Content Release',
                        'content' => 'PTL reserves the right to publish match photographs, scores, and tournament highlights on official channels.',
                    ],
                ],
            ],
            [
                'title' => 'Other Rules',
                'icon' => 'fa-shield-halved',
                'items' => [
                    [
                        'title' => 'Tennis Ball Provision',
                        'content' => 'The home or first-listed player must provide a new unopened can of extra-duty pressurized tennis balls.',
                    ],
                    [
                        'title' => 'Final Dispute Settlement',
                        'content' => 'In all matters of rule interpretation or dispute, the decision of the PTL Rules Committee is final and binding.',
                    ],
                ],
            ],
        ];

        foreach ($sectionsData as $sectionIndex => $sec) {
            $secNum = $sectionIndex + 1;
            $section = RuleSection::create([
                'title' => $sec['title'],
                'slug' => Str::slug($sec['title']),
                'icon' => $sec['icon'] ?? 'fa-book-bookmark',
                'display_order' => $secNum,
                'is_active' => true,
            ]);

            foreach ($sec['items'] as $itemIndex => $itm) {
                $subNum = $itemIndex + 1;
                RuleItem::create([
                    'rule_section_id' => $section->id,
                    'item_number' => "{$secNum}.{$subNum}",
                    'title' => $itm['title'],
                    'content' => $itm['content'],
                    'is_highlighted' => $itm['is_highlighted'] ?? false,
                    'highlight_type' => $itm['highlight_type'] ?? 'info',
                    'display_order' => $subNum,
                ]);
            }
        }

        // 3. FAQs
        RuleFaq::query()->delete();
        $faqs = [
            [
                'question' => 'Can we reschedule a playoff match?',
                'answer' => 'Yes. Playoff matches may be rescheduled if both players/teams mutually agree at least 48 hours prior to the deadline and receive written approval from PTL Administration.',
            ],
            [
                'question' => 'Who provides the tennis balls for matches?',
                'answer' => 'The home player (first-listed player/team on the match schedule) is responsible for providing a new, unopened can of pressurized extra-duty tennis balls.',
            ],
            [
                'question' => 'Can the third set be played as a 10-point tiebreaker?',
                'answer' => 'Yes. Standard PTL format specifies a 10-point Match Tiebreaker (win by 2 points) in lieu of a full 3rd set for all group and playoff matches.',
            ],
            [
                'question' => 'How are walkover matches scored for standings?',
                'answer' => 'Walkovers are logged as a 6-0, 6-0 victory ( awarding 3 standings points) to the non-defaulting player.',
            ],
            [
                'question' => 'Can injured players return for playoffs?',
                'answer' => 'An injured player may participate in playoffs only if they completed the minimum required number of group stage matches (at least 3 completed matches).',
            ],
        ];

        foreach ($faqs as $idx => $f) {
            RuleFaq::create([
                'question' => $f['question'],
                'answer' => $f['answer'],
                'display_order' => $idx + 1,
                'is_active' => true,
            ]);
        }
    }
}
