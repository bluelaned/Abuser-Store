@extends('admin.layouts.app')
@section('title', 'Announcements')
@section('content')

<style>
.ann-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.ann-header h1 { font-size:1.5rem; font-weight:700; }

/* Toggle Switch */
.toggle-switch { position:relative; display:inline-block; width:44px; height:24px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; cursor:pointer; inset:0; background:#cbd5e1; border-radius:24px; transition:.3s; }
.toggle-slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
.toggle-switch input:checked + .toggle-slider { background:var(--success); }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(20px); }

/* Style badges */
.style-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; text-transform:uppercase; }
.style-badge.default { background:rgba(37,99,235,.12); color:#2563eb; }
.style-badge.warning { background:rgba(245,158,11,.12); color:#d97706; }
.style-badge.success { background:rgba(16,185,129,.12); color:#059669; }
.style-badge.promo   { background:rgba(139,92,246,.12); color:#7c3aed; }

/* Modal */
.ann-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.65); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(6px); }
.ann-modal.open { display:flex; }
.ann-box { background:var(--bg-card); border:1px solid var(--border-color); border-radius:16px; width:100%; max-width:960px; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column; animation:annIn .25s cubic-bezier(.22,1,.36,1); }
@keyframes annIn { from{opacity:0;transform:scale(.96) translateY(14px)} to{opacity:1;transform:scale(1) translateY(0)} }
.ann-modal-header { display:flex; align-items:center; justify-content:space-between; padding:22px 28px; border-bottom:1px solid var(--border-color); position:sticky; top:0; background:var(--bg-card); z-index:10; border-radius:16px 16px 0 0; }
.ann-modal-title { font-size:1.05rem; font-weight:700; }
.ann-modal-body { padding:28px; flex:1; }
.ann-modal-footer { padding:18px 28px; border-top:1px solid var(--border-color); display:flex; gap:10px; justify-content:flex-end; position:sticky; bottom:0; background:var(--bg-card); border-radius:0 0 16px 16px; }

/* Section labels */
.sec-label { font-size:.72rem; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:var(--text-muted); margin:22px 0 10px; display:flex; align-items:center; gap:8px; }
.sec-label::after { content:''; flex:1; height:1px; background:var(--border-color); }
.sec-label:first-child { margin-top:0; }

/* Toolbar */
.editor-toolbar { display:flex; flex-wrap:wrap; gap:6px; padding:10px 12px; background:var(--bg-body); border:1px solid var(--border-color); border-bottom:none; border-radius:10px 10px 0 0; }
.tb-btn { padding:5px 10px; border:1px solid var(--border-color); border-radius:6px; background:transparent; color:var(--text-main); cursor:pointer; font-size:.8rem; transition:.15s; display:flex; align-items:center; gap:4px; }
.tb-btn:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
.tb-select { padding:5px 8px; border:1px solid var(--border-color); border-radius:6px; background:var(--bg-body); color:var(--text-main); font-size:.8rem; cursor:pointer; }
.editor-area { width:100%; min-height:220px; padding:14px; border:1px solid var(--border-color); border-radius:0 0 10px 10px; background:var(--bg-body); color:var(--text-main); font-size:.9rem; line-height:1.6; resize:vertical; font-family:'Inter',sans-serif; }
.editor-area:focus { outline:none; border-color:var(--primary); }

/* Emoji Panel */
.emoji-panel { display:none; background:var(--bg-card); border:1px solid var(--border-color); border-radius:10px; padding:12px; margin-top:8px; }
.emoji-panel.show { display:block; }
.emoji-grid { display:flex; flex-wrap:wrap; gap:4px; max-height:160px; overflow-y:auto; }
.emoji-btn { font-size:1.3rem; padding:4px 6px; border:none; background:transparent; cursor:pointer; border-radius:6px; transition:.15s; }
.emoji-btn:hover { background:var(--border-color); }

/* Button Inserter */
.btn-inserter { background:var(--bg-body); border:1px solid var(--border-color); border-radius:10px; padding:14px; margin-top:8px; display:none; }
.btn-inserter.show { display:block; }
.bi-row { display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:10px; align-items:end; }

/* Style picker */
.style-picker { display:flex; gap:8px; flex-wrap:wrap; }
.style-opt { padding:8px 16px; border-radius:8px; border:2px solid var(--border-color); cursor:pointer; font-size:.8rem; font-weight:700; transition:.15s; }
.style-opt[data-style="default"] { color:#2563eb; }
.style-opt[data-style="warning"] { color:#d97706; }
.style-opt[data-style="success"] { color:#059669; }
.style-opt[data-style="promo"]   { color:#7c3aed; }
.style-opt.selected { border-color:currentColor; background:rgba(0,0,0,.04); }

/* Preview */
.preview-popup { border-radius:14px; padding:24px 28px; border-left:5px solid #2563eb; background:var(--bg-card); box-shadow:0 8px 32px rgba(0,0,0,.18); position:relative; }
.preview-popup.warning { border-left-color:#d97706; }
.preview-popup.success { border-left-color:#059669; }
.preview-popup.promo   { border-left-color:#7c3aed; }

/* Form controls reuse */
.fc { width:100%; padding:10px 13px; border:1px solid var(--border-color); border-radius:8px; background:var(--bg-body); color:var(--text-main); font-size:.875rem; transition:.2s; }
.fc:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.fl { display:block; font-size:.78rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:5px; }
.fg { margin-bottom:14px; }
</style>

<div class="ann-header">
    <h1>📢 <span data-tr="announcements">Announcements</span></h1>
    <button onclick="openAnnModal()" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        <span data-tr="create_announcement">Create Announcement</span>
    </button>
</div>

<!-- Announcements Table -->
<div class="card" style="padding:0; overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th data-tr="ann_title">Title</th>
                <th data-tr="ann_style">Style</th>
                <th data-tr="ann_schedule">Schedule</th>
                <th data-tr="ann_created">Created By</th>
                <th data-tr="ann_active">Active</th>
                <th data-tr="action">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($announcements as $ann)
            <tr>
                <td>
                    <div style="font-weight:600;font-size:.9rem;">{{ $ann->title }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px;">{{ Str::limit(strip_tags($ann->content), 60) }}</div>
                </td>
                <td><span class="style-badge {{ $ann->popup_style }}">{{ ucfirst($ann->popup_style) }}</span></td>
                <td style="font-size:.8rem;color:var(--text-muted);">
                    @if($ann->starts_at || $ann->ends_at)
                        {{ $ann->starts_at ? $ann->starts_at->format('d M Y') : '∞' }}
                        →
                        {{ $ann->ends_at ? $ann->ends_at->format('d M Y') : '∞' }}
                    @else
                        <span style="color:var(--success);font-weight:600;">Always</span>
                    @endif
                </td>
                <td style="font-size:.8rem;">{{ $ann->creator->name ?? '-' }}</td>
                <td>
                    <label class="toggle-switch">
                        <input type="checkbox" {{ $ann->is_active ? 'checked' : '' }}
                               onchange="toggleAnn({{ $ann->id }}, this)">
                        <span class="toggle-slider"></span>
                    </label>
                </td>
                <td>
                    <div style="display: flex; gap: 6px;">
                        <button class="btn btn-sm btn-secondary" onclick="editAnn({{ $ann->id }})" data-tr="edit">Edit</button>
                        <form action="{{ route('admin.announcements.destroy', $ann->id) }}" method="POST"
                              onsubmit="return confirm('Delete this announcement?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" type="submit" data-tr="delete">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:48px;color:var(--text-muted);">
                    No announcements yet. Click "Create Announcement" to get started.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- CREATE MODAL -->
<div id="annModal" class="ann-modal">
<div class="ann-box">

    <div class="ann-modal-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:38px;height:38px;border-radius:10px;background:rgba(37,99,235,.12);display:flex;align-items:center;justify-content:center;font-size:1.2rem;">📢</div>
            <div>
                <div class="ann-modal-title" id="annModalTitle" data-tr="create_announcement">Create Announcement</div>
                <div style="font-size:.75rem;color:var(--text-muted);">Compose a rich popup message for all users</div>
            </div>
        </div>
        <button onclick="closeAnnModal()" style="width:34px;height:34px;border-radius:8px;border:1px solid var(--border-color);background:transparent;color:var(--text-muted);cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;">✕</button>
    </div>

    <div class="ann-modal-body">
    <form id="annForm" action="{{ route('admin.announcements.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_method" id="annMethod" value="POST" disabled>

        <!-- Basic -->
        <div class="sec-label">📋 Basic Info</div>
        <div style="display:grid;grid-template-columns:1fr auto;gap:14px;align-items:start;">
            <div class="fg">
                <label class="fl">Title / Subject</label>
                <input type="text" name="title" class="fc" placeholder="e.g. 🔥 Big Sale — 50% OFF This Weekend!" required>
            </div>
            <div class="fg">
                <label class="fl">Publish Now</label>
                <div style="padding:10px 0;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_active" id="pubToggle" value="1">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Schedule -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="fg">
                <label class="fl">Start Date (optional)</label>
                <input type="datetime-local" name="starts_at" class="fc">
            </div>
            <div class="fg">
                <label class="fl">End Date (optional)</label>
                <input type="datetime-local" name="ends_at" class="fc">
            </div>
        </div>

        <!-- Popup Style -->
        <div class="sec-label">🎨 Popup Style</div>
        <div class="style-picker" id="stylePicker">
            <div class="style-opt selected" data-style="default" onclick="selectStyle('default')">🔵 Default</div>
            <div class="style-opt" data-style="warning" onclick="selectStyle('warning')">🟠 Warning</div>
            <div class="style-opt" data-style="success" onclick="selectStyle('success')">🟢 Success</div>
            <div class="style-opt" data-style="promo" onclick="selectStyle('promo')">🟣 Promo</div>
        </div>
        <input type="hidden" name="popup_style" id="popupStyleInput" value="default">

        <!-- Content Editor -->
        <div class="sec-label" style="margin-top:22px;">✍️ Message Content</div>

        <!-- Toolbar -->
        <div class="editor-toolbar">
            <select class="tb-select" id="fontFamilyPicker" onchange="execCmd('fontName', this.value)">
                <option value="Inter">Inter</option>
                <option value="Roboto">Roboto</option>
                <option value="Poppins">Poppins</option>
                <option value="Georgia">Georgia</option>
                <option value="Courier New">Courier New</option>
                <option value="Arial">Arial</option>
            </select>
            <select class="tb-select" id="fontSizePicker" onchange="execCmd('fontSize', this.value)">
                <option value="3" selected>Normal</option>
                <option value="1">Small</option>
                <option value="4">Large</option>
                <option value="5">XL</option>
                <option value="6">2XL</option>
            </select>
            <button type="button" class="tb-btn" onclick="execCmd('bold')" title="Bold"><b>B</b></button>
            <button type="button" class="tb-btn" onclick="execCmd('italic')" title="Italic"><i>I</i></button>
            <button type="button" class="tb-btn" onclick="execCmd('underline')" title="Underline"><u>U</u></button>
            <button type="button" class="tb-btn" onclick="execCmd('strikeThrough')" title="Strike"><s>S</s></button>
            <button type="button" class="tb-btn" onclick="insertHr()" title="Divider">─</button>
            <button type="button" class="tb-btn" onclick="setAlign('left')" title="Left">⬅</button>
            <button type="button" class="tb-btn" onclick="setAlign('center')" title="Center">☰</button>
            <button type="button" class="tb-btn" onclick="setAlign('right')" title="Right">➡</button>
            <select class="tb-select" onchange="execCmd('foreColor', this.value); this.value=''">
                <option value="">Text Color</option>
                <option value="#ef4444">🔴 Red</option>
                <option value="#f97316">🟠 Orange</option>
                <option value="#eab308">🟡 Yellow</option>
                <option value="#22c55e">🟢 Green</option>
                <option value="#3b82f6">🔵 Blue</option>
                <option value="#a855f7">🟣 Purple</option>
                <option value="#ffffff">⬜ White</option>
            </select>
            <button type="button" class="tb-btn" onclick="toggleEmoji()">😀 Emoji</button>
            <button type="button" class="tb-btn" onclick="toggleBtnInserter()">🔘 Button</button>
            <button type="button" class="tb-btn" onclick="insertLink()">🔗 Link</button>
        </div>

        <!-- Editable Content Area -->
        <div id="annEditor" class="editor-area" contenteditable="true"
             style="min-height:200px;"
             oninput="syncEditor()"
             placeholder="Type your announcement message here..."></div>
        <textarea name="content" id="annContent" style="display:none;" required></textarea>

        <!-- Emoji Panel -->
        <div id="emojiPanel" class="emoji-panel">
            <div style="font-size:.7rem;font-weight:700;color:var(--text-muted);margin-bottom:8px;text-transform:uppercase;">Smileys & Emotion</div>
            <div class="emoji-grid" id="emojiGrid"></div>
        </div>

        <!-- Button Inserter -->
        <div id="btnInserter" class="btn-inserter">
            <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">Insert Custom Button</div>
            <div class="bi-row">
                <div>
                    <label class="fl">Button Text</label>
                    <input type="text" id="biText" class="fc" placeholder="Click Here">
                </div>
                <div>
                    <label class="fl">Link URL</label>
                    <input type="text" id="biUrl" class="fc" placeholder="https://...">
                </div>
                <div>
                    <label class="fl">Color</label>
                    <select id="biColor" class="fc">
                        <option value="#2563eb">Blue</option>
                        <option value="#d97706">Orange</option>
                        <option value="#059669">Green</option>
                        <option value="#7c3aed">Purple</option>
                        <option value="#ef4444">Red</option>
                        <option value="#1e293b">Dark</option>
                    </select>
                </div>
                <button type="button" class="btn btn-primary" style="height:40px;" onclick="insertCustomBtn()">Insert</button>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="sec-label" style="margin-top:22px;">👁️ Live Preview</div>
        <div id="previewPopup" class="preview-popup">
            <div style="display:flex;align-items:start;justify-content:space-between;gap:12px;">
                <div>
                    <div style="font-weight:700;font-size:1rem;margin-bottom:8px;" id="previewTitle">Your announcement title</div>
                    <div id="previewContent" style="font-size:.9rem;line-height:1.6;color:var(--text-muted);">
                        Your announcement content will appear here...
                    </div>
                </div>
                <button style="width:28px;height:28px;border-radius:6px;border:1px solid var(--border-color);background:transparent;cursor:pointer;flex-shrink:0;font-size:.9rem;" onclick="return false;">✕</button>
            </div>
        </div>

    </form>
    </div>

    <div class="ann-modal-footer">
        <button type="button" onclick="closeAnnModal()" class="btn btn-secondary" data-tr="cancel">Cancel</button>
        <button type="button" onclick="previewAnnouncement()" class="btn btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Preview
        </button>
        <button type="button" onclick="submitAnnForm()" class="btn btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            <span data-tr="save_publish">Save & Publish</span>
        </button>
    </div>

</div>
</div>

<script>
/* ── Emojis ── */
const EMOJIS = ['😀','😂','🥰','😎','🤩','🔥','⚡','💥','🎉','🎊','🏆','👑','💎','✨','🌟','⭐','🎁','💰','💸','🛒','🔔','📢','📣','🚀','💡','✅','❌','⚠️','ℹ️','🔒','🔓','👍','👏','🙌','💪','🤝','❤️','🧡','💛','💚','💙','💜','🔴','🟠','🟡','🟢','🔵','🟣'];

document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('emojiGrid');
    EMOJIS.forEach(em => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'emoji-btn';
        b.textContent = em;
        b.onclick = () => insertEmoji(em);
        grid.appendChild(b);
    });
});

/* ── Modal ── */
const ALL_ANNS = @json($announcements);

function openAnnModal() {
    // Reset form for create
    const form = document.getElementById('annForm');
    form.reset();
    form.action = "{{ route('admin.announcements.store') }}";
    document.getElementById('annMethod').disabled = true;
    document.getElementById('annModalTitle').textContent = "Create Announcement";

    // Reset specific fields
    document.getElementById('annEditor').innerHTML = "";
    document.getElementById('previewTitle').textContent = "Your announcement title";
    document.getElementById('previewContent').innerHTML = "Your announcement content will appear here...";
    document.getElementById('pubToggle').checked = false;
    selectStyle('default');
    syncEditor();

    document.getElementById('annModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function editAnn(id) {
    const ann = ALL_ANNS.find(a => a.id === id);
    if (!ann) return;

    const form = document.getElementById('annForm');
    form.action = `/admin/announcements/${id}`;

    const methodInput = document.getElementById('annMethod');
    methodInput.value = "PUT";
    methodInput.disabled = false;

    document.getElementById('annModalTitle').textContent = "Edit Announcement";

    // Populate fields
    form.elements['title'].value = ann.title;
    document.getElementById('previewTitle').textContent = ann.title;

    document.getElementById('pubToggle').checked = ann.is_active;

    // Dates
    if (ann.starts_at) {
        const d = new Date(ann.starts_at);
        form.elements['starts_at'].value = d.toISOString().slice(0, 16);
    } else {
        form.elements['starts_at'].value = '';
    }

    if (ann.ends_at) {
        const d = new Date(ann.ends_at);
        form.elements['ends_at'].value = d.toISOString().slice(0, 16);
    } else {
        form.elements['ends_at'].value = '';
    }

    // Style
    selectStyle(ann.popup_style || 'default');

    // Editor content
    document.getElementById('annEditor').innerHTML = ann.content;
    syncEditor();

    document.getElementById('annModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeAnnModal() {
    document.getElementById('annModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('annModal').addEventListener('click', function(e) {
    if (e.target === this) closeAnnModal();
});

/* ── Editor sync ── */
function syncEditor() {
    const ed = document.getElementById('annEditor');
    document.getElementById('annContent').value = ed.innerHTML;
    document.getElementById('previewContent').innerHTML = ed.innerHTML;
}

/* ── execCommand wrappers ── */
function execCmd(cmd, val) {
    document.getElementById('annEditor').focus();
    document.execCommand(cmd, false, val || null);
    syncEditor();
}
function insertHr() {
    execCmd('insertHTML', '<hr style="border:none;border-top:1px solid var(--border-color);margin:12px 0;">');
}
function setAlign(dir) {
    const cmds = { left:'justifyLeft', center:'justifyCenter', right:'justifyRight' };
    execCmd(cmds[dir]);
}
function insertLink() {
    const url = prompt('Enter URL:', 'https://');
    if (url) execCmd('createLink', url);
}

/* ── Emoji ── */
let savedRange = null;
function toggleEmoji() {
    saveRange();
    document.getElementById('emojiPanel').classList.toggle('show');
    document.getElementById('btnInserter').classList.remove('show');
}
function insertEmoji(em) {
    restoreRange();
    execCmd('insertText', em);
    document.getElementById('emojiPanel').classList.remove('show');
}

/* ── Custom Button ── */
function toggleBtnInserter() {
    saveRange();
    document.getElementById('btnInserter').classList.toggle('show');
    document.getElementById('emojiPanel').classList.remove('show');
}
function insertCustomBtn() {
    const text  = document.getElementById('biText').value || 'Click Here';
    const url   = document.getElementById('biUrl').value  || '#';
    const color = document.getElementById('biColor').value;
    const html  = `<a href="${url}" target="_blank" style="display:inline-block;padding:8px 20px;background:${color};color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:.85rem;margin:6px 2px;">${text}</a>`;
    restoreRange();
    execCmd('insertHTML', html);
    document.getElementById('btnInserter').classList.remove('show');
}

/* ── Save / Restore selection ── */
function saveRange() {
    const sel = window.getSelection();
    if (sel.rangeCount) savedRange = sel.getRangeAt(0).cloneRange();
}
function restoreRange() {
    const ed = document.getElementById('annEditor');
    ed.focus();
    if (savedRange) {
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(savedRange);
    }
}

/* ── Style Picker ── */
function selectStyle(style) {
    document.querySelectorAll('.style-opt').forEach(o => o.classList.toggle('selected', o.dataset.style === style));
    document.getElementById('popupStyleInput').value = style;
    const p = document.getElementById('previewPopup');
    p.className = 'preview-popup ' + style;
}

/* ── Title sync to preview ── */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('[name="title"]').addEventListener('input', function() {
        document.getElementById('previewTitle').textContent = this.value || 'Your announcement title';
    });
});

/* ── Toggle active via AJAX ── */
function toggleAnn(id, checkbox) {
    fetch(`/admin/announcements/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { checkbox.checked = data.active; })
    .catch(() => { checkbox.checked = !checkbox.checked; });
}

/* ── Submit ── */
function submitAnnForm() {
    syncEditor();
    const form = document.getElementById('annForm');
    if (!form.reportValidity()) return;
    form.submit();
}
</script>

<script>
async function previewAnnouncement() {
    const form = document.querySelector('form[action*="announcements"]');
    if (!form) return;

    const title = form.querySelector('[name="title"]')?.value;
    const content = form.querySelector('[name="content"]')?.value;
    const style = form.querySelector('[name="popup_style"]')?.value || 'default';

    if (!title || !content) {
        alert('Please fill in Title and Content first.');
        return;
    }

    try {
        const res = await fetch('{{ route('admin.announcements.preview') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            },
            body: JSON.stringify({ title, content, popup_style: style })
        });
        const ann = await res.json();

        window._previewAnn = ann;
        showPreviewPopup(ann);
    } catch(e) {
        alert('Preview failed: ' + e.message);
    }
}

function showPreviewPopup(ann) {
    const STYLE_LABELS = { default:'ANNOUNCEMENT', warning:'WARNING', success:'NOTICE', promo:'PROMO 🎁' };
    const STYLE_ICONS  = { default:'📢', warning:'⚠️', success:'✅', promo:'🎁' };

    const overlay = document.createElement('div');
    overlay.id = 'previewOverlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.72);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;';

    const style = ann.popup_style || 'default';
    const gradients = {
        default: 'linear-gradient(90deg,#2563eb,#7c3aed,#2563eb)',
        warning: 'linear-gradient(90deg,#d97706,#f59e0b,#d97706)',
        success: 'linear-gradient(90deg,#059669,#10b981,#059669)',
        promo:   'linear-gradient(90deg,#7c3aed,#a855f7,#7c3aed)',
    };
    const btnColors = {
        default: 'linear-gradient(135deg,#2563eb,#1d4ed8)',
        warning: 'linear-gradient(135deg,#d97706,#b45309)',
        success: 'linear-gradient(135deg,#059669,#047857)',
        promo:   'linear-gradient(135deg,#7c3aed,#6d28d9)',
    };

    overlay.innerHTML = `
        <div style="background:linear-gradient(145deg,#1a1d2e,#13151f);border:1px solid rgba(255,255,255,0.08);border-radius:20px;width:100%;max-width:560px;max-height:85vh;overflow-y:auto;box-shadow:0 32px 80px rgba(0,0,0,0.6);position:relative;">
            <div style="height:3px;background:${gradients[style]};border-radius:20px 20px 0 0;"></div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:22px 24px 0;gap:12px;">
                <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;">
                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(37,99,235,0.18);border:1px solid rgba(37,99,235,0.25);display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;">${STYLE_ICONS[style]||'📢'}</div>
                    <div style="min-width:0;">
                        <div style="font-size:0.95rem;font-weight:700;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${ann.title}</div>
                        <div style="font-size:0.6rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#2563eb;background:rgba(37,99,235,0.12);padding:2px 7px;border-radius:20px;display:inline-block;margin-top:2px;">${STYLE_LABELS[style]||'ANNOUNCEMENT'}</div>
                    </div>
                </div>
                <button onclick="document.getElementById('previewOverlay').remove()" style="width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,0.08);background:transparent;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.95rem;flex-shrink:0;">✕</button>
            </div>
            <div style="padding:18px 24px 20px;font-size:0.875rem;line-height:1.75;color:#94a3b8;">${ann.content}</div>
            <div style="padding:0 24px 20px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid rgba(255,255,255,0.05);padding-top:14px;">
                <span style="font-size:0.75rem;color:#64748b;font-style:italic;">Preview Mode — not published</span>
                <button onclick="document.getElementById('previewOverlay').remove()" style="padding:7px 18px;border-radius:8px;border:none;background:${btnColors[style]};color:#fff;cursor:pointer;font-size:0.78rem;font-weight:700;">Close Preview</button>
            </div>
        </div>
    `;

    overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.remove(); });
    document.body.appendChild(overlay);
}
</script>

@endsection
