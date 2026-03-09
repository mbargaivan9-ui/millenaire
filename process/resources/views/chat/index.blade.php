@extends('layouts.app')

@section('title', 'Chat — Messages')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   CHAT MILLENAIRE — Design identique FlexAdmin (teal #1abc9c)
   ═══════════════════════════════════════════════════════ */

/* ── Variables ── */
:root {
    --chat-primary:       #1abc9c;
    --chat-primary-dark:  #16a085;
    --chat-primary-light: #e8faf6;
    --chat-bg:            #f8fafc;
    --chat-sidebar-bg:    #ffffff;
    --chat-border:        #e5e7eb;
    --chat-text:          #111827;
    --chat-muted:         #6b7280;
    --chat-bubble-mine:   #1abc9c;
    --chat-bubble-other:  #f3f4f6;
    --chat-hover:         #f9fafb;
    --chat-active:        #e8faf6;
    --chat-radius:        16px;
    --sidebar-width:      320px;
}

/* ── Layout global ── */
.chat-wrapper {
    display: flex;
    height: calc(100vh - 70px);
    background: var(--chat-bg);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    margin: -8px;
}

/* ── SIDEBAR CONVERSATIONS ── */
.chat-sidebar {
    width: var(--sidebar-width);
    min-width: var(--sidebar-width);
    background: var(--chat-sidebar-bg);
    border-right: 1px solid var(--chat-border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-sidebar-header {
    padding: 20px 20px 12px;
    border-bottom: 1px solid var(--chat-border);
    flex-shrink: 0;
}

.chat-sidebar-header h5 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--chat-text);
    margin: 0 0 12px;
}

/* ── Barre de recherche ── */
.chat-search-wrap {
    position: relative;
}

.chat-search-input {
    width: 100%;
    padding: 9px 14px 9px 36px;
    border: 1px solid var(--chat-border);
    border-radius: 24px;
    font-size: .875rem;
    color: var(--chat-text);
    background: var(--chat-bg);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}

.chat-search-input:focus {
    border-color: var(--chat-primary);
    box-shadow: 0 0 0 3px rgba(26,188,156,.12);
}

.chat-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--chat-muted);
    width: 15px;
    height: 15px;
}

/* ── Filtres All | Unread | Groups ── */
.chat-filters {
    display: flex;
    gap: 4px;
    padding: 10px 20px 0;
    flex-shrink: 0;
}

.chat-filter-btn {
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid var(--chat-border);
    background: transparent;
    font-size: .8rem;
    font-weight: 500;
    color: var(--chat-muted);
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.chat-filter-btn.active,
.chat-filter-btn:hover {
    background: var(--chat-primary);
    border-color: var(--chat-primary);
    color: #fff;
}

.chat-filter-badge {
    background: #ef4444;
    color: #fff;
    border-radius: 10px;
    font-size: .65rem;
    min-width: 17px;
    height: 17px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    font-weight: 700;
}

.chat-filter-btn.active .chat-filter-badge {
    background: rgba(255,255,255,.3);
}

/* ── Liste des conversations ── */
.chat-conv-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
    scrollbar-width: thin;
    scrollbar-color: #e5e7eb transparent;
}

.chat-conv-list::-webkit-scrollbar { width: 4px; }
.chat-conv-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

.chat-conv-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    cursor: pointer;
    border-left: 3px solid transparent;
    transition: all .15s;
    position: relative;
}

.chat-conv-item:hover {
    background: var(--chat-hover);
}

.chat-conv-item.active {
    background: var(--chat-active);
    border-left-color: var(--chat-primary);
}

/* ── Avatar ── */
.chat-avatar {
    position: relative;
    flex-shrink: 0;
}

.chat-avatar-img {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--chat-primary);
}

.chat-avatar-group {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--chat-primary), var(--chat-primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: .95rem;
}

.chat-online-dot {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    background: #22c55e;
}

.chat-offline-dot {
    background: #f59e0b;
}

/* ── Infos conversation ── */
.chat-conv-info {
    flex: 1;
    min-width: 0;
}

.chat-conv-name {
    font-weight: 600;
    font-size: .9rem;
    color: var(--chat-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-conv-preview {
    font-size: .8rem;
    color: var(--chat-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}

.chat-conv-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    flex-shrink: 0;
}

.chat-conv-time {
    font-size: .72rem;
    color: var(--chat-muted);
    white-space: nowrap;
}

.chat-unread-badge {
    background: var(--chat-primary);
    color: #fff;
    border-radius: 10px;
    font-size: .68rem;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
    font-weight: 700;
}

/* Bouton nouvelle conversation */
.chat-new-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: var(--chat-primary);
    color: #fff;
    border: none;
    border-radius: 20px;
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    margin: 0 20px 8px;
    transition: all .2s;
    width: calc(100% - 40px);
    justify-content: center;
}

.chat-new-btn:hover {
    background: var(--chat-primary-dark);
    transform: translateY(-1px);
}

/* ═══════════════════════════════════════════════════════
   ZONE DE CHAT PRINCIPALE
   ═══════════════════════════════════════════════════════ */

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #fff;
}

/* ── Header conversation active ── */
.chat-main-header {
    display: flex;
    align-items: center;
    padding: 14px 24px;
    border-bottom: 1px solid var(--chat-border);
    background: #fff;
    flex-shrink: 0;
    gap: 14px;
}

.chat-main-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-main-avatar-group {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--chat-primary), var(--chat-primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: .95rem;
}

.chat-main-info { flex: 1; }

.chat-main-name {
    font-weight: 700;
    font-size: .95rem;
    color: var(--chat-text);
}

.chat-main-status {
    font-size: .78rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

.chat-main-status .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
}

.chat-main-status.offline .dot { background: var(--chat-muted); }

.chat-main-status span { color: #22c55e; font-weight: 500; }
.chat-main-status.offline span { color: var(--chat-muted); }

.chat-header-actions {
    display: flex;
    gap: 4px;
}

.chat-header-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--chat-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .2s;
}

.chat-header-btn:hover {
    background: var(--chat-bg);
    color: var(--chat-primary);
}

/* ── Zone des messages ── */
.chat-messages-area {
    flex: 1;
    overflow-y: auto;
    padding: 16px 24px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    scrollbar-width: thin;
    scrollbar-color: #e5e7eb transparent;
}

.chat-messages-area::-webkit-scrollbar { width: 5px; }
.chat-messages-area::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

/* ── Séparateur de date ── */
.chat-date-separator {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 12px 0;
    color: var(--chat-muted);
    font-size: .78rem;
}

.chat-date-separator::before,
.chat-date-separator::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--chat-border);
}

/* ── Messages ── */
.chat-msg-group {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-bottom: 6px;
}

.chat-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    max-width: 72%;
}

.chat-msg-row.mine {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.chat-msg-row.other {
    align-self: flex-start;
}

.chat-msg-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    align-self: flex-end;
}

.chat-msg-avatar-placeholder { width: 32px; flex-shrink: 0; }

/* ── Bulle de message ── */
.chat-bubble {
    padding: 10px 14px;
    border-radius: 18px;
    font-size: .9rem;
    line-height: 1.5;
    max-width: 100%;
    word-break: break-word;
    position: relative;
    transition: all .15s;
}

.chat-bubble.mine {
    background: var(--chat-bubble-mine);
    color: #fff;
    border-bottom-right-radius: 4px;
}

.chat-bubble.other {
    background: var(--chat-bubble-other);
    color: var(--chat-text);
    border-bottom-left-radius: 4px;
}

.chat-bubble.deleted {
    opacity: .6;
    font-style: italic;
    font-size: .82rem;
}

.chat-msg-time {
    font-size: .7rem;
    color: var(--chat-muted);
    margin-top: 3px;
    padding: 0 4px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.chat-msg-row.mine .chat-msg-time {
    justify-content: flex-end;
}

.chat-seen-tick {
    color: var(--chat-primary);
}

/* ── Typing indicator ── */
.chat-typing-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
    font-size: .82rem;
    color: var(--chat-muted);
    font-style: italic;
}

.chat-typing-dots {
    display: flex;
    gap: 3px;
}

.chat-typing-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--chat-muted);
    animation: typingBounce 1.2s ease-in-out infinite;
}

.chat-typing-dots span:nth-child(2) { animation-delay: .2s; }
.chat-typing-dots span:nth-child(3) { animation-delay: .4s; }

@keyframes typingBounce {
    0%, 100% { transform: translateY(0); opacity: .4; }
    50%       { transform: translateY(-4px); opacity: 1; }
}

/* ── Attachement fichier ── */
.chat-attachment {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: rgba(255,255,255,.15);
    border-radius: 10px;
    margin-top: 4px;
    text-decoration: none;
    transition: background .2s;
    min-width: 200px;
}

.chat-attachment:hover { background: rgba(255,255,255,.25); }

.chat-attachment.other {
    background: rgba(0,0,0,.05);
    color: var(--chat-text);
}

.chat-attachment.other:hover { background: rgba(0,0,0,.08); }

.chat-attachment-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.2);
    font-size: 1rem;
    flex-shrink: 0;
}

.chat-attachment.other .chat-attachment-icon { background: rgba(26,188,156,.1); color: var(--chat-primary); }

.chat-attachment-info { flex: 1; min-width: 0; }

.chat-attachment-name {
    font-size: .83rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: inherit;
}

.chat-attachment-size { font-size: .72rem; opacity: .75; }

.chat-attachment-download {
    flex-shrink: 0;
    opacity: .7;
}

/* ── Image attachée ── */
.chat-img-attachment {
    max-width: 220px;
    max-height: 180px;
    border-radius: 10px;
    object-fit: cover;
    cursor: pointer;
    margin-top: 4px;
    transition: opacity .2s;
}

.chat-img-attachment:hover { opacity: .9; }

/* ── Réactions ── */
.chat-reactions {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    margin-top: 4px;
}

.chat-reaction-chip {
    display: flex;
    align-items: center;
    gap: 3px;
    padding: 2px 7px;
    border-radius: 12px;
    background: rgba(0,0,0,.06);
    font-size: .8rem;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all .15s;
}

.chat-reaction-chip:hover { border-color: var(--chat-primary); }
.chat-reaction-chip.mine  { border-color: var(--chat-primary); background: var(--chat-primary-light); }

/* ── Menu actions message ── */
.chat-msg-actions {
    position: absolute;
    top: -28px;
    right: 0;
    display: none;
    gap: 3px;
    background: #fff;
    border-radius: 20px;
    padding: 4px;
    box-shadow: 0 2px 12px rgba(0,0,0,.12);
    z-index: 10;
}

.chat-bubble:hover .chat-msg-actions { display: flex; }

.chat-msg-action-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--chat-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    transition: all .15s;
}

.chat-msg-action-btn:hover { background: var(--chat-bg); color: var(--chat-primary); }

/* ── Emoji picker ── */
.chat-emoji-picker {
    position: absolute;
    bottom: 100%;
    right: 0;
    background: #fff;
    border-radius: 12px;
    padding: 8px;
    display: none;
    gap: 4px;
    flex-wrap: wrap;
    max-width: 200px;
    box-shadow: 0 4px 20px rgba(0,0,0,.12);
    z-index: 20;
}

.chat-emoji-picker.open { display: flex; }

.chat-emoji-btn {
    font-size: 1.1rem;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: background .15s;
    line-height: 1;
}

.chat-emoji-btn:hover { background: var(--chat-bg); }

/* ══════════════════════════
   ZONE DE SAISIE
   ══════════════════════════ */
.chat-input-area {
    border-top: 1px solid var(--chat-border);
    padding: 14px 20px;
    background: #fff;
    flex-shrink: 0;
}

.chat-input-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--chat-bg);
    border-radius: 28px;
    padding: 6px 6px 6px 16px;
    border: 1px solid var(--chat-border);
    transition: border-color .2s, box-shadow .2s;
}

.chat-input-wrap:focus-within {
    border-color: var(--chat-primary);
    box-shadow: 0 0 0 3px rgba(26,188,156,.1);
}

.chat-input-actions-left {
    display: flex;
    gap: 2px;
    flex-shrink: 0;
}

.chat-attach-btn,
.chat-emoji-toggle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--chat-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .15s;
}

.chat-attach-btn:hover,
.chat-emoji-toggle:hover {
    background: var(--chat-border);
    color: var(--chat-primary);
}

.chat-text-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: .9rem;
    color: var(--chat-text);
    resize: none;
    max-height: 100px;
    line-height: 1.5;
    font-family: inherit;
    padding: 4px 0;
}

.chat-text-input::placeholder { color: var(--chat-muted); }

.chat-send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: var(--chat-primary);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all .2s;
}

.chat-send-btn:hover {
    background: var(--chat-primary-dark);
    transform: scale(1.05);
}

.chat-send-btn:disabled {
    background: #d1d5db;
    cursor: not-allowed;
    transform: none;
}

/* ── Preview pièce jointe ── */
.chat-attachment-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: var(--chat-primary-light);
    border-radius: 8px;
    margin-bottom: 8px;
    font-size: .82rem;
    color: var(--chat-primary-dark);
}

.chat-attachment-preview button {
    margin-left: auto;
    background: none;
    border: none;
    color: var(--chat-primary-dark);
    cursor: pointer;
    font-size: 1rem;
    padding: 0;
    line-height: 1;
}

/* ══════════════════════════
   ÉTAT VIDE (pas de conv sélectionnée)
   ══════════════════════════ */
.chat-empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--chat-muted);
    text-align: center;
    padding: 40px;
}

.chat-empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--chat-primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: var(--chat-primary);
}

/* ══════════════════════════
   MODAL NOUVELLE CONVERSATION
   ══════════════════════════ */
.chat-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.4);
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s;
}

.chat-modal-overlay.open {
    opacity: 1;
    pointer-events: all;
}

.chat-modal {
    background: #fff;
    border-radius: 16px;
    width: 480px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    transform: scale(.95);
    transition: transform .2s;
}

.chat-modal-overlay.open .chat-modal { transform: scale(1); }

.chat-modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--chat-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-modal-header h6 {
    font-weight: 700;
    font-size: 1rem;
    margin: 0;
}

.chat-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: var(--chat-bg);
    color: var(--chat-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.1rem;
}

.chat-modal-body { padding: 16px 24px; flex: 1; overflow-y: auto; }
.chat-modal-footer {
    padding: 12px 24px 20px;
    border-top: 1px solid var(--chat-border);
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

/* ── Liste utilisateurs modal ── */
.chat-user-list { max-height: 300px; overflow-y: auto; border: 1px solid var(--chat-border); border-radius: 10px; }

.chat-user-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    cursor: pointer;
    transition: background .15s;
    border-bottom: 1px solid var(--chat-border);
}

.chat-user-item:last-child { border-bottom: none; }
.chat-user-item:hover { background: var(--chat-hover); }
.chat-user-item.selected { background: var(--chat-active); }

.chat-user-item-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-user-item-info { flex: 1; }
.chat-user-item-name { font-size: .88rem; font-weight: 600; }
.chat-user-item-role {
    font-size: .75rem;
    color: var(--chat-muted);
}

.chat-user-check {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid var(--chat-border);
    transition: all .15s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-user-item.selected .chat-user-check {
    background: var(--chat-primary);
    border-color: var(--chat-primary);
    color: #fff;
}

/* ── Boutons modal ── */
.btn-chat-primary {
    background: var(--chat-primary);
    color: #fff;
    border: none;
    padding: 9px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: .88rem;
    cursor: pointer;
    transition: all .2s;
}

.btn-chat-primary:hover { background: var(--chat-primary-dark); }
.btn-chat-primary:disabled { background: #d1d5db; cursor: not-allowed; }

.btn-chat-secondary {
    background: transparent;
    color: var(--chat-muted);
    border: 1px solid var(--chat-border);
    padding: 9px 20px;
    border-radius: 8px;
    font-weight: 500;
    font-size: .88rem;
    cursor: pointer;
    transition: all .2s;
}

.btn-chat-secondary:hover { background: var(--chat-bg); color: var(--chat-text); }

/* ── Tags sélectionnés ── */
.chat-selected-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 10px;
    min-height: 32px;
}

.chat-tag {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: var(--chat-primary-light);
    border-radius: 20px;
    font-size: .78rem;
    color: var(--chat-primary-dark);
    font-weight: 500;
}

.chat-tag-remove {
    cursor: pointer;
    width: 14px;
    height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--chat-primary);
    color: #fff;
    font-size: .6rem;
    font-weight: 700;
}

/* ══════════════════════════
   ÉTATS CHARGEMENT
   ══════════════════════════ */
.chat-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    flex-direction: column;
    gap: 12px;
    color: var(--chat-muted);
    font-size: .88rem;
}

.chat-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid var(--chat-border);
    border-top-color: var(--chat-primary);
    border-radius: 50%;
    animation: spin .7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ══════════════════════════
   TOAST NOTIFICATION
   ══════════════════════════ */
.chat-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: var(--chat-primary);
    color: #fff;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: .88rem;
    font-weight: 500;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
    z-index: 9999;
    transform: translateY(80px);
    opacity: 0;
    transition: all .3s;
}

.chat-toast.show {
    transform: translateY(0);
    opacity: 1;
}

/* ══════════════════════════
   RESPONSIVE
   ══════════════════════════ */
@media (max-width: 768px) {
    .chat-wrapper { height: calc(100vh - 60px); border-radius: 0; margin: -12px -16px; }
    .chat-sidebar { width: 100%; position: absolute; z-index: 10; transition: transform .3s; }
    .chat-sidebar.hidden { transform: translateX(-100%); }
    .chat-main { width: 100%; }
    .chat-msg-row { max-width: 88%; }
}
</style>
@endpush

@section('content')

{{-- Page header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <h4 style="font-weight:700;margin:0">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px;margin-right:8px;color:#1abc9c"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Messages
    </h4>
    <nav style="font-size:.82rem;color:#6b7280">
        <a href="{{ route(auth()->user()->isAdmin() ? 'admin.dashboard' : (auth()->user()->isTeacher() ? 'teacher.dashboard' : (auth()->user()->isParent() ? 'parent.dashboard' : 'student.dashboard'))) }}" style="color:#1abc9c;text-decoration:none">Accueil</a>
        <span style="margin:0 6px">/</span>
        <span>Chat</span>
    </nav>
</div>

{{-- Chat wrapper --}}
<div class="chat-wrapper" id="chatWrapper">

    {{-- ═════════════════════════════════
         SIDEBAR CONVERSATIONS
         ═════════════════════════════════ --}}
    <div class="chat-sidebar" id="chatSidebar">

        <div class="chat-sidebar-header">
            <h5>Messages</h5>
            <div class="chat-search-wrap">
                <svg class="chat-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="chat-search-input" id="convSearch" placeholder="Rechercher une conversation...">
            </div>
        </div>

        <div class="chat-filters">
            <button class="chat-filter-btn active" data-filter="all" onclick="setFilter(this,'all')">
                Tous
            </button>
            <button class="chat-filter-btn" data-filter="unread" onclick="setFilter(this,'unread')">
                Non lus
                <span class="chat-filter-badge" id="unreadBadge" style="display:{{ $totalUnread > 0 ? 'flex' : 'none' }}">{{ $totalUnread }}</span>
            </button>
            <button class="chat-filter-btn" data-filter="groups" onclick="setFilter(this,'groups')">
                Groupes
            </button>
        </div>

        {{-- Bouton nouvelle conversation --}}
        <div style="padding:12px 20px 4px">
            <button class="chat-new-btn" onclick="openNewConvModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouvelle conversation
            </button>
        </div>

        {{-- Liste --}}
        <div class="chat-conv-list" id="convList">
            @forelse($conversations as $conv)
                @php
                    $isPrivate = $conv->type === 'private';
                    $other = $isPrivate ? $conv->participants->firstWhere('id', '!=', auth()->id()) : null;
                    $displayName = $isPrivate ? ($other?->name ?? 'Inconnu') : ($conv->name ?? 'Groupe');
                    $avatar = $other?->profile_photo ? asset('storage/'.$other->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode(substr($displayName,0,2)).'&background=1abc9c&color=fff&size=80';
                    $lastMsg = $conv->lastMessage;
                    $unreadCount = $conv->participants->firstWhere('id', auth()->id())?->pivot?->unread_count ?? 0;
                    $isOnline = $other && $other->last_login && $other->last_login->diffInMinutes(now()) < 5;
                    $previewText = $lastMsg ? (
                        $lastMsg->is_deleted ? 'Message supprimé' : (
                            $lastMsg->type !== 'text' ? '📎 Fichier joint' :
                            \Illuminate\Support\Str::limit($lastMsg->content ?? '', 48)
                        )
                    ) : 'Démarrez la conversation';
                    $isActive = isset($activeConversation) && $activeConversation->id === $conv->id;
                @endphp
                <div class="chat-conv-item {{ $isActive ? 'active' : '' }}"
                     data-conv-id="{{ $conv->id }}"
                     data-conv-type="{{ $conv->type }}"
                     onclick="selectConversation({{ $conv->id }})">

                    <div class="chat-avatar">
                        @if($isPrivate)
                            <img src="{{ $avatar }}" alt="{{ $displayName }}" class="chat-avatar-img">
                            <div class="chat-online-dot {{ $isOnline ? '' : 'chat-offline-dot' }}"></div>
                        @else
                            <div class="chat-avatar-group">{{ strtoupper(substr($displayName,0,1)) }}</div>
                        @endif
                    </div>

                    <div class="chat-conv-info">
                        <div class="chat-conv-name">{{ $displayName }}</div>
                        <div class="chat-conv-preview">{{ $previewText }}</div>
                    </div>

                    <div class="chat-conv-meta">
                        <span class="chat-conv-time">{{ $lastMsg ? $lastMsg->created_at->diffForHumans(null, true) : '' }}</span>
                        @if($unreadCount > 0)
                            <span class="chat-unread-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div style="padding:32px 20px;text-align:center;color:#9ca3af;font-size:.85rem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.4;display:block;margin:0 auto 12px"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Aucune conversation pour l'instant.<br>Démarrez un nouveau chat !
                </div>
            @endforelse
        </div>
    </div>

    {{-- ═════════════════════════════════
         ZONE PRINCIPALE DU CHAT
         ═════════════════════════════════ --}}
    <div class="chat-main" id="chatMain">

        {{-- État vide (aucune conv sélectionnée) --}}
        <div class="chat-empty-state" id="chatEmptyState" style="{{ isset($activeConversation) ? 'display:none' : '' }}">
            <div class="chat-empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <h6 style="font-weight:700;color:#374151;margin-bottom:8px">Vos messages</h6>
            <p style="font-size:.88rem;max-width:280px">Sélectionnez une conversation ou démarrez un nouveau chat avec un collègue, enseignant ou parent.</p>
            <button class="btn-chat-primary" onclick="openNewConvModal()" style="margin-top:12px">
                ✉️ Nouveau message
            </button>
        </div>

        {{-- Contenu de la conversation active --}}
        <div id="chatConvContent" style="{{ isset($activeConversation) ? '' : 'display:none' }}">

            {{-- Header --}}
            <div class="chat-main-header" id="chatHeader">
                @if(isset($activeConversation))
                    @php
                        $hIsPrivate = $activeConversation->type === 'private';
                        $hOther = $hIsPrivate ? $activeConversation->participants->firstWhere('id', '!=', auth()->id()) : null;
                        $hName = $hIsPrivate ? ($hOther?->name ?? 'Inconnu') : ($activeConversation->name ?? 'Groupe');
                        $hAvatar = $hOther?->profile_photo ? asset('storage/'.$hOther->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode(substr($hName,0,2)).'&background=1abc9c&color=fff&size=80';
                        $hOnline = $hOther && $hOther->last_login && $hOther->last_login->diffInMinutes(now()) < 5;
                    @endphp

                    @if($hIsPrivate)
                        <img src="{{ $hAvatar }}" alt="{{ $hName }}" class="chat-main-avatar" id="headerAvatar">
                    @else
                        <div class="chat-main-avatar-group" id="headerAvatar">{{ strtoupper(substr($hName,0,1)) }}</div>
                    @endif

                    <div class="chat-main-info">
                        <div class="chat-main-name" id="headerName">{{ $hName }}</div>
                        <div class="chat-main-status {{ $hOnline || !$hIsPrivate ? '' : 'offline' }}" id="headerStatus">
                            <span class="dot"></span>
                            <span id="headerStatusText">{{ $hIsPrivate ? ($hOnline ? 'En ligne' : 'Hors ligne') : (($activeConversation->participants->count()).' participants') }}</span>
                        </div>
                    </div>

                    <div class="chat-header-actions">
                        <button class="chat-header-btn" title="Informations" onclick="toggleConvInfo()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        </button>
                        <button class="chat-header-btn" title="Plus d'options" onclick="toggleHeaderMenu()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                        </button>
                    </div>
                @endif
            </div>

            {{-- Zone messages --}}
            <div class="chat-messages-area" id="messagesArea">
                @if(isset($messages) && $messages->isNotEmpty())
                    @php $prevDate = null; @endphp
                    @foreach($messages as $msg)
                        @php
                            $msgDate = $msg->created_at->isToday() ? 'Aujourd\'hui' : ($msg->created_at->isYesterday() ? 'Hier' : $msg->created_at->format('d/m/Y'));
                            $isMine  = $msg->sender_id === auth()->id();
                            $senderAvatar = $msg->sender?->profile_photo
                                ? asset('storage/'.$msg->sender->profile_photo)
                                : 'https://ui-avatars.com/api/?name='.urlencode(substr($msg->sender?->name ?? '?', 0, 2)).'&background=1abc9c&color=fff&size=80';
                        @endphp

                        @if($prevDate !== $msgDate)
                            <div class="chat-date-separator">{{ $msgDate }}</div>
                            @php $prevDate = $msgDate; @endphp
                        @endif

                        <div class="chat-msg-group" id="msgGroup{{ $msg->id }}">
                            <div class="chat-msg-row {{ $isMine ? 'mine' : 'other' }}">

                                @if(!$isMine)
                                    <img src="{{ $senderAvatar }}" alt="{{ $msg->sender?->name }}" class="chat-msg-avatar">
                                @else
                                    <div class="chat-msg-avatar-placeholder"></div>
                                @endif

                                <div style="display:flex;flex-direction:column;gap:3px;{{ $isMine ? 'align-items:flex-end' : '' }}">
                                    @if(!$isMine && isset($activeConversation) && $activeConversation->type !== 'private')
                                        <span style="font-size:.72rem;color:#6b7280;padding:0 4px;font-weight:500">{{ $msg->sender?->name }}</span>
                                    @endif

                                    <div class="chat-bubble {{ $isMine ? 'mine' : 'other' }} {{ $msg->is_deleted ? 'deleted' : '' }}"
                                         style="position:relative"
                                         data-msg-id="{{ $msg->id }}">

                                        {{-- Actions hover --}}
                                        <div class="chat-msg-actions">
                                            <button class="chat-msg-action-btn" onclick="toggleEmojiPicker({{ $msg->id }})" title="Réagir">😊</button>
                                            @if($isMine && !$msg->is_deleted)
                                                <button class="chat-msg-action-btn" onclick="deleteMessage({{ $msg->id }})" title="Supprimer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                                </button>
                                            @endif
                                            <div class="chat-emoji-picker" id="emojiPicker{{ $msg->id }}">
                                                @foreach(['👍','❤️','😂','😮','😢','🙏'] as $emoji)
                                                    <span class="chat-emoji-btn" onclick="reactToMessage({{ $msg->id }}, '{{ $emoji }}')">{{ $emoji }}</span>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Contenu --}}
                                        @if($msg->is_deleted)
                                            <span style="opacity:.6;font-style:italic">🚫 Message supprimé</span>
                                        @else
                                            @if($msg->content)
                                                <div style="white-space:pre-wrap">{{ $msg->content }}</div>
                                            @endif

                                            {{-- Pièces jointes --}}
                                            @foreach($msg->attachments as $att)
                                                @if($att->file_type === 'image')
                                                    <img src="{{ asset('storage/'.$att->file_path) }}"
                                                         alt="{{ $att->file_name }}"
                                                         class="chat-img-attachment"
                                                         onclick="openImageModal('{{ asset('storage/'.$att->file_path) }}')">
                                                @else
                                                    <a href="{{ route('chat.attachment.download', $att->id) }}"
                                                       class="chat-attachment {{ $isMine ? '' : 'other' }}"
                                                       style="color:{{ $isMine ? '#fff' : 'inherit' }}">
                                                        <div class="chat-attachment-icon">
                                                            {{ $att->file_type === 'audio' ? '🎵' : '📄' }}
                                                        </div>
                                                        <div class="chat-attachment-info">
                                                            <div class="chat-attachment-name">{{ $att->file_name }}</div>
                                                            <div class="chat-attachment-size">{{ number_format($att->file_size/1024/1024, 1) }} MB</div>
                                                        </div>
                                                        <svg class="chat-attachment-download" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    </a>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>

                                    {{-- Réactions --}}
                                    @if($msg->reactions->isNotEmpty())
                                        <div class="chat-reactions" id="reactions{{ $msg->id }}">
                                            @foreach($msg->reactions->groupBy('emoji') as $emoji => $reacts)
                                                <span class="chat-reaction-chip {{ $reacts->contains('user_id', auth()->id()) ? 'mine' : '' }}"
                                                      onclick="reactToMessage({{ $msg->id }}, '{{ $emoji }}')">
                                                    {{ $emoji }} {{ $reacts->count() }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Heure + Vu --}}
                                    <div class="chat-msg-time">
                                        {{ $msg->created_at->format('H:i') }}
                                        @if($isMine)
                                            <svg class="chat-seen-tick" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span id="seenLabel{{ $msg->id }}" style="font-size:.68rem;color:#1abc9c">Vu</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Indicateur de frappe --}}
                <div class="chat-typing-indicator" id="typingIndicator" style="display:none">
                    <div class="chat-typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                    <span id="typingText">en train d'écrire...</span>
                </div>
            </div>

            {{-- Zone de saisie --}}
            <div class="chat-input-area" id="chatInputArea">
                {{-- Preview pièce jointe --}}
                <div id="attachmentPreview" style="display:none"></div>

                <div class="chat-input-wrap">
                    <div class="chat-input-actions-left">
                        <label class="chat-attach-btn" for="fileInput" title="Joindre un fichier">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                            <input type="file" id="fileInput" style="display:none" onchange="previewFile(this)" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">
                        </label>
                        <button class="chat-attach-btn chat-emoji-toggle" title="Emoji" onclick="toggleInputEmoji()" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                        </button>
                    </div>

                    <textarea id="chatInput"
                              class="chat-text-input"
                              rows="1"
                              placeholder="Type a message..."
                              oninput="autoResize(this); handleTyping()"
                              onkeydown="handleKeyDown(event)"></textarea>

                    <button class="chat-send-btn" id="sendBtn" onclick="sendMessage()" title="Envoyer" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transform:rotate(90deg)"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>

                {{-- Emoji picker pour la saisie --}}
                <div id="inputEmojiPicker" style="display:none;padding:10px 0;flex-wrap:wrap;gap:4px">
                    @foreach(['😊','😂','❤️','👍','🙏','😮','😢','🎉','🔥','✅','👋','💪','😎','🤔','👀','✨','🎯','💡','📚','✏️'] as $em)
                        <span style="font-size:1.3rem;cursor:pointer;padding:4px;border-radius:6px;display:inline-block" onclick="insertEmoji('{{ $em }}')">{{ $em }}</span>
                    @endforeach
                </div>
            </div>

        </div>{{-- end chatConvContent --}}
    </div>{{-- end chat-main --}}

</div>{{-- end chat-wrapper --}}

{{-- ═════════════════════════════════════════════════
     MODAL NOUVELLE CONVERSATION
     ═════════════════════════════════════════════════ --}}
<div class="chat-modal-overlay" id="newConvModal">
    <div class="chat-modal">
        <div class="chat-modal-header">
            <h6>✉️ Nouvelle conversation</h6>
            <button class="chat-modal-close" onclick="closeNewConvModal()">✕</button>
        </div>
        <div class="chat-modal-body">
            {{-- Type de conversation --}}
            <div style="margin-bottom:14px">
                <label style="font-size:.82rem;font-weight:600;color:#374151;display:block;margin-bottom:6px">Type</label>
                <div style="display:flex;gap:8px">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:.85rem">
                        <input type="radio" name="convType" value="private" checked onchange="onConvTypeChange(this.value)">
                        Privé
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:.85rem">
                        <input type="radio" name="convType" value="group" onchange="onConvTypeChange(this.value)">
                        Groupe
                    </label>
                    @if(auth()->user()->isAdmin() || auth()->user()->isTeacher())
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:.85rem">
                        <input type="radio" name="convType" value="class" onchange="onConvTypeChange(this.value)">
                        Classe
                    </label>
                    @endif
                </div>
            </div>

            {{-- Nom du groupe (masqué pour privé) --}}
            <div id="groupNameWrap" style="display:none;margin-bottom:14px">
                <label style="font-size:.82rem;font-weight:600;color:#374151;display:block;margin-bottom:6px">Nom du groupe</label>
                <input type="text" id="groupNameInput" placeholder="Ex: Enseignants Terminale A..."
                       style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:.88rem;outline:none">
            </div>

            {{-- Sélection des participants --}}
            <div style="margin-bottom:10px">
                <label style="font-size:.82rem;font-weight:600;color:#374151;display:block;margin-bottom:6px">
                    Participants <span id="selectedCountLabel" style="color:#1abc9c;font-weight:700"></span>
                </label>
                <div class="chat-selected-tags" id="selectedTags"></div>
                <input type="text" id="userSearch" placeholder="Rechercher un utilisateur..."
                       oninput="searchUsers(this.value)"
                       style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:.88rem;outline:none;margin-bottom:8px">
                <div class="chat-user-list" id="userList">
                    <div class="chat-loading">
                        <div class="chat-spinner"></div>
                        Chargement...
                    </div>
                </div>
            </div>
        </div>
        <div class="chat-modal-footer">
            <button class="btn-chat-secondary" onclick="closeNewConvModal()">Annuler</button>
            <button class="btn-chat-primary" id="createConvBtn" onclick="createConversation()" disabled>
                Démarrer
            </button>
        </div>
    </div>
</div>

{{-- Modal image --}}
<div id="imageModal" onclick="this.style.display='none'" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;display:none;align-items:center;justify-content:center;cursor:zoom-out">
    <img id="imageModalImg" style="max-width:90vw;max-height:90vh;border-radius:8px;object-fit:contain">
</div>

{{-- Toast --}}
<div class="chat-toast" id="chatToast"></div>

@endsection

@push('scripts')
<script>
/* ══════════════════════════════════════════════════
   CHAT MILLENAIRE — JavaScript Complet
   ══════════════════════════════════════════════════ */

const BASE_URL      = '{{ url('') }}';
const CSRF_TOKEN    = '{{ csrf_token() }}';
const CURRENT_USER  = {{ auth()->id() }};

// État global
let activeConvId    = {{ isset($activeConversation) ? $activeConversation->id : 'null' }};
let lastMessageId   = {{ isset($messages) && $messages->isNotEmpty() ? $messages->last()->id : 0 }};
let currentFilter   = 'all';
let pollingTimer    = null;
let typingTimer     = null;
let selectedUsers   = [];
let allUsers        = [];
let isLoadingConv   = false;

// ══════════════════════════════════════════════════
//  INIT
// ══════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
    scrollToBottom();
    if (activeConvId) startPolling();
    initSearch();
    updateSendBtn();
    initMessageInput();
});

// ══════════════════════════════════════════════════
//  SÉLECTION CONVERSATION
// ══════════════════════════════════════════════════

async function selectConversation(convId) {
    if (isLoadingConv || convId === activeConvId) return;

    isLoadingConv = true;
    activeConvId  = convId;

    // Marquer actif dans la liste
    document.querySelectorAll('.chat-conv-item').forEach(el => {
        el.classList.toggle('active', el.dataset.convId === String(convId));
    });

    // Afficher le contenu
    document.getElementById('chatEmptyState').style.display  = 'none';
    document.getElementById('chatConvContent').style.display = '';

    // Afficher loader dans la zone de messages
    const area = document.getElementById('messagesArea');
    area.innerHTML = `
        <div class="chat-loading">
            <div class="chat-spinner"></div>
            Chargement...
        </div>`;

    try {
        const res  = await apiGet(`/chat/conversations/${convId}`);
        const data = await res.json();

        if (!res.ok) throw new Error(data.error || 'Erreur');

        updateHeader(data.conversation);
        renderMessages(data.messages);
        lastMessageId = data.messages.length > 0
            ? Math.max(...data.messages.map(m => m.id))
            : 0;

        startPolling();
        updateUnreadBadge();
        window.history.replaceState(null, '', `/chat?conversation=${convId}`);

    } catch (e) {
        showToast('Erreur lors du chargement de la conversation.', true);
    } finally {
        isLoadingConv = false;
    }
}

// ══════════════════════════════════════════════════
//  MISE À JOUR HEADER
// ══════════════════════════════════════════════════

function updateHeader(conv) {
    const headerAvatar = document.getElementById('headerAvatar');
    const headerName   = document.getElementById('headerName');
    const headerStatus = document.getElementById('headerStatus');
    const headerStatusTxt = document.getElementById('headerStatusText');

    if (headerName) headerName.textContent = conv.name;

    if (headerAvatar) {
        if (conv.type === 'private' && conv.avatar) {
            headerAvatar.src = conv.avatar;
            headerAvatar.className = 'chat-main-avatar';
        } else {
            headerAvatar.textContent = (conv.name || '?').charAt(0).toUpperCase();
            headerAvatar.className = 'chat-main-avatar-group';
        }
    }

    if (headerStatus) {
        headerStatus.className = `chat-main-status ${conv.is_online || conv.type !== 'private' ? '' : 'offline'}`;
    }
    if (headerStatusTxt) {
        headerStatusTxt.textContent = conv.type === 'private'
            ? (conv.is_online ? 'En ligne' : 'Hors ligne')
            : `${conv.participants_count} participants`;
    }
}

// ══════════════════════════════════════════════════
//  RENDU DES MESSAGES
// ══════════════════════════════════════════════════

function renderMessages(messages) {
    const area = document.getElementById('messagesArea');
    area.innerHTML = '';

    if (!messages || messages.length === 0) {
        area.innerHTML = `
            <div style="flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;color:#9ca3af;font-size:.85rem">
                <div style="font-size:2rem">💬</div>
                Aucun message. Dites bonjour !
            </div>`;
        return;
    }

    let prevDate = null;

    messages.forEach(msg => {
        if (msg.date_label !== prevDate) {
            const sep = document.createElement('div');
            sep.className = 'chat-date-separator';
            sep.textContent = msg.date_label;
            area.appendChild(sep);
            prevDate = msg.date_label;
        }
        area.appendChild(buildMessageEl(msg));
    });

    // Indicateur de frappe
    const typingEl = document.createElement('div');
    typingEl.className = 'chat-typing-indicator';
    typingEl.id = 'typingIndicator';
    typingEl.style.display = 'none';
    typingEl.innerHTML = `
        <div class="chat-typing-dots"><span></span><span></span><span></span></div>
        <span id="typingText">en train d'écrire...</span>`;
    area.appendChild(typingEl);

    scrollToBottom();
}

function buildMessageEl(msg) {
    const wrap = document.createElement('div');
    wrap.className = 'chat-msg-group';
    wrap.id = `msgGroup${msg.id}`;

    const row = document.createElement('div');
    row.className = `chat-msg-row ${msg.is_mine ? 'mine' : 'other'}`;

    // Avatar
    if (!msg.is_mine) {
        const av = document.createElement('img');
        av.src = msg.sender_avatar;
        av.alt = msg.sender_name;
        av.className = 'chat-msg-avatar';
        row.appendChild(av);
    } else {
        const ph = document.createElement('div');
        ph.className = 'chat-msg-avatar-placeholder';
        row.appendChild(ph);
    }

    // Contenu
    const content = document.createElement('div');
    content.style.cssText = `display:flex;flex-direction:column;gap:3px;${msg.is_mine ? 'align-items:flex-end' : ''}`;

    // Bulle
    const bubble = document.createElement('div');
    bubble.className = `chat-bubble ${msg.is_mine ? 'mine' : 'other'}${msg.is_deleted ? ' deleted' : ''}`;
    bubble.dataset.msgId = msg.id;
    bubble.style.position = 'relative';

    // Actions hover
    const actions = document.createElement('div');
    actions.className = 'chat-msg-actions';
    actions.innerHTML = `
        <button class="chat-msg-action-btn" onclick="toggleEmojiPicker(${msg.id})" title="Réagir">😊</button>
        ${msg.is_mine && !msg.is_deleted ? `<button class="chat-msg-action-btn" onclick="deleteMessage(${msg.id})" title="Supprimer">🗑️</button>` : ''}
        <div class="chat-emoji-picker" id="emojiPicker${msg.id}">
            ${'👍❤️😂😮😢🙏'.split('').map(() => '').join('')}
        </div>`;

    // Remplir les emojis
    const emojiPicker = actions.querySelector('.chat-emoji-picker');
    ['👍','❤️','😂','😮','😢','🙏'].forEach(em => {
        const span = document.createElement('span');
        span.className = 'chat-emoji-btn';
        span.textContent = em;
        span.onclick = () => reactToMessage(msg.id, em);
        emojiPicker.appendChild(span);
    });
    bubble.appendChild(actions);

    // Message content
    if (msg.is_deleted) {
        bubble.innerHTML += '<span style="opacity:.6;font-style:italic">🚫 Message supprimé</span>';
    } else {
        if (msg.content) {
            const txt = document.createElement('div');
            txt.style.whiteSpace = 'pre-wrap';
            txt.textContent = msg.content;
            bubble.appendChild(txt);
        }

        // Pièces jointes
        msg.attachments.forEach(att => {
            if (att.file_type === 'image') {
                const img = document.createElement('img');
                img.src = att.url;
                img.alt = att.file_name;
                img.className = 'chat-img-attachment';
                img.onclick = () => openImageModal(att.url);
                bubble.appendChild(img);
            } else {
                const link = document.createElement('a');
                link.href = att.download_url;
                link.className = `chat-attachment ${msg.is_mine ? '' : 'other'}`;
                link.style.color = msg.is_mine ? '#fff' : 'inherit';
                link.innerHTML = `
                    <div class="chat-attachment-icon">${att.file_type === 'audio' ? '🎵' : '📄'}</div>
                    <div class="chat-attachment-info">
                        <div class="chat-attachment-name">${escHtml(att.file_name)}</div>
                        <div class="chat-attachment-size">${att.file_size}</div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`;
                bubble.appendChild(link);
            }
        });
    }

    content.appendChild(bubble);

    // Réactions
    if (msg.reactions && msg.reactions.length > 0) {
        content.appendChild(buildReactionsEl(msg.id, msg.reactions));
    }

    // Heure
    const time = document.createElement('div');
    time.className = 'chat-msg-time';
    time.innerHTML = `${msg.created_at}${msg.is_mine ? `
        <svg class="chat-seen-tick" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        ${msg.is_seen ? '<span style="font-size:.68rem;color:#1abc9c">Vu</span>' : ''}` : ''}`;
    content.appendChild(time);

    row.appendChild(content);
    wrap.appendChild(row);
    return wrap;
}

function buildReactionsEl(msgId, reactions) {
    const div = document.createElement('div');
    div.className = 'chat-reactions';
    div.id = `reactions${msgId}`;
    reactions.forEach(r => {
        const chip = document.createElement('span');
        chip.className = `chat-reaction-chip ${r.mine ? 'mine' : ''}`;
        chip.textContent = `${r.emoji} ${r.count}`;
        chip.title = r.users;
        chip.onclick = () => reactToMessage(msgId, r.emoji);
        div.appendChild(chip);
    });
    return div;
}

// ══════════════════════════════════════════════════
//  ENVOYER UN MESSAGE
// ══════════════════════════════════════════════════

async function sendMessage() {
    const input = document.getElementById('chatInput');
    const content = input.value.trim();
    const fileInput = document.getElementById('fileInput');
    const hasFile = fileInput.files.length > 0;

    if (!content && !hasFile) return;
    if (!activeConvId) return;

    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;

    const formData = new FormData();
    formData.append('conversation_id', activeConvId);
    if (content) formData.append('content', content);
    if (hasFile) formData.append('attachment', fileInput.files[0]);
    formData.append('_token', CSRF_TOKEN);

    // Réinitialiser les champs
    input.value = '';
    input.style.height = 'auto';
    fileInput.value = '';
    document.getElementById('attachmentPreview').style.display = 'none';
    document.getElementById('attachmentPreview').innerHTML = '';
    updateSendBtn();

    try {
        const res  = await fetch(`${BASE_URL}/chat/messages`, { method: 'POST', body: formData });
        const data = await res.json();

        if (!res.ok) throw new Error(data.error || 'Erreur');

        appendMessage(data.message);
        lastMessageId = data.message.id;
        refreshConvList();

    } catch (e) {
        showToast('Erreur lors de l\'envoi : ' + e.message, true);
        sendBtn.disabled = false;
    }
}

function appendMessage(msg) {
    const area = document.getElementById('messagesArea');
    const typingEl = document.getElementById('typingIndicator');

    const el = buildMessageEl(msg);
    if (typingEl) area.insertBefore(el, typingEl);
    else area.appendChild(el);

    scrollToBottom();
}

// ══════════════════════════════════════════════════
//  SUPPRIMER UN MESSAGE
// ══════════════════════════════════════════════════

async function deleteMessage(msgId) {
    if (!confirm('Supprimer ce message ?')) return;

    const res = await apiDelete(`/chat/messages/${msgId}`);
    if (res.ok) {
        const bubble = document.querySelector(`[data-msg-id="${msgId}"]`);
        if (bubble) {
            bubble.classList.add('deleted');
            bubble.innerHTML = '<span style="opacity:.6;font-style:italic">🚫 Message supprimé</span>';
        }
    }
}

// ══════════════════════════════════════════════════
//  RÉACTIONS
// ══════════════════════════════════════════════════

async function reactToMessage(msgId, emoji) {
    closeAllEmojiPickers();

    const res  = await apiPost(`/chat/messages/${msgId}/react`, { emoji });
    const data = await res.json();

    if (data.success) {
        // Mettre à jour les réactions
        const existing = document.getElementById(`reactions${msgId}`);
        if (existing) existing.remove();

        const group = document.getElementById(`msgGroup${msgId}`);
        if (group && data.reactions && data.reactions.length > 0) {
            const content = group.querySelector('[style*="flex-direction:column"]');
            const timeEl  = group.querySelector('.chat-msg-time');
            if (content && timeEl) {
                const reactionDiv = buildReactionsEl(msgId, data.reactions);
                content.insertBefore(reactionDiv, timeEl);
            }
        }
    }
}

function toggleEmojiPicker(msgId) {
    closeAllEmojiPickers();
    const picker = document.getElementById(`emojiPicker${msgId}`);
    if (picker) picker.classList.toggle('open');
}

function closeAllEmojiPickers() {
    document.querySelectorAll('.chat-emoji-picker').forEach(p => p.classList.remove('open'));
}

// ══════════════════════════════════════════════════
//  POLLING (fallback sans WebSocket)
// ══════════════════════════════════════════════════

function startPolling() {
    stopPolling();
    if (!activeConvId) return;

    pollingTimer = setInterval(async () => {
        if (!activeConvId) return;

        try {
            const res  = await apiGet(`/chat/conversations/${activeConvId}/poll?last_message_id=${lastMessageId}`);
            const data = await res.json();

            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    // Ne pas ajouter les messages du current user (déjà affichés)
                    if (msg.sender_id !== CURRENT_USER) {
                        appendMessage(msg);
                    }
                    lastMessageId = Math.max(lastMessageId, msg.id);
                });
                refreshConvList();
            }

            // Mettre à jour le badge non-lu dans la topbar
            if (data.total_unread !== undefined) {
                updateTopbarBadge(data.total_unread);
            }

        } catch (e) {}

    }, 3000); // Polling toutes les 3 secondes
}

function stopPolling() {
    if (pollingTimer) {
        clearInterval(pollingTimer);
        pollingTimer = null;
    }
}

// ══════════════════════════════════════════════════
//  ACTUALISER LA LISTE DES CONVERSATIONS
// ══════════════════════════════════════════════════

async function refreshConvList() {
    try {
        const res  = await apiGet(`/chat/conversations?filter=${currentFilter}`);
        const data = await res.json();

        const list = document.getElementById('convList');

        // Ne mettre à jour que les previews et badges (pas reconstruire entier)
        data.conversations.forEach(conv => {
            const item = list.querySelector(`[data-conv-id="${conv.id}"]`);
            if (item) {
                const preview = item.querySelector('.chat-conv-preview');
                const time    = item.querySelector('.chat-conv-time');
                const badge   = item.querySelector('.chat-unread-badge');

                if (preview) preview.textContent = conv.last_message || '';
                if (time) time.textContent = conv.last_message_at || '';

                if (conv.unread_count > 0) {
                    if (badge) {
                        badge.textContent = conv.unread_count > 9 ? '9+' : conv.unread_count;
                        badge.style.display = 'flex';
                    } else {
                        const meta = item.querySelector('.chat-conv-meta');
                        if (meta) {
                            const nb = document.createElement('span');
                            nb.className = 'chat-unread-badge';
                            nb.textContent = conv.unread_count > 9 ? '9+' : conv.unread_count;
                            meta.appendChild(nb);
                        }
                    }
                } else if (badge) {
                    badge.style.display = 'none';
                }
            }
        });

        updateUnreadBadge(data.total_unread);

    } catch (e) {}
}

function updateUnreadBadge(count) {
    const badge = document.getElementById('unreadBadge');
    if (!badge) return;

    if (!count) {
        // Récupérer depuis API si pas fourni
        apiGet('/chat/unread').then(r => r.json()).then(d => {
            const c = d.count || 0;
            badge.textContent = c;
            badge.style.display = c > 0 ? 'flex' : 'none';
            updateTopbarBadge(c);
        });
        return;
    }
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
    updateTopbarBadge(count);
}

function updateTopbarBadge(count) {
    // Mettre à jour le badge dans la topbar globale
    const topbarBadge = document.querySelector('[data-chat-unread]');
    if (topbarBadge) {
        topbarBadge.textContent = count;
        topbarBadge.style.display = count > 0 ? '' : 'none';
    }
}

// ══════════════════════════════════════════════════
//  FILTRES CONVERSATIONS
// ══════════════════════════════════════════════════

function setFilter(btn, filter) {
    currentFilter = filter;
    document.querySelectorAll('.chat-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    loadFilteredConversations(filter);
}

async function loadFilteredConversations(filter) {
    const list = document.getElementById('convList');
    list.innerHTML = `<div class="chat-loading"><div class="chat-spinner"></div></div>`;

    try {
        const res  = await apiGet(`/chat/conversations?filter=${filter}`);
        const data = await res.json();

        list.innerHTML = '';

        if (!data.conversations || data.conversations.length === 0) {
            list.innerHTML = `<div style="padding:32px 20px;text-align:center;color:#9ca3af;font-size:.85rem">Aucune conversation.</div>`;
            return;
        }

        data.conversations.forEach(conv => {
            list.appendChild(buildConvItem(conv));
        });

    } catch (e) {
        list.innerHTML = `<div style="padding:16px;text-align:center;color:#ef4444;font-size:.85rem">Erreur de chargement.</div>`;
    }
}

function buildConvItem(conv) {
    const div = document.createElement('div');
    div.className = `chat-conv-item ${conv.id === activeConvId ? 'active' : ''}`;
    div.dataset.convId   = conv.id;
    div.dataset.convType = conv.type;
    div.onclick = () => selectConversation(conv.id);

    const avatarHtml = conv.type === 'private'
        ? `<div class="chat-avatar">
            <img src="${escHtml(conv.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(conv.name) + '&background=1abc9c&color=fff')}"
                 alt="${escHtml(conv.name)}" class="chat-avatar-img">
            <div class="chat-online-dot ${conv.is_online ? '' : 'chat-offline-dot'}"></div>
           </div>`
        : `<div class="chat-avatar">
            <div class="chat-avatar-group">${conv.name.charAt(0).toUpperCase()}</div>
           </div>`;

    div.innerHTML = `
        ${avatarHtml}
        <div class="chat-conv-info">
            <div class="chat-conv-name">${escHtml(conv.name)}</div>
            <div class="chat-conv-preview">${escHtml(conv.last_message || '')}</div>
        </div>
        <div class="chat-conv-meta">
            <span class="chat-conv-time">${escHtml(conv.last_message_at || '')}</span>
            ${conv.unread_count > 0 ? `<span class="chat-unread-badge">${conv.unread_count > 9 ? '9+' : conv.unread_count}</span>` : ''}
        </div>`;

    return div;
}

// ══════════════════════════════════════════════════
//  RECHERCHE CONVERSATIONS
// ══════════════════════════════════════════════════

let searchTimer = null;

function initSearch() {
    const input = document.getElementById('convSearch');
    if (!input) return;
    input.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => searchConversations(input.value), 350);
    });
}

async function searchConversations(q) {
    if (q.length < 2) {
        refreshConvList();
        return;
    }

    const list = document.getElementById('convList');
    list.innerHTML = `<div class="chat-loading"><div class="chat-spinner"></div></div>`;

    try {
        const res  = await apiGet(`/chat/search?q=${encodeURIComponent(q)}`);
        const data = await res.json();

        list.innerHTML = '';
        if (data.results && data.results.length > 0) {
            data.results.forEach(conv => list.appendChild(buildConvItem(conv)));
        } else {
            list.innerHTML = `<div style="padding:24px 20px;text-align:center;color:#9ca3af;font-size:.85rem">Aucun résultat pour "${escHtml(q)}"</div>`;
        }
    } catch (e) {}
}

// ══════════════════════════════════════════════════
//  MODAL NOUVELLE CONVERSATION
// ══════════════════════════════════════════════════

async function openNewConvModal() {
    document.getElementById('newConvModal').classList.add('open');
    document.getElementById('selectedTags').innerHTML = '';
    document.getElementById('groupNameInput').value = '';
    document.getElementById('userSearch').value = '';
    selectedUsers = [];
    updateCreateBtn();

    // Charger les utilisateurs
    await loadUsers('');
}

function closeNewConvModal() {
    document.getElementById('newConvModal').classList.remove('open');
}

async function loadUsers(search) {
    const list = document.getElementById('userList');
    list.innerHTML = `<div class="chat-loading"><div class="chat-spinner"></div></div>`;

    try {
        const res  = await apiGet(`/chat/users?q=${encodeURIComponent(search)}`);
        const data = await res.json();

        allUsers = data.users || [];
        renderUserList(allUsers);

    } catch (e) {
        list.innerHTML = `<div style="padding:16px;text-align:center;color:#ef4444;font-size:.82rem">Erreur de chargement.</div>`;
    }
}

let userSearchTimer = null;

function searchUsers(q) {
    clearTimeout(userSearchTimer);
    userSearchTimer = setTimeout(() => loadUsers(q), 350);
}

function renderUserList(users) {
    const list = document.getElementById('userList');
    list.innerHTML = '';

    if (!users || users.length === 0) {
        list.innerHTML = `<div style="padding:24px;text-align:center;color:#9ca3af;font-size:.82rem">Aucun utilisateur disponible.</div>`;
        return;
    }

    users.forEach(user => {
        const isSelected = selectedUsers.some(u => u.id === user.id);
        const div = document.createElement('div');
        div.className = `chat-user-item ${isSelected ? 'selected' : ''}`;
        div.dataset.userId = user.id;
        div.innerHTML = `
            <img src="${escHtml(user.profile_photo)}" alt="${escHtml(user.name)}" class="chat-user-item-avatar">
            <div class="chat-user-item-info">
                <div class="chat-user-item-name">${escHtml(user.name)}</div>
                <div class="chat-user-item-role">${escHtml(user.role_label)}</div>
            </div>
            <div class="chat-user-check">${isSelected ? '✓' : ''}</div>`;
        div.onclick = () => toggleUserSelection(user, div);
        list.appendChild(div);
    });
}

function toggleUserSelection(user, el) {
    const idx = selectedUsers.findIndex(u => u.id === user.id);
    const convType = document.querySelector('input[name="convType"]:checked')?.value;

    if (idx > -1) {
        selectedUsers.splice(idx, 1);
        el.classList.remove('selected');
        el.querySelector('.chat-user-check').textContent = '';
    } else {
        // Pour une conv privée : 1 seul utilisateur
        if (convType === 'private') {
            selectedUsers = [];
            document.querySelectorAll('.chat-user-item').forEach(i => {
                i.classList.remove('selected');
                i.querySelector('.chat-user-check').textContent = '';
            });
        }
        selectedUsers.push(user);
        el.classList.add('selected');
        el.querySelector('.chat-user-check').textContent = '✓';
    }

    updateSelectedTags();
    updateCreateBtn();
}

function updateSelectedTags() {
    const container = document.getElementById('selectedTags');
    const count = document.getElementById('selectedCountLabel');
    container.innerHTML = '';

    selectedUsers.forEach(user => {
        const tag = document.createElement('div');
        tag.className = 'chat-tag';
        tag.innerHTML = `
            ${escHtml(user.name)}
            <span class="chat-tag-remove" onclick="removeSelectedUser(${user.id})">✕</span>`;
        container.appendChild(tag);
    });

    count.textContent = selectedUsers.length > 0 ? `(${selectedUsers.length})` : '';
}

function removeSelectedUser(userId) {
    selectedUsers = selectedUsers.filter(u => u.id !== userId);
    renderUserList(allUsers);
    updateSelectedTags();
    updateCreateBtn();
}

function updateCreateBtn() {
    const btn = document.getElementById('createConvBtn');
    btn.disabled = selectedUsers.length === 0;
}

function onConvTypeChange(type) {
    const nameWrap = document.getElementById('groupNameWrap');
    nameWrap.style.display = (type === 'group' || type === 'class') ? 'block' : 'none';

    // Pour une conv privée : max 1 participant
    if (type === 'private' && selectedUsers.length > 1) {
        selectedUsers = [selectedUsers[0]];
        updateSelectedTags();
        renderUserList(allUsers);
    }
}

async function createConversation() {
    if (selectedUsers.length === 0) return;

    const type = document.querySelector('input[name="convType"]:checked')?.value || 'private';
    const name = document.getElementById('groupNameInput')?.value?.trim() || null;
    const btn  = document.getElementById('createConvBtn');
    btn.disabled = true;
    btn.textContent = 'Création...';

    try {
        const res  = await apiPost('/chat/conversations', {
            type,
            name,
            participants: selectedUsers.map(u => u.id),
        });
        const data = await res.json();

        if (!res.ok) throw new Error(data.error || 'Erreur');

        closeNewConvModal();
        showToast('Conversation créée avec succès !');

        // Recharger la liste et ouvrir la conv
        await loadFilteredConversations(currentFilter);
        selectConversation(data.conversation_id);

    } catch (e) {
        showToast(e.message, true);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Démarrer';
    }
}

// ══════════════════════════════════════════════════
//  SAISIE : AUTO-RESIZE, KEYBOARD, TYPING
// ══════════════════════════════════════════════════

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 100) + 'px';
    updateSendBtn();
}

function updateSendBtn() {
    const input = document.getElementById('chatInput');
    const file  = document.getElementById('fileInput');
    const btn   = document.getElementById('sendBtn');
    if (!btn) return;
    btn.disabled = (!input?.value.trim() && !file?.files.length) || !activeConvId;
}

function initMessageInput() {
    document.getElementById('chatInput')?.addEventListener('input', updateSendBtn);
}

function handleKeyDown(e) {
    // Ctrl+Enter ou Shift+Enter = nouvelle ligne
    if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey) {
        e.preventDefault();
        sendMessage();
    }
}

let typingTimeout = null;
let isTyping = false;

function handleTyping() {
    if (!activeConvId) return;

    if (!isTyping) {
        isTyping = true;
        apiPost('/chat/typing', { conversation_id: activeConvId, is_typing: true });
    }

    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
        isTyping = false;
        apiPost('/chat/typing', { conversation_id: activeConvId, is_typing: false });
    }, 2000);
}

// ══════════════════════════════════════════════════
//  PIÈCES JOINTES
// ══════════════════════════════════════════════════

function previewFile(input) {
    const preview = document.getElementById('attachmentPreview');
    if (!input.files.length) {
        preview.style.display = 'none';
        preview.innerHTML = '';
        updateSendBtn();
        return;
    }

    const file = input.files[0];
    const size = file.size >= 1048576
        ? (file.size / 1048576).toFixed(1) + ' MB'
        : Math.round(file.size / 1024) + ' KB';

    preview.style.display = 'flex';
    preview.innerHTML = `
        <div class="chat-attachment-preview">
            <span>📎</span>
            <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(file.name)}</span>
            <span style="opacity:.7;font-size:.75rem">${size}</span>
            <button onclick="clearFile()" title="Supprimer">✕</button>
        </div>`;
    updateSendBtn();
}

function clearFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('attachmentPreview').style.display = 'none';
    document.getElementById('attachmentPreview').innerHTML = '';
    updateSendBtn();
}

// ══════════════════════════════════════════════════
//  EMOJI DANS LA SAISIE
// ══════════════════════════════════════════════════

function toggleInputEmoji() {
    const picker = document.getElementById('inputEmojiPicker');
    picker.style.display = picker.style.display === 'flex' ? 'none' : 'flex';
}

function insertEmoji(emoji) {
    const input = document.getElementById('chatInput');
    const pos = input.selectionStart;
    input.value = input.value.slice(0, pos) + emoji + input.value.slice(pos);
    input.selectionStart = input.selectionEnd = pos + emoji.length;
    input.focus();
    document.getElementById('inputEmojiPicker').style.display = 'none';
    updateSendBtn();
}

// ══════════════════════════════════════════════════
//  IMAGE MODAL
// ══════════════════════════════════════════════════

function openImageModal(src) {
    const modal = document.getElementById('imageModal');
    const img   = document.getElementById('imageModalImg');
    img.src = src;
    modal.style.display = 'flex';
}

// ══════════════════════════════════════════════════
//  SCROLL TO BOTTOM
// ══════════════════════════════════════════════════

function scrollToBottom(smooth = false) {
    const area = document.getElementById('messagesArea');
    if (!area) return;
    area.scrollTo({ top: area.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
}

// ══════════════════════════════════════════════════
//  HEADER EXTRA ACTIONS
// ══════════════════════════════════════════════════

function toggleConvInfo() {
    // Possibilité d'afficher un panel d'infos sur la conversation
    showToast('Informations de la conversation');
}

function toggleHeaderMenu() {
    // Menu contextuel (archiver, couper le son...)
}

// ══════════════════════════════════════════════════
//  TOAST
// ══════════════════════════════════════════════════

let toastTimer = null;

function showToast(message, isError = false) {
    const toast = document.getElementById('chatToast');
    toast.textContent = message;
    toast.style.background = isError ? '#ef4444' : '#1abc9c';
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
}

// ══════════════════════════════════════════════════
//  API HELPERS
// ══════════════════════════════════════════════════

function apiGet(url) {
    return fetch(BASE_URL + url, {
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    });
}

function apiPost(url, data) {
    return fetch(BASE_URL + url, {
        method:  'POST',
        headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  CSRF_TOKEN,
            'Accept':        'application/json',
        },
        body: JSON.stringify(data),
    });
}

function apiDelete(url) {
    return fetch(BASE_URL + url, {
        method:  'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    });
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Fermer les menus en cliquant en dehors
document.addEventListener('click', (e) => {
    if (!e.target.closest('.chat-emoji-picker') && !e.target.closest('.chat-msg-action-btn')) {
        closeAllEmojiPickers();
    }
    if (!e.target.closest('#newConvModal') && !e.target.closest('.chat-new-btn')) {
        // Ne pas fermer le modal si on clique sur les boutons du modal
    }
    if (!e.target.closest('#inputEmojiPicker') && !e.target.closest('.chat-emoji-toggle')) {
        const p = document.getElementById('inputEmojiPicker');
        if (p) p.style.display = 'none';
    }
});

// Fermer le modal au clic sur l'overlay
document.getElementById('newConvModal').addEventListener('click', function(e) {
    if (e.target === this) closeNewConvModal();
});
</script>
@endpush
