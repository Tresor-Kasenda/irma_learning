@php
    $type = $data['type'] ?? 'unknown';
    $value = $data['value'] ?? null;
    $count = $data['count'] ?? null;
    $class = $data['class'] ?? null;
@endphp

<div class="json-viewer-item">
    @if($type === 'object' || $type === 'array')
        <div class="json-viewer-{{ $type }}-header">
            @if($isCollapsible && $count > 0)
                <button
                    class="json-viewer-collapse-btn"
                    onclick="toggleJsonSection(this)"
                    type="button"
                >
                    {{ $isDefaultCollapsed ? '+' : '−' }}
                </button>
            @endif

            @if($key !== null)
                <span class="json-viewer-key">"{{ $key }}":</span>
            @endif

            <span class="json-viewer-bracket">{{ $type === 'array' ? '[' : '{' }}</span>

            @if($count !== null)
                <span class="json-viewer-count">({{ $count }} éléments)</span>
            @endif

            @if($showTypes)
                <span class="json-viewer-type">[{{ $type }}{{ $class ? ' ' . basename($class) : '' }}]</span>
            @endif
        </div>

        @if($count > 0)
            <div
                class="json-viewer-nested"
                style="display: {{ $isDefaultCollapsed ? 'none' : 'block' }}"
            >
                @foreach($value as $nestedKey => $nestedValue)
                    @include('forms.components.json-viewer-item', [
                        'data' => $nestedValue,
                        'key' => $nestedKey,
                        'depth' => $depth + 1,
                        'isCollapsible' => $isCollapsible,
                        'isDefaultCollapsed' => $isDefaultCollapsed && $depth >= 1,
                        'showTypes' => $showTypes
                    ])
                @endforeach
            </div>
        @endif

        @if(!$isCollapsible || $count === 0)
            <span class="json-viewer-bracket">{{ $type === 'array' ? ']' : '}' }}</span>
        @else
            <div style="display: {{ $isDefaultCollapsed ? 'block' : 'none' }}">
                <span class="json-viewer-bracket">{{ $type === 'array' ? ']' : '}' }}</span>
            </div>
        @endif

    @else
        {{-- Valeurs primitives --}}
        <div class="json-viewer-primitive">
            @if($key !== null)
                <span class="json-viewer-key">"{{ $key }}":</span>
            @endif

            @switch($type)
                @case('string')
                    <span class="json-viewer-value-string">"{{ $value }}"</span>
                    @break

                @case('integer')
                @case('float')
                    <span class="json-viewer-value-number">{{ $value }}</span>
                    @break

                @case('boolean')
                    <span class="json-viewer-value-boolean">{{ $value }}</span>
                    @break

                @case('null')
                    <span class="json-viewer-value-null">{{ $value }}</span>
                    @break

                @case('truncated')
                    <span class="json-viewer-value-null">{{ $value }}</span>
                    @break

                @default
                    <span class="json-viewer-value-string">"{{ $value }}"</span>
            @endswitch

            @if($showTypes && $type !== 'truncated')
                <span class="json-viewer-type">[{{ $type }}]</span>
            @endif
        </div>
    @endif
</div>
