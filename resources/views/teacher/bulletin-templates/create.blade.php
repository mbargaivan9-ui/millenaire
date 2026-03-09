@extends('layouts.app')

@section('title', isset($bulletinTemplate) ? __('Edit Template') : __('Create Template'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <h1 class="text-3xl font-bold text-gray-900 mb-6">
        @if (isset($bulletinTemplate))
            {{ __('Edit Template: ') . $bulletinTemplate->name }}
        @else
            {{ __('Create Bulletin Template') }}
        @endif
    </h1>

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Editor -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            @if (!isset($bulletinTemplate))
                <!-- Upload Form -->
                <form action="{{ route('teacher.bulletin-templates.store') }}" method="POST" enctype="multipart/form-data" class="mb-6">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="classe_id" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Class') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="classe_id" id="classe_id" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" required>
                            <option value="">{{ __('Select a class') }}</option>
                            @foreach ($classe ? [$classe] : [] as $cls)
                                <option value="{{ $cls->id }}" selected>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Template Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" placeholder="e.g., Template 6ème A - 2024" 
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" required>
                    </div>

                    <div class="mb-4">
                        <label for="template_image" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Upload Blank Bulletin Image') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="template_image" id="template_image" accept="image/*" 
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" required>
                        <p class="text-sm text-gray-500 mt-2">{{ __('Supported formats: JPEG, PNG, GIF (max 5MB)') }}</p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('Create Template') }}
                    </button>
                </form>
            @else
                <!-- Template Editor -->
                <div id="templateEditor" class="bg-gray-50 rounded-lg p-4 mb-6">
                    <!-- Template Image Canvas -->
                    <div class="relative inline-block w-full bg-white border-2 border-gray-300 rounded mb-4" id="imageContainer">
                        <img id="templateImage" 
                             src="{{ asset('storage/' . $bulletinTemplate->template_image_path) }}" 
                             alt="{{ $bulletinTemplate->name }}"
                             class="w-full h-auto cursor-crosshair select-none"
                             @if ($bulletinTemplate->image_width && $bulletinTemplate->image_height)
                                style="width: auto; height: auto; max-width: 100%;"
                             @endif>
                        
                        <!-- SVG Overlay for Field Zones -->
                        <svg id="zonesSvg" class="absolute top-0 left-0 w-full h-full" style="pointer-events: none;">
                        </svg>

                        <!-- Drawing Canvas -->
                        <canvas id="drawingCanvas" class="absolute top-0 left-0" style="cursor: crosshair; display: none;"></canvas>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
                        <h3 class="font-semibold text-blue-900 mb-2">{{ __('How to use:') }}</h3>
                        <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                            <li>{{ __('Select a subject from the dropdown below') }}</li>
                            <li>{{ __('Click and drag on the image to draw a rectangle') }}</li>
                            <li>{{ __('The zone will be saved automatically') }}</li>
                            <li>{{ __('Repeat for each subject') }}</li>
                        </ol>
                    </div>

                    <!-- Controls -->
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label for="subjectSelect" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Select Subject for Zone') }}
                            </label>
                            <select id="subjectSelect" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                                <option value="">{{ __('Choose a subject...') }}</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" data-name="{{ $subject->name }}">
                                        {{ $subject->name }} ({{ $subject->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="button" id="clearDrawingBtn" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                                {{ __('Clear') }}
                            </button>
                            <button type="button" id="drawBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" disabled>
                                {{ __('Draw Zone') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar - Field Zones List -->
        @if (isset($bulletinTemplate))
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Defined Zones') }}</h2>
                    
                    <div id="zonesList" class="space-y-3">
                        @forelse ($bulletinTemplate->field_zones ?? [] as $zone)
                            <div class="bg-gray-50 border border-gray-300 rounded p-3 zone-item" data-subject-id="{{ $zone['subject_id'] }}">
                                <div class="flex justify-between items-start mb-2">
                                    <strong class="text-sm text-gray-900">
                                        @php
                                            $subject = $subjects->firstWhere('id', $zone['subject_id']);
                                        @endphp
                                        {{ $subject->name ?? 'Unknown' }}
                                    </strong>
                                    <button type="button" class="remove-zone-btn text-red-500 hover:text-red-700 text-sm font-bold"
                                            data-subject-id="{{ $zone['subject_id'] }}">
                                        ✕
                                    </button>
                                </div>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <div>X: {{ $zone['x'] }}px</div>
                                    <div>Y: {{ $zone['y'] }}px</div>
                                    <div>W: {{ $zone['width'] }}px</div>
                                    <div>H: {{ $zone['height'] }}px</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm text-center py-4">{{ __('No zones defined yet') }}</p>
                        @endforelse
                    </div>

                    <div id="zoneStats" class="mt-6 p-4 bg-blue-50 rounded text-sm">
                        <p class="text-gray-700">
                            <strong>{{ __('Total Zones:') }}</strong> <span id="totalZones">{{ count($bulletinTemplate->field_zones ?? []) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if (isset($bulletinTemplate))
        initializeTemplateEditor({
            templateId: {{ $bulletinTemplate->id }},
            imageWidth: {{ $bulletinTemplate->image_width }},
            imageHeight: {{ $bulletinTemplate->image_height }},
            fieldZones: @json($bulletinTemplate->field_zones ?? []),
            subjects: @json($subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])),
        });
    @endif
});

function initializeTemplateEditor(config) {
    const container = document.getElementById('imageContainer');
    const canvas = document.getElementById('drawingCanvas');
    const ctx = canvas ? canvas.getContext('2d') : null;
    const svg = document.getElementById('zonesSvg');
    const image = document.getElementById('templateImage');
    const subjectSelect = document.getElementById('subjectSelect');
    const drawBtn = document.getElementById('drawBtn');
    const clearBtn = document.getElementById('clearDrawingBtn');
    const zonesList = document.getElementById('zonesList');

    let isDrawing = false;
    let startX = 0, startY = 0;
    let fieldZones = [...config.fieldZones];

    // Setup canvas dimensions when image loads
    image.onload = function() {
        if (canvas) {
            canvas.width = image.offsetWidth;
            canvas.height = image.offsetHeight;
        }
        renderZones();
    };

    // Render existing zones
    function renderZones() {
        svg.innerHTML = '';
        fieldZones.forEach(zone => {
            const scaleX = image.offsetWidth / config.imageWidth;
            const scaleY = image.offsetHeight / config.imageHeight;
            
            const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            rect.setAttribute('x', zone.x * scaleX);
            rect.setAttribute('y', zone.y * scaleY);
            rect.setAttribute('width', zone.width * scaleX);
            rect.setAttribute('height', zone.height * scaleY);
            rect.setAttribute('fill', 'rgba(59, 130, 246, 0.1)');
            rect.setAttribute('stroke', '#3b82f6');
            rect.setAttribute('stroke-width', '2');
            rect.setAttribute('data-subject-id', zone.subject_id);
            
            svg.appendChild(rect);
        });
    }

    // Handle subject selection
    subjectSelect.addEventListener('change', function() {
        drawBtn.disabled = !this.value;
        if (this.value) {
            drawBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            drawBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    });

    // Enable drawing mode
    drawBtn.addEventListener('click', function() {
        if (canvas && ctx) {
            isDrawing = true;
            canvas.style.display = 'block';
            image.style.cursor = 'crosshair';
        }
    });

    // Clear current drawing
    clearBtn.addEventListener('click', function() {
        if (canvas && ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            canvas.style.display = 'none';
            isDrawing = false;
            image.style.cursor = 'default';
        }
    });

    // Mouse events for drawing
    if (canvas) {
        canvas.addEventListener('mousedown', function(e) {
            if (!isDrawing) return;
            const rect = canvas.getBoundingClientRect();
            const containerRect = container.getBoundingClientRect();
            startX = e.clientX - containerRect.left;
            startY = e.clientY - containerRect.top;
        });

        canvas.addEventListener('mousemove', function(e) {
            if (!isDrawing || !subjectSelect.value) return;
            const rect = canvas.getBoundingClientRect();
            const containerRect = container.getBoundingClientRect();
            const currentX = e.clientX - containerRect.left;
            const currentY = e.clientY - containerRect.top;

            // Redraw
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'rgba(59, 130, 246, 0.2)';
            ctx.fillRect(startX, startY, currentX - startX, currentY - startY);
            ctx.strokeStyle = '#3b82f6';
            ctx.lineWidth = 2;
            ctx.strokeRect(startX, startY, currentX - startX, currentY - startY);
        });

        canvas.addEventListener('mouseup', function(e) {
            if (!isDrawing || !subjectSelect.value) return;
            const rect = canvas.getBoundingClientRect();
            const containerRect = container.getBoundingClientRect();
            const endX = e.clientX - containerRect.left;
            const endY = e.clientY - containerRect.top;

            const scaleX = config.imageWidth / image.offsetWidth;
            const scaleY = config.imageHeight / image.offsetHeight;

            const zone = {
                subject_id: parseInt(subjectSelect.value),
                x: Math.round(Math.min(startX, endX) * scaleX),
                y: Math.round(Math.min(startY, endY) * scaleY),
                width: Math.round(Math.abs(endX - startX) * scaleX),
                height: Math.round(Math.abs(endY - startY) * scaleY),
                label: document.querySelector('#subjectSelect option:checked').dataset.name,
            };

            // Save zone via AJAX
            saveFieldZone(config.templateId, zone);

            // Reset
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            canvas.style.display = 'none';
            isDrawing = false;
            image.style.cursor = 'default';
            subjectSelect.value = '';
            drawBtn.disabled = true;
        });
    }

    // Save field zone
    function saveFieldZone(templateId, zone) {
        fetch(`/teacher/bulletin-templates/${templateId}/field-zones`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(zone),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fieldZones = data.data;
                renderZones();
                updateZonesList();
                document.getElementById('totalZones').textContent = fieldZones.length;
            }
        })
        .catch(err => console.error('Error:', err));
    }

    // Remove field zone
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-zone-btn')) {
            const subjectId = parseInt(e.target.dataset.subjectId);
            removeFieldZone(config.templateId, subjectId);
        }
    });

    function removeFieldZone(templateId, subjectId) {
        if (!confirm('{{ __('Remove this zone?') }}')) return;

        fetch(`/teacher/bulletin-templates/${templateId}/field-zones/remove`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ subject_id: subjectId }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fieldZones = data.data;
                renderZones();
                updateZonesList();
                document.getElementById('totalZones').textContent = fieldZones.length;
            }
        })
        .catch(err => console.error('Error:', err));
    }

    function updateZonesList() {
        zonesList.innerHTML = '';
        if (fieldZones.length === 0) {
            zonesList.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">{{ __('No zones defined yet') }}</p>';
            return;
        }

        fieldZones.forEach(zone => {
            const subject = config.subjects.find(s => s.id === zone.subject_id);
            const item = document.createElement('div');
            item.className = 'bg-gray-50 border border-gray-300 rounded p-3 zone-item';
            item.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <strong class="text-sm text-gray-900">${subject.name}</strong>
                    <button type="button" class="remove-zone-btn text-red-500 hover:text-red-700 text-sm font-bold" data-subject-id="${zone.subject_id}">
                        ✕
                    </button>
                </div>
                <div class="text-xs text-gray-600 space-y-1">
                    <div>X: ${zone.x}px</div>
                    <div>Y: ${zone.y}px</div>
                    <div>W: ${zone.width}px</div>
                    <div>H: ${zone.height}px</div>
                </div>
            `;
            zonesList.appendChild(item);
        });
    }
}
</script>
@endsection
