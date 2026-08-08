@extends('layouts.admin')

@section('title', 'Rules & Regulations Management | '.config('app.name', 'playptl'))
@section('meta_description', 'Update rulebook sections, sub-rules, version log, and FAQs from the admin dashboard.')

@push('styles')
<style>
    .settings-tabs {
        display: flex;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 24px;
        gap: 8px;
        overflow-x: auto;
    }
    .settings-tab-btn {
        padding: 10px 18px;
        font-weight: 700;
        font-size: 14px;
        color: #64748b;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .settings-tab-btn:hover {
        color: #55A64E;
    }
    .settings-tab-btn.is-active {
        color: #55A64E;
        border-bottom-color: #55A64E;
    }
    .settings-tab-panel {
        display: none;
        animation: fadeIn 0.2s ease-in-out;
    }
    .settings-tab-panel.is-active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .rule-sec-card {
        border: 1px solid #d7ead9;
        border-radius: 12px;
        background: #ffffff;
        padding: 20px;
        margin-bottom: 20px;
    }
    .rule-sec-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #eef4ef;
        padding-bottom: 12px;
        margin-bottom: 16px;
    }
    .sub-rule-box {
        background: #f8faf8;
        border: 1px solid #e1efe2;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 14px;
    }
</style>
@endpush

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Rules &amp; Regulations Management</h1>
                <p class="admin-card-text">Update PTL rulebook sections, sub-rules, version log, and FAQs without touching code.</p>
            </div>
            <a class="admin-button admin-button-secondary admin-button-link" href="{{ route('rules') }}" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                <span>View Live Rules Page</span>
            </a>
        </div>

        @if (session('success'))
            <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <!-- Tabs Navigation -->
        <div class="settings-tabs" role="tablist" style="margin-top: 24px;">
            <button type="button" class="settings-tab-btn is-active" data-tab="sections" role="tab">Rule Sections &amp; Sub-rules</button>
            <button type="button" class="settings-tab-btn" data-tab="version" role="tab">Version &amp; Header Info</button>
            <button type="button" class="settings-tab-btn" data-tab="faqs" role="tab">FAQs</button>
        </div>

        <!-- 1. Rule Sections Panel -->
        <div id="panel-sections" class="settings-tab-panel is-active" role="tabpanel">
            
            <!-- Add New Rule Section Form -->
            <div style="background: #e8f6ea; border: 1px solid #d7ead9; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                <h2 style="font-size: 16px; font-weight: 800; color: #2f7a2a; margin: 0 0 12px;">
                    <i class="fa-solid fa-plus-circle" style="margin-right: 6px;"></i> Add New Rule Category Section
                </h2>
                <form class="admin-form-wide" method="POST" action="{{ route('admin.rules.store-section') }}">
                    @csrf
                    <div class="admin-form-grid" style="grid-template-columns: 2fr 1fr auto; align-items: end; gap: 14px;">
                        <div class="admin-form-group" style="margin-bottom: 0;">
                            <label class="admin-label" for="section_title">Category Title</label>
                            <input class="admin-input" id="section_title" type="text" name="title" placeholder="e.g. Tiebreak Regulations" required>
                        </div>
                        <div class="admin-form-group" style="margin-bottom: 0;">
                            <label class="admin-label" for="section_order">Display Order</label>
                            <input class="admin-input" id="section_order" type="number" name="display_order" placeholder="Auto">
                        </div>
                        <div>
                            <button class="admin-button" type="submit">
                                <i class="fa-solid fa-plus"></i>
                                <span>Create Category</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Existing Rule Sections List -->
            @foreach($sections as $secIndex => $sec)
                <div class="rule-sec-card">
                    <div class="rule-sec-header">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="admin-group-pill" style="font-size: 13px; padding: 6px 12px;">{{ $secIndex + 1 }}</span>
                            <h2 style="font-size: 18px; font-weight: 800; color: #1a1a1a; margin: 0;">{{ $sec->title }}</h2>
                            <span style="font-size: 13px; color: #64748b; font-weight: 600;">({{ count($sec->items) }} sub-rules)</span>
                        </div>
                        <form method="POST" action="{{ route('admin.rules.destroy-section', $sec->id) }}" onsubmit="return confirm('Delete category &quot;{{ $sec->title }}&quot; and all its sub-rules?');">
                            @csrf
                            @method('DELETE')
                            <button class="admin-button admin-button-secondary" type="submit" style="padding: 6px 14px; font-size: 13px; color: #dc2626; border-color: #fca5a5;">
                                <i class="fa-solid fa-trash"></i>
                                <span>Delete Category</span>
                            </button>
                        </form>
                    </div>

                    <!-- Sub-rules List under section -->
                    @foreach($sec->items as $itm)
                        <div class="sub-rule-box">
                            <form method="POST" action="{{ route('admin.rules.update-item', $itm->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="admin-form-grid" style="grid-template-columns: 120px 1fr; gap: 12px; margin-bottom: 12px;">
                                    <div>
                                        <label class="admin-label" style="font-size: 12px;">Rule #</label>
                                        <input class="admin-input" type="text" name="item_number" value="{{ $itm->item_number }}" style="padding: 8px 12px; font-size: 14px;">
                                    </div>
                                    <div>
                                        <label class="admin-label" style="font-size: 12px;">Rule Title</label>
                                        <input class="admin-input" type="text" name="title" value="{{ $itm->title }}" required style="padding: 8px 12px; font-size: 14px; font-weight: 700;">
                                    </div>
                                </div>
                                <div class="admin-form-group" style="margin-bottom: 12px;">
                                    <label class="admin-label" style="font-size: 12px;">Rule Explanation / Content</label>
                                    <textarea class="admin-input" name="content" rows="2" required style="padding: 8px 12px; font-size: 14px; min-height: 70px;">{{ $itm->content }}</textarea>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                                    <div style="display: flex; align-items: center; gap: 16px;">
                                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #1a1a1a; cursor: pointer;">
                                            <input type="checkbox" name="is_highlighted" value="1" {{ $itm->is_highlighted ? 'checked' : '' }}>
                                            <span>Highlight Rule Callout</span>
                                        </label>
                                        <select class="admin-input" name="highlight_type" style="padding: 6px 10px; font-size: 13px; width: 160px;">
                                            <option value="info" {{ $itm->highlight_type === 'info' ? 'selected' : '' }}>Info Badge</option>
                                            <option value="warning" {{ $itm->highlight_type === 'warning' ? 'selected' : '' }}>Warning (Amber)</option>
                                            <option value="important" {{ $itm->highlight_type === 'important' ? 'selected' : '' }}>Important (Red)</option>
                                            <option value="success" {{ $itm->highlight_type === 'success' ? 'selected' : '' }}>Success (Green)</option>
                                        </select>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <button class="admin-button" type="submit" style="padding: 8px 16px; font-size: 13px;">Save Changes</button>
                            </form>
                                        <form method="POST" action="{{ route('admin.rules.destroy-item', $itm->id) }}" onsubmit="return confirm('Delete sub-rule &quot;{{ $itm->title }}&quot;?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-button admin-button-secondary" type="submit" style="padding: 8px 14px; font-size: 13px; color: #dc2626; border-color: #fca5a5;">
                                                <i class="fa-solid fa-trash"></i>
                                                <span>Delete Rule</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                        </div>
                    @endforeach

                    <!-- Add Sub-rule Form -->
                    <div style="border: 1px dashed #55A64E; border-radius: 10px; padding: 16px; background: #ffffff; margin-top: 16px;">
                        <h3 style="font-size: 14px; font-weight: 800; color: #2f7a2a; margin: 0 0 12px;">
                            + Add New Sub-rule to "{{ $sec->title }}"
                        </h3>
                        <form method="POST" action="{{ route('admin.rules.store-item', $sec->id) }}">
                            @csrf
                            <div class="admin-form-grid" style="grid-template-columns: 120px 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <input class="admin-input" type="text" name="item_number" placeholder="e.g. {{ $secIndex + 1 }}.{{ count($sec->items) + 1 }}" style="padding: 8px 12px; font-size: 14px;">
                                </div>
                                <div>
                                    <input class="admin-input" type="text" name="title" placeholder="Sub-rule Title" required style="padding: 8px 12px; font-size: 14px;">
                                </div>
                            </div>
                            <div class="admin-form-group" style="margin-bottom: 12px;">
                                <textarea class="admin-input" name="content" placeholder="Detailed sub-rule explanation..." rows="2" required style="padding: 8px 12px; font-size: 14px; min-height: 70px;"></textarea>
                            </div>
                            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #1a1a1a; cursor: pointer;">
                                        <input type="checkbox" name="is_highlighted" value="1">
                                        <span>Highlight Rule</span>
                                    </label>
                                    <select class="admin-input" name="highlight_type" style="padding: 6px 10px; font-size: 13px; width: 160px;">
                                        <option value="info">Info Badge</option>
                                        <option value="warning">Warning (Amber)</option>
                                        <option value="important">Important (Red)</option>
                                        <option value="success">Success (Green)</option>
                                    </select>
                                </div>
                                <button class="admin-button admin-button-secondary" type="submit" style="padding: 8px 16px; font-size: 13px;">Add Sub-rule</button>
                            </div>
                        </form>
                    </div>

                </div>
            @endforeach

        </div>

        <!-- 2. Version & Header Settings Panel -->
        <div id="panel-version" class="settings-tab-panel" role="tabpanel">
            <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.rules.update-version') }}">
                @csrf
                <div class="admin-form-grid" style="margin-bottom: 20px;">
                    <div class="admin-form-group">
                        <label class="admin-label" for="version_number">Version Number</label>
                        <input class="admin-input" id="version_number" type="text" name="version_number" value="{{ old('version_number', $currentVersion->version_number ?? '2.3') }}" required>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-label" for="last_updated">Last Updated Date</label>
                        <input class="admin-input" id="last_updated" type="text" name="last_updated" value="{{ old('last_updated', $currentVersion->last_updated ?? 'August 1, 2026') }}" required>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="changelog">Changelog &amp; Revision History Notes</label>
                    <textarea class="admin-input admin-textarea" id="changelog" name="changelog" rows="5" required>{{ old('changelog', $currentVersion->changelog ?? '') }}</textarea>
                </div>

                <button class="admin-button" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Version Info</span>
                </button>
            </form>
        </div>

        <!-- 3. FAQs Panel -->
        <div id="panel-faqs" class="settings-tab-panel" role="tabpanel">
            
            <!-- Add FAQ Form -->
            <div style="background: #e8f6ea; border: 1px solid #d7ead9; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                <h2 style="font-size: 16px; font-weight: 800; color: #2f7a2a; margin: 0 0 12px;">
                    <i class="fa-solid fa-circle-question" style="margin-right: 6px;"></i> Add New FAQ Question
                </h2>
                <form class="admin-form-wide" method="POST" action="{{ route('admin.rules.store-faq') }}">
                    @csrf
                    <div class="admin-form-group">
                        <label class="admin-label" for="faq_question">Question</label>
                        <input class="admin-input" id="faq_question" type="text" name="question" placeholder="e.g. Can we reschedule a playoff match?" required>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-label" for="faq_answer">Answer</label>
                        <textarea class="admin-input" id="faq_answer" name="answer" rows="3" required placeholder="Provide clear explanation..."></textarea>
                    </div>
                    <button class="admin-button" type="submit">
                        <i class="fa-solid fa-plus"></i>
                        <span>Save FAQ</span>
                    </button>
                </form>
            </div>

            <!-- FAQs List Table -->
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Question</th>
                            <th style="width: 60%;">Answer</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faqs as $f)
                            <tr>
                                <td><strong>Q: {{ $f->question }}</strong></td>
                                <td>{{ $f->answer }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.rules.destroy-faq', $f->id) }}" onsubmit="return confirm('Delete this FAQ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-button admin-button-secondary" type="submit" style="padding: 6px 12px; color: #dc2626; border-color: #fca5a5;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: #64748b;">No FAQs created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.settings-tab-btn');
        const panels = document.querySelectorAll('.settings-tab-panel');

        tabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetTab = this.getAttribute('data-tab');

                tabs.forEach(b => b.classList.remove('is-active'));
                panels.forEach(p => p.classList.remove('is-active'));

                this.classList.add('is-active');
                const panel = document.getElementById('panel-' + targetTab);
                if (panel) {
                    panel.classList.add('is-active');
                }
            });
        });
    });
</script>
@endpush
