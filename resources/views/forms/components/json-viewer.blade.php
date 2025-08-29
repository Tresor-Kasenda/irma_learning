@php
    $data = $getState();
    $processedData = $processData($data);
    $theme = $getTheme();
    $showTypes = $shouldShowTypes();
    $stats = $getStatsForData($data);
    $uniqueId = 'json-viewer-' . uniqid();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="json-viewer-wrapper" id="{{ $uniqueId }}">
        @if($hasToolbar() && $data)
            <div class="json-viewer-toolbar {{ $theme === 'dark' ? 'json-viewer-dark' : 'json-viewer-light' }}">
                <div class="json-viewer-toolbar-left">
                    <button type="button" class="json-viewer-btn"
                            onclick="JsonViewerUtils.expandAll(document.getElementById('{{ $uniqueId }}'))">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.897-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Tout étendre
                    </button>

                    <button type="button" class="json-viewer-btn"
                            onclick="JsonViewerUtils.collapseAll(document.getElementById('{{ $uniqueId }}'))">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                        Tout réduire
                    </button>

                    <button type="button" class="json-viewer-btn"
                            onclick="JsonViewerUtils.copyToClipboard(JSON.stringify(@json($data), null, 2))">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copier JSON
                    </button>
                </div>

                <div class="json-viewer-toolbar-right">
                    <div class="json-viewer-stats">
                        <span class="json-viewer-stat">
                            <strong>{{ $stats['total_keys'] }}</strong> clés
                        </span>
                        <span class="json-viewer-stat">
                            <strong>{{ $stats['max_depth'] }}</strong> niveaux
                        </span>
                        <span class="json-viewer-stat">
                            <strong>{{ $stats['size_estimate'] }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <div class="json-viewer-container">
            @if($data)
                @if($isSearchable())
                    <div class="json-viewer-search">
                        <input
                            type="text"
                            placeholder="Rechercher dans les données..."
                            class="json-viewer-search-input"
                            oninput="filterJsonViewer(this.value, '{{ $uniqueId }}')"
                        >
                    </div>
                @endif

                <div class="json-viewer-content">
                    @include('forms.components.json-viewer-item', [
                        'data' => $processedData,
                        'key' => null,
                        'depth' => 0,
                        'isCollapsible' => $isCollapsible(),
                        'isDefaultCollapsed' => $isDefaultCollapsed(),
                        'showTypes' => $showTypes,
                        'lineNumber' => 1,
                        'hasLineNumbers' => $hasLineNumbers()
                    ])
                </div>
            @else
                <div class="json-viewer-empty">
                    <div class="json-viewer-empty-icon">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="text-gray-500 italic">Aucune donnée à afficher</span>
                </div>
            @endif
        </div>
    </div>

    @once
        <style>
            /* Variables CSS pour les thèmes */
            /* Define default (light) theme variables at root level */
            :root {
                --json-bg: #ffffff;
                --json-border: #e5e7eb;
                --json-text: #374151;
                --json-key: #059669;
                --json-string: #dc2626;
                --json-number: #2563eb;
                --json-boolean: #7c3aed;
                --json-null: #6b7280;
                --json-type: #9333ea;
                --json-collapse-bg: #f3f4f6;
                --json-collapse-hover: #e5e7eb;
                --json-shadow: rgba(0, 0, 0, 0.05);
                --json-highlight: #fef3c7;
                --json-toolbar-bg: #f9fafb;
                --json-btn-hover: #f3f4f6;
            }

            /* Override variables for dark theme using Filament's dark mode */
            .dark {
                --json-bg: #1f2937;
                --json-border: #374151;
                --json-text: #d1d5db;
                --json-key: #10b981;
                --json-string: #f87171;
                --json-number: #60a5fa;
                --json-boolean: #a78bfa;
                --json-null: #9ca3af;
                --json-type: #c084fc;
                --json-collapse-bg: #374151;
                --json-collapse-hover: #4b5563;
                --json-shadow: rgba(0, 0, 0, 0.25);
                --json-highlight: #451a03;
                --json-toolbar-bg: #374151;
                --json-btn-hover: #4b5563;
            }

            /* Layout principal */
            .json-viewer-wrapper {
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid var(--json-border);
            }

            /* Toolbar */
            .json-viewer-toolbar {
                background: var(--json-toolbar-bg);
                border-bottom: 1px solid var(--json-border);
                padding: 8px 12px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px;
            }

            .json-viewer-toolbar-left {
                display: flex;
                gap: 6px;
                align-items: center;
            }

            .json-viewer-toolbar-right {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .json-viewer-btn {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 6px 10px;
                background: transparent;
                border: 1px solid var(--json-border);
                border-radius: 6px;
                font-size: 12px;
                color: var(--json-text);
                cursor: pointer;
                transition: all 0.2s;
            }

            .json-viewer-btn:hover {
                background: var(--json-btn-hover);
            }

            .json-viewer-stats {
                display: flex;
                gap: 12px;
                font-size: 11px;
                color: var(--json-text);
                opacity: 0.8;
            }

            .json-viewer-stat {
                white-space: nowrap;
            }

            /* Recherche */
            .json-viewer-search {
                padding: 12px;
                border-bottom: 1px solid var(--json-border);
                background: var(--json-bg);
            }

            .json-viewer-search-input {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid var(--json-border);
                border-radius: 6px;
                background: var(--json-bg);
                color: var(--json-text);
                font-size: 13px;
            }

            .json-viewer-search-input:focus {
                outline: none;
                border-color: var(--json-key);
                box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.1);
            }

            /* Contenu principal */
            .json-viewer-container {
                background: var(--json-bg);
                padding: 16px;
                font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
                font-size: 13px;
                line-height: 1.4;
                max-height: 600px;
                overflow-y: auto;
                color: var(--json-text);
            }

            /* Scrollbar */
            .json-viewer-container::-webkit-scrollbar {
                width: 8px;
            }

            .json-viewer-container::-webkit-scrollbar-track {
                background: transparent;
            }

            .json-viewer-container::-webkit-scrollbar-thumb {
                background: var(--json-border);
                border-radius: 4px;
            }

            .json-viewer-container::-webkit-scrollbar-thumb:hover {
                background: var(--json-collapse-hover);
            }

            /* Éléments JSON */
            .json-viewer-item {
                margin: 2px 0;
                position: relative;
            }

            .json-viewer-item.highlighted {
                background: var(--json-highlight);
                padding: 2px 4px;
                border-radius: 3px;
                animation: highlight-pulse 2s ease-out;
            }

            @keyframes highlight-pulse {
                0%, 100% {
                    background: var(--json-highlight);
                }
                50% {
                    background: transparent;
                }
            }

            .json-viewer-line-number {
                display: inline-block;
                width: 30px;
                text-align: right;
                margin-right: 12px;
                color: var(--json-null);
                font-size: 11px;
                user-select: none;
            }

            .json-viewer-key {
                color: var(--json-key);
                font-weight: 600;
                margin-right: 8px;
            }

            .json-viewer-value-string {
                color: var(--json-string);
                word-break: break-all;
            }

            .json-viewer-value-number {
                color: var(--json-number);
            }

            .json-viewer-value-boolean {
                color: var(--json-boolean);
                font-weight: 600;
            }

            .json-viewer-value-null {
                color: var(--json-null);
                font-style: italic;
            }

            .json-viewer-type {
                color: var(--json-type);
                font-size: 11px;
                opacity: 0.8;
                margin-left: 8px;
            }

            .json-viewer-collapse-btn {
                background: var(--json-collapse-bg);
                border: 1px solid var(--json-border);
                border-radius: 4px;
                padding: 2px 6px;
                font-size: 11px;
                cursor: pointer;
                margin-right: 8px;
                transition: background-color 0.2s;
                color: var(--json-text);
                min-width: 20px;
                text-align: center;
            }

            .json-viewer-collapse-btn:hover {
                background: var(--json-collapse-hover);
            }

            .json-viewer-nested {
                margin-left: 20px;
                border-left: 1px solid var(--json-border);
                padding-left: 12px;
                margin-top: 4px;
            }

            .json-viewer-count {
                color: var(--json-type);
                font-size: 11px;
                opacity: 0.7;
                margin-left: 8px;
            }

            .json-viewer-bracket {
                color: var(--json-text);
                font-weight: bold;
            }

            /* État vide */
            .json-viewer-empty {
                text-align: center;
                padding: 40px 20px;
                color: var(--json-null);
            }

            .json-viewer-empty-icon {
                margin-bottom: 12px;
                opacity: 0.5;
            }

            .json-viewer-empty-icon svg {
                margin: 0 auto;
                display: block;
            }

            /* Headers */
            .json-viewer-object-header,
            .json-viewer-array-header {
                display: flex;
                align-items: center;
                margin-bottom: 4px;
            }

            /* Responsive */
            @media (max-width: 640px) {
                .json-viewer-toolbar {
                    flex-direction: column;
                    align-items: stretch;
                }

                .json-viewer-toolbar-left,
                .json-viewer-toolbar-right {
                    justify-content: center;
                }

                .json-viewer-stats {
                    justify-content: center;
                }
            }

            /* États de filtrage */
            .json-viewer-item.filtered-hidden {
                display: none;
            }

            .json-viewer-item.filtered-match .json-viewer-key,
            .json-viewer-item.filtered-match .json-viewer-value-string {
                background: var(--json-highlight);
                padding: 1px 2px;
                border-radius: 2px;
            }
        </style>

        <script>
            const JsonViewerUtils = {
                toggleSection: function (button) {
                    const content = button.closest('.json-viewer-object-header, .json-viewer-array-header')
                        .nextElementSibling;

                    if (content && content.classList.contains('json-viewer-nested')) {
                        const isVisible = content.style.display !== 'none';
                        content.style.display = isVisible ? 'none' : 'block';

                        button.textContent = isVisible ? '+' : '−';

                        const closingBracket = content.nextElementSibling;
                        if (closingBracket && closingBracket.style) {
                            closingBracket.style.display = isVisible ? 'block' : 'none';
                        }
                    }
                },

                expandAll: function (container) {
                    const buttons = container.querySelectorAll('.json-viewer-collapse-btn');
                    buttons.forEach(button => {
                        const content = button.closest('.json-viewer-object-header, .json-viewer-array-header')
                            .nextElementSibling;
                        if (content && content.style.display === 'none') {
                            this.toggleSection(button);
                        }
                    });
                },

                collapseAll: function (container) {
                    const buttons = container.querySelectorAll('.json-viewer-collapse-btn');
                    buttons.forEach(button => {
                        const content = button.closest('.json-viewer-object-header, .json-viewer-array-header')
                            .nextElementSibling;
                        if (content && content.style.display !== 'none') {
                            this.toggleSection(button);
                        }
                    });
                },

                copyToClipboard: function (text) {
                    navigator.clipboard.writeText(text)
                        .then(() => {
                            const notification = document.createElement('div');
                            notification.textContent = 'Copied to clipboard!';
                            notification.style.position = 'fixed';
                            notification.style.bottom = '20px';
                            notification.style.right = '20px';
                            notification.style.padding = '10px 20px';
                            notification.style.backgroundColor = 'var(--json-key, #059669)';
                            notification.style.color = '#fff';
                            notification.style.borderRadius = '4px';
                            notification.style.zIndex = '9999';

                            document.body.appendChild(notification);
                            setTimeout(() => notification.remove(), 2000);
                        })
                        .catch(err => console.error('Failed to copy: ', err));
                },

                filterViewer: function (searchTerm, containerId) {
                    const container = document.getElementById(containerId);
                    const items = container.querySelectorAll('.json-viewer-item');

                    if (!searchTerm.trim()) {
                        items.forEach(item => {
                            item.classList.remove('filtered-hidden', 'filtered-match');
                        });
                        return;
                    }

                    const searchLower = searchTerm.toLowerCase();

                    items.forEach(item => {
                        const keyElement = item.querySelector('.json-viewer-key');
                        const valueElement = item.querySelector('[class*="json-viewer-value-"]');

                        let hasMatch = false;

                        if (keyElement) {
                            const keyText = keyElement.textContent.toLowerCase();
                            hasMatch = keyText.includes(searchLower);
                        }

                        if (!hasMatch && valueElement) {
                            const valueText = valueElement.textContent.toLowerCase();
                            hasMatch = valueText.includes(searchLower);
                        }

                        if (hasMatch) {
                            item.classList.remove('filtered-hidden');
                            item.classList.add('filtered-match');

                            let parent = item.parentElement;
                            while (parent && parent.classList.contains('json-viewer-nested')) {
                                if (parent.style.display === 'none') {
                                    const header = parent.previousElementSibling;
                                    const btn = header?.querySelector('.json-viewer-collapse-btn');
                                    if (btn) {
                                        this.toggleSection(btn);
                                    }
                                }
                                parent = parent.parentElement;
                            }
                        } else {
                            item.classList.add('filtered-hidden');
                            item.classList.remove('filtered-match');
                        }
                    });
                }
            };

            function toggleJsonSection(button) {
                JsonViewerUtils.toggleSection(button);
            }

            function filterJsonViewer(searchTerm, containerId) {
                JsonViewerUtils.filterViewer(searchTerm, containerId);
            }
        </script>
    @endonce
</x-dynamic-component>
