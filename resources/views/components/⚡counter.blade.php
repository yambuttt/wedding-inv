<?php

use Livewire\Component;

new class extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }
};
?>

<div class="space-y-4 w-full">
    <!-- Livewire Counter (Server) -->
    <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-md flex flex-col items-center space-y-4 border border-gray-200 dark:border-zinc-800">
        <div class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Livewire Counter (Server-side)</div>
        <div class="text-4xl font-extrabold text-indigo-600 dark:text-indigo-400" id="livewire-count-display">{{ $count }}</div>
        <div class="flex space-x-3">
            <button wire:click="decrement" class="px-4 py-2 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-900 dark:text-zinc-100 font-bold rounded-lg transition-colors cursor-pointer" id="btn-livewire-dec">-</button>
            <button wire:click="increment" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition-colors cursor-pointer" id="btn-livewire-inc">+</button>
        </div>
    </div>

    <!-- Alpine.js Counter (Client) -->
    <div x-data="{ alpineCount: 0 }" class="p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-md flex flex-col items-center space-y-4 border border-gray-200 dark:border-zinc-800">
        <div class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Alpine.js Counter (Client-side)</div>
        <div class="text-4xl font-extrabold text-emerald-600 dark:text-emerald-400" x-text="alpineCount" id="alpine-count-display"></div>
        <div class="flex space-x-3">
            <button @click="alpineCount--" class="px-4 py-2 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-900 dark:text-zinc-100 font-bold rounded-lg transition-colors cursor-pointer" id="btn-alpine-dec">-</button>
            <button @click="alpineCount++" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition-colors cursor-pointer" id="btn-alpine-inc">+</button>
        </div>
    </div>
</div>