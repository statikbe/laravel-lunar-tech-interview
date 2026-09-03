<div class="max-w-md px-4 py-16 mx-auto sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold">
        Sign in
    </h1>

    <p class="mt-2 text-gray-600">
        Sign in to pick up where you left off.
    </p>

    <form
        class="mt-8 space-y-6"
        wire:submit="authenticate"
    >
        <div>
            <label
                class="block text-sm font-medium text-gray-700"
                for="email"
            >
                Email address
            </label>

            <input
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                id="email"
                type="email"
                autocomplete="email"
                required
                wire:model="email"
            >

            @error('email')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label
                class="block text-sm font-medium text-gray-700"
                for="password"
            >
                Password
            </label>

            <input
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                id="password"
                type="password"
                autocomplete="current-password"
                required
                wire:model="password"
            >

            @error('password')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input
                class="border-gray-300 rounded text-indigo-600 focus:ring-indigo-500"
                type="checkbox"
                wire:model="remember"
            >
            Remember me
        </label>

        <button
            class="w-full px-6 py-3 font-medium text-white bg-indigo-600 rounded shadow hover:bg-indigo-700 active:bg-indigo-500 focus:outline-none focus:ring"
            type="submit"
        >
            Sign in
        </button>
    </form>
</div>
