<?php

namespace App\Http\Controllers;

use App\Models\RuleFaq;
use App\Models\RuleSection;
use App\Models\RuleVersion;
use Illuminate\View\View;

class RulesController extends Controller
{
    /**
     * Display the Rules & Regulations page.
     */
    public function index(): View
    {
        $currentVersion = RuleVersion::query()->where('is_current', true)->latest('id')->first();
        if (! $currentVersion) {
            $currentVersion = (object) [
                'version_number' => '2.3',
                'last_updated' => 'August 1, 2026',
                'changelog' => 'Version 2.3 (August 1, 2026): Initial digitized release of PTL Rules.',
            ];
        }

        $sections = RuleSection::query()
            ->with(['items' => fn ($q) => $q->orderBy('display_order')])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $faqs = RuleFaq::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $versionHistory = RuleVersion::query()
            ->orderByDesc('id')
            ->get();

        return view('rules', [
            'currentVersion' => $currentVersion,
            'sections' => $sections,
            'faqs' => $faqs,
            'versionHistory' => $versionHistory,
        ]);
    }
}
