<!DOCTYPE html>
<html>
<head>
    <title>Create Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Create Portfolio</h1>
        
        <form method="POST" action="{{ route('portfolios.store') }}" class="bg-white p-6 rounded shadow">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full p-2 border rounded">
                @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700">Description</label>
                <textarea name="description" class="w-full p-2 border rounded">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700">Type</label>
                <select name="type" class="w-full p-2 border rounded">
                    <option value="investment" {{ old('type') === 'investment' ? 'selected' : '' }}>Investment</option>
                    <option value="retirement" {{ old('type') === 'retirement' ? 'selected' : '' }}>Retirement</option>
                    <option value="trading" {{ old('type') === 'trading' ? 'selected' : '' }}>Trading</option>
                </select>
                @error('type') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
            
            <div class="flex gap-4">
                <a href="{{ route('portfolios.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</a>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create</button>
            </div>
        </form>
    </div>
</body>
</html>
