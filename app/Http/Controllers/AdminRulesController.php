<?php

namespace App\Http\Controllers;

use App\Models\RuleFaq;
use App\Models\RuleItem;
use App\Models\RuleSection;
use App\Models\RuleVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminRulesController extends Controller
{
    /**
     * Admin Dashboard view for Rules & Regulations management.
     */
    public function index(): View
    {
        $currentVersion = RuleVersion::query()->where('is_current', true)->latest('id')->first();
        $sections = RuleSection::query()->with('items')->orderBy('display_order')->get();
        $faqs = RuleFaq::query()->orderBy('display_order')->get();
        $versionHistory = RuleVersion::query()->orderByDesc('id')->get();

        return view('admin.rules.index', [
            'currentVersion' => $currentVersion,
            'sections' => $sections,
            'faqs' => $faqs,
            'versionHistory' => $versionHistory,
        ]);
    }

    /**
     * Store new Rule Section.
     */
    public function storeSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $maxOrder = (int) RuleSection::query()->max('display_order');
        RuleSection::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'display_order' => $validated['display_order'] ?? ($maxOrder + 1),
            'is_active' => true,
        ]);

        return redirect()->route('admin.rules.index')->with('success', 'Rule section created successfully.');
    }

    /**
     * Delete Rule Section.
     */
    public function destroySection(RuleSection $section): RedirectResponse
    {
        $section->delete();
        return redirect()->route('admin.rules.index')->with('success', 'Rule section and all its sub-rules deleted successfully.');
    }

    /**
     * Store new Rule Item under a section.
     */
    public function storeItem(Request $request, RuleSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'item_number' => ['nullable', 'string', 'max:20'],
            'is_highlighted' => ['nullable', 'boolean'],
            'highlight_type' => ['required', 'string', 'in:info,warning,important,success'],
        ]);

        $maxOrder = (int) $section->items()->max('display_order');
        RuleItem::create([
            'rule_section_id' => $section->id,
            'item_number' => $validated['item_number'] ?? "{$section->display_order}." . ($maxOrder + 1),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_highlighted' => $request->has('is_highlighted'),
            'highlight_type' => $validated['highlight_type'],
            'display_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.rules.index')->with('success', 'Sub-rule added successfully.');
    }

    /**
     * Update existing Rule Item.
     */
    public function updateItem(Request $request, RuleItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'item_number' => ['nullable', 'string', 'max:20'],
            'highlight_type' => ['required', 'string', 'in:info,warning,important,success'],
        ]);

        $item->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'item_number' => $validated['item_number'],
            'is_highlighted' => $request->has('is_highlighted'),
            'highlight_type' => $validated['highlight_type'],
        ]);

        return redirect()->route('admin.rules.index')->with('success', 'Sub-rule updated successfully.');
    }

    /**
     * Delete Rule Item.
     */
    public function destroyItem(RuleItem $item): RedirectResponse
    {
        $item->delete();
        return redirect()->route('admin.rules.index')->with('success', 'Sub-rule deleted.');
    }

    /**
     * Update Rule Version Log.
     */
    public function updateVersion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'version_number' => ['required', 'string', 'max:20'],
            'last_updated' => ['required', 'string', 'max:100'],
            'changelog' => ['required', 'string'],
        ]);

        $version = RuleVersion::query()->where('is_current', true)->latest('id')->first();
        if ($version) {
            $version->update([
                'version_number' => $validated['version_number'],
                'last_updated' => $validated['last_updated'],
                'changelog' => $validated['changelog'],
            ]);
        } else {
            RuleVersion::create([
                'version_number' => $validated['version_number'],
                'last_updated' => $validated['last_updated'],
                'changelog' => $validated['changelog'],
                'is_current' => true,
            ]);
        }

        return redirect()->route('admin.rules.index')->with('success', 'Rule version details updated.');
    }

    /**
     * Store FAQ item.
     */
    public function storeFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
        ]);

        $maxOrder = (int) RuleFaq::query()->max('display_order');
        RuleFaq::create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'display_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.rules.index')->with('success', 'FAQ question added.');
    }

    /**
     * Delete FAQ item.
     */
    public function destroyFaq(RuleFaq $faq): RedirectResponse
    {
        $faq->delete();
        return redirect()->route('admin.rules.index')->with('success', 'FAQ deleted.');
    }
}
