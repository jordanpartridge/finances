<!DOCTYPE html>
<html>
<head>
    <title>Finance Portfolio Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Your Portfolios</h1>
        
        <a href="{{ route('portfolios.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
            Create Portfolio
        </a>
        
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        @if($portfolios->count() > 0)
            <div class="grid gap-4">
                @foreach($portfolios as $portfolio)
                    <div class="bg-white p-6 rounded shadow">
                        <h2 class="text-xl font-semibold">{{ $portfolio->name }}</h2>
                        <p class="text-gray-600">{{ $portfolio->description }}</p>
                        <div class="mt-2">
                            <span class="text-sm bg-gray-200 px-2 py-1 rounded">{{ $portfolio->type }}</span>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="font-bold">${{ number_format($portfolio->calculateValue(), 2) }}</span>
                            <div>
                                <a href="{{ route('portfolios.show', $portfolio) }}" class="text-blue-500 mr-4">View</a>
                                <a href="{{ route('portfolios.edit', $portfolio) }}" class="text-blue-500 mr-4">Edit</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No portfolios yet. <a href="{{ route('portfolios.create') }}" class="text-blue-500">Create one</a></p>
        @endif
    </div>
</body>
</html>
