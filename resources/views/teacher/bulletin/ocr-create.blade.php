{{--
    teacher/bulletin/ocr-create.blade.php
    OCR Wizard with Vue.js Components Integration
--}}

@extends('layouts.app')

@section('title', 'Digitaliseur OCR de Bulletin')

@push('styles')
<style>
    #ocr-wizard-app {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 30px 0;
    }
    
    .ocr-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    [v-cloak] {
        display: none;
    }
</style>
@endpush

@section('content')
<div id="ocr-wizard-app" v-cloak>
    <div class="ocr-container">
        <ocr-wizard
            :upload-url="uploadUrl"
            :csrf-token="csrfToken"
            @wizard-complete="handleWizardComplete"
        ></ocr-wizard>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    // Import Vue and components
    import { createApp } from 'vue';
    import OCRWizard from '/resources/js/components/OCRWizard.vue';
    
    // Create Vue app
    const app = createApp({
        data() {
            return {
                uploadUrl: '{{ route("api.v1.teacher.bulletin.ocr.upload") }}',
                csrfToken: '{{ csrf_token() }}'
            };
        },
        methods: {
            handleWizardComplete(data) {
                console.log('OCR Wizard Complete:', data);
                // Optionally redirect or show success message
                // window.location.href = '/teacher/bulletin/grid';
            }
        },
        components: {
            OCRWizard
        }
    });
    
    app.mount('#ocr-wizard-app');
</script>
@endpush
