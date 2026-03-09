@extends('layouts.app')

@section('title', 'Bulletin Templates')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-4xl font-bold text-gray-900">{{ __('Bulletin Templates') }}</h1>
        <a href="{{ route('teacher.bulletin-templates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
            {{ __('Create Template') }}
        </a>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($templates as $template)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <!-- Template Preview -->
                <div class="h-64 bg-gray-200 overflow-hidden flex items-center justify-center">
                    <img src="{{ asset('storage/' . $template->template_image_path) }}" 
                         alt="{{ $template->name }}" 
                         class="w-full h-full object-cover">
                </div>

                <!-- Template Info -->
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $template->name }}</h3>
                    <p class="text-sm text-gray-600 mb-1">
                        <strong>{{ __('Class:') }}</strong> {{ $template->classe->name }}
                    </p>
                    <p class="text-sm text-gray-600 mb-3">
                        <strong>{{ __('Fields:') }}</strong> {{ count($template->field_zones ?? []) }}
                    </p>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <a href="{{ route('teacher.bulletin-templates.edit', $template) }}" 
                           class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-3 rounded text-center text-sm">
                            {{ __('Edit') }}
                        </a>
                        <form action="{{ route('teacher.bulletin-templates.destroy', $template) }}" method="POST" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded text-sm"
                                    onclick="return confirm('{{ __('Are you sure?') }}')">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-gray-100 rounded-lg p-8 text-center">
                <p class="text-gray-600 text-lg mb-4">{{ __('No templates yet.') }}</p>
                <a href="{{ route('teacher.bulletin-templates.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                    {{ __('Create the first template') }}
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($templates->hasPages())
        <div class="mt-8">
            {{ $templates->links() }}
        </div>
    @endif
</div>
@endsection
