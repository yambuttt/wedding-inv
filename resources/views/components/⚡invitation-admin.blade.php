<?php

use Livewire\Component;
use App\Models\Invitation;
use App\Models\Greeting;
use App\Models\Guest;
use Livewire\Attributes\Validate;

new class extends Component
{
    public Invitation $invitation;
    public $activeTab = 'pengantin'; // pengantin, acara, konten, doa, tamu
    public $isLoggedIn = false;

    // Login credentials
    public $email = '';
    public $password = '';

    // Guest name input
    public $newGuestName = '';

    // Invitation settings
    public $groom_name_short;
    public $groom_name_full;
    public $groom_father;
    public $groom_mother;

    public $bride_name_short;
    public $bride_name_full;
    public $bride_father;
    public $bride_mother;

    public $event_date;
    public $akad_time;
    public $akad_location;
    public $resepsi_time;
    public $resepsi_location;
    public $maps_url;
    public $maps_embed_url;
    public $latitude;
    public $longitude;
    public $template;

    public $welcome_message;
    public $bg_music_url;

    public function mount($slug = 'sari-raju')
    {
        $this->isLoggedIn = auth()->check();
        
        $this->invitation = Invitation::where('slug', $slug)->firstOrFail();
        
        $this->groom_name_short = $this->invitation->groom_name_short;
        $this->groom_name_full = $this->invitation->groom_name_full;
        $this->groom_father = $this->invitation->groom_father;
        $this->groom_mother = $this->invitation->groom_mother;

        $this->bride_name_short = $this->invitation->bride_name_short;
        $this->bride_name_full = $this->invitation->bride_name_full;
        $this->bride_father = $this->invitation->bride_father;
        $this->bride_mother = $this->invitation->bride_mother;

        $this->event_date = $this->invitation->event_date->format('Y-m-d\TH:i');
        $this->akad_time = $this->invitation->akad_time;
        $this->akad_location = $this->invitation->akad_location;
        $this->resepsi_time = $this->invitation->resepsi_time;
        $this->resepsi_location = $this->invitation->resepsi_location;
        $this->maps_url = $this->invitation->maps_url;
        $this->maps_embed_url = $this->invitation->maps_embed_url;
        $this->latitude = $this->invitation->latitude;
        $this->longitude = $this->invitation->longitude;
        $this->template = $this->invitation->template ?? 'elegant';

        $this->welcome_message = $this->invitation->welcome_message;
        $this->bg_music_url = $this->invitation->bg_music_url;
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.'
        ]);

        if (auth()->attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->isLoggedIn = true;
            session()->regenerate();
            $this->reset(['email', 'password']);
        } else {
            $this->addError('loginError', 'Email atau password salah.');
        }
    }

    public function logout()
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->isLoggedIn = false;
    }

    public function saveSettings()
    {
        if (!auth()->check()) {
            $this->isLoggedIn = false;
            return;
        }

        $this->validate([
            'groom_name_short' => 'required|string|max:50',
            'groom_name_full' => 'required|string|max:100',
            'bride_name_short' => 'required|string|max:50',
            'bride_name_full' => 'required|string|max:100',
            'event_date' => 'required|date',
            'akad_time' => 'nullable|string|max:100',
            'akad_location' => 'nullable|string|max:255',
            'resepsi_time' => 'nullable|string|max:100',
            'resepsi_location' => 'nullable|string|max:255',
            'maps_url' => 'nullable|url',
            'maps_embed_url' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'template' => 'required|string|in:elegant,genz,pastel,retro',
            'welcome_message' => 'nullable|string|max:1000',
            'bg_music_url' => 'nullable|string',
        ], [
            'required' => 'Kolom ini wajib diisi.',
            'url' => 'Format link URL tidak valid.',
            'date' => 'Format tanggal tidak valid.',
            'numeric' => 'Nilai koordinat harus berupa angka.'
        ]);

        $this->invitation->update([
            'groom_name_short' => $this->groom_name_short,
            'groom_name_full' => $this->groom_name_full,
            'groom_father' => $this->groom_father,
            'groom_mother' => $this->groom_mother,

            'bride_name_short' => $this->bride_name_short,
            'bride_name_full' => $this->bride_name_full,
            'bride_father' => $this->bride_father,
            'bride_mother' => $this->bride_mother,

            'event_date' => $this->event_date,
            'akad_time' => $this->akad_time,
            'akad_location' => $this->akad_location,
            'resepsi_time' => $this->resepsi_time,
            'resepsi_location' => $this->resepsi_location,
            'maps_url' => $this->maps_url,
            'maps_embed_url' => $this->maps_embed_url,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'template' => $this->template,

            'welcome_message' => $this->welcome_message,
            'bg_music_url' => $this->bg_music_url,
        ]);

        session()->flash('success_message', 'Semua pengaturan berhasil disimpan!');
    }

    public function deleteGreeting($id)
    {
        if (!auth()->check()) {
            $this->isLoggedIn = false;
            return;
        }

        Greeting::destroy($id);
        session()->flash('success_message', 'Ucapan berhasil dihapus!');
    }

    public function getGreetings()
    {
        return $this->invitation->greetings()->latest()->get();
    }

    // Guest Management Actions
    public function addGuest()
    {
        if (!auth()->check()) {
            $this->isLoggedIn = false;
            return;
        }

        $this->validate([
            'newGuestName' => 'required|string|min:2|max:100'
        ], [
            'newGuestName.required' => 'Nama tamu tidak boleh kosong.',
            'newGuestName.min' => 'Nama tamu minimal 2 karakter.',
            'newGuestName.max' => 'Nama tamu maksimal 100 karakter.'
        ]);

        Guest::create([
            'invitation_id' => $this->invitation->id,
            'name' => $this->newGuestName,
        ]);

        $this->reset('newGuestName');
        session()->flash('success_message', 'Tamu berhasil ditambahkan ke daftar!');
    }

    public function deleteGuest($id)
    {
        if (!auth()->check()) {
            $this->isLoggedIn = false;
            return;
        }

        Guest::destroy($id);
        session()->flash('success_message', 'Tamu berhasil dihapus!');
    }

    public function getGuests()
    {
        return $this->invitation->guests()->latest()->get();
    }
};
?>

<div>
    @if ($isLoggedIn)
        <!-- Dashboard Admin View with Sidebar -->
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-[#030907] flex flex-col md:flex-row relative">
            
            <!-- Mobile Sidebar Backdrop -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:opacity
                class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 md:hidden"></div>

            <!-- Sidebar Panel -->
            <aside class="fixed inset-y-0 left-0 w-64 bg-stone-950 border-r border-stone-850 z-40 transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:h-screen md:sticky md:top-0 flex flex-col shrink-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
                
                <!-- Sidebar Header -->
                <div class="p-5 border-b border-stone-850 flex justify-between items-center shrink-0">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-[#e6ca65] flex items-center space-x-2">
                            <span>S&amp;R Admin</span>
                        </h2>
                        <p class="text-[9px] text-stone-500 mt-0.5">Wedding Invitation Panel</p>
                    </div>
                    <!-- Close button for Mobile drawer -->
                    <button @click="sidebarOpen = false" class="md:hidden text-stone-400 hover:text-white cursor-pointer p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-grow p-4 space-y-1 overflow-y-auto">
                    <!-- Tab: Pengantin -->
                    <button wire:click="$set('activeTab', 'pengantin'); sidebarOpen = false;"
                        class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-xs uppercase tracking-wider font-bold transition-all cursor-pointer text-left"
                        :class="'{{ $activeTab }}' === 'pengantin' ? 'bg-[#e6ca65] text-stone-950 shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-900/60'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        <span>Pengantin</span>
                    </button>

                    <!-- Tab: Acara & Lokasi -->
                    <button wire:click="$set('activeTab', 'acara'); sidebarOpen = false;"
                        class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-xs uppercase tracking-wider font-bold transition-all cursor-pointer text-left"
                        :class="'{{ $activeTab }}' === 'acara' ? 'bg-[#e6ca65] text-stone-950 shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-900/60'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span>Acara &amp; Lokasi</span>
                    </button>

                    <!-- Tab: Konten & Musik -->
                    <button wire:click="$set('activeTab', 'konten'); sidebarOpen = false;"
                        class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-xs uppercase tracking-wider font-bold transition-all cursor-pointer text-left"
                        :class="'{{ $activeTab }}' === 'konten' ? 'bg-[#e6ca65] text-stone-950 shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-900/60'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 0v15m0-15l-10.5 3m10.5-3V8.25m-10.5 3v15M9 12H4.5A1.5 1.5 0 003 13.5v5A1.5 1.5 0 004.5 20H9m0-8v8" />
                        </svg>
                        <span>Konten &amp; Musik</span>
                    </button>

                    <!-- Tab: Undang Tamu -->
                    <button wire:click="$set('activeTab', 'tamu'); sidebarOpen = false;"
                        class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-xs uppercase tracking-wider font-bold transition-all cursor-pointer text-left"
                        :class="'{{ $activeTab }}' === 'tamu' ? 'bg-[#e6ca65] text-stone-950 shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-900/60'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5a3 3 0 11-6 0 3 3 0 016 0zM12 11.25a3 3 0 11-6 0 3 3 0 016 0zM21 18.75v-1.125A3.375 3.375 0 0017.625 14.25H12.75a3.375 3.375 0 00-3.375 3.375V18.75m11.25 0H9" />
                        </svg>
                        <span>Undang Tamu ({{ count($this->getGuests()) }})</span>
                    </button>

                    <!-- Tab: Daftar Doa Restu -->
                    <button wire:click="$set('activeTab', 'doa'); sidebarOpen = false;"
                        class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-xs uppercase tracking-wider font-bold transition-all cursor-pointer text-left"
                        :class="'{{ $activeTab }}' === 'doa' ? 'bg-[#e6ca65] text-stone-950 shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-900/60'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.028z" />
                        </svg>
                        <span>Daftar Doa ({{ count($this->getGreetings()) }})</span>
                    </button>
                </nav>

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-stone-850 space-y-2 shrink-0">
                    <a href="{{ url('/') }}" target="_blank"
                        class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 bg-stone-900 border border-stone-800 text-stone-250 text-xs font-semibold rounded-xl hover:bg-stone-850 transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-[#e6ca65]">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3" />
                        </svg>
                        <span>Lihat Website</span>
                    </a>
                    <button wire:click="logout"
                        class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 bg-rose-950/30 border border-rose-900/20 text-rose-400 hover:bg-rose-950 hover:text-white text-xs font-semibold rounded-xl transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-grow flex flex-col min-h-screen overflow-x-hidden">
                <!-- Top Mobile Header -->
                <header class="bg-stone-950 border-b border-stone-850 p-4 flex justify-between items-center md:px-8 shrink-0">
                    <div class="flex items-center space-x-3">
                        <!-- Hamburger button -->
                        <button @click="sidebarOpen = true" class="md:hidden p-1.5 text-stone-400 hover:text-white bg-stone-900 border border-stone-800 rounded-lg cursor-pointer" aria-label="Open Sidebar">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-sm font-bold text-white capitalize md:text-base">
                                Menu: {{ $activeTab === 'tamu' ? 'Undang Tamu' : ($activeTab === 'doa' ? 'Kelola Doa' : $activeTab) }}
                            </h2>
                        </div>
                    </div>
                    <div class="text-[10px] text-stone-500 font-medium md:text-xs">
                        Undangan: <span class="text-[#e6ca65]">{{ $invitation->bride_name_short }} &amp; {{ $invitation->groom_name_short }}</span>
                    </div>
                </header>

                <!-- Page Content Body -->
                <main class="flex-grow p-4 md:p-8 max-w-4xl w-full mx-auto space-y-6">
                    
                    <!-- Success Message Alert -->
                    @if (session()->has('success_message'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                            class="p-4 bg-[#e6ca65]/10 border border-[#e6ca65]/35 text-[#e6ca65] text-xs font-semibold rounded-xl flex justify-between items-center shadow-lg">
                            <span class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ session('success_message') }}</span>
                            </span>
                            <button @click="show = false" class="text-stone-400 hover:text-white cursor-pointer">&times;</button>
                        </div>
                    @endif

                    <!-- Render Content depending on active tab -->
                    <!-- 1. Tab: Pengantin -->
                    @if ($activeTab === 'pengantin')
                        <form wire:submit.prevent="saveSettings" class="space-y-6">
                            <div class="glass-card rounded-2xl p-6 space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Informasi Mempelai Wanita (Bride)</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Nama Panggilan</label>
                                        <input type="text" wire:model="bride_name_short" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                        @error('bride_name_short') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Nama Lengkap</label>
                                        <input type="text" wire:model="bride_name_full" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                        @error('bride_name_full') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Nama Ayah</label>
                                        <input type="text" wire:model="bride_father" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Nama Ibu</label>
                                        <input type="text" wire:model="bride_mother" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card rounded-2xl p-6 space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Informasi Mempelai Pria (Groom)</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Nama Panggilan</label>
                                        <input type="text" wire:model="groom_name_short" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                        @error('groom_name_short') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Nama Lengkap</label>
                                        <input type="text" wire:model="groom_name_full" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                        @error('groom_name_full') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Nama Ayah</label>
                                        <input type="text" wire:model="groom_father" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Nama Ibu</label>
                                        <input type="text" wire:model="groom_mother" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-3.5 bg-gold-gradient hover:opacity-95 text-stone-950 font-bold uppercase tracking-wider text-xs rounded-xl shadow-lg shadow-[#e6ca65]/10 cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </form>
                    @endif

                    <!-- 2. Tab: Acara & Lokasi -->
                    @if ($activeTab === 'acara')
                        <form wire:submit.prevent="saveSettings" class="space-y-6">
                            <div class="glass-card rounded-2xl p-6 space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Jadwal & Waktu</h3>
                                <div class="space-y-1">
                                    <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Tanggal & Jam Acara Utama (Countdown)</label>
                                    <input type="datetime-local" wire:model="event_date" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                    @error('event_date') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Jam Akad Nikah (Opsional)</label>
                                        <input type="text" wire:model="akad_time" placeholder="Contoh: 09:00 - 10:30 WIB (Kosongkan jika tidak ada)" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                        @error('akad_time') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Jam Resepsi</label>
                                        <input type="text" wire:model="resepsi_time" placeholder="Contoh: 11:00 - 14:00 WIB" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                        @error('resepsi_time') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card rounded-2xl p-6 space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Lokasi & Navigasi</h3>
                                <div class="space-y-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Lokasi Akad (Opsional)</label>
                                        <input type="text" wire:model="akad_location" placeholder="Nama Masjid / Tempat Akad (Kosongkan jika tidak ada)" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                        <span class="text-[9px] text-stone-500">Kosongkan kolom ini jika acara hanya berupa Resepsi.</span>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Lokasi Resepsi</label>
                                        <input type="text" wire:model="resepsi_location" placeholder="Nama Gedung / Tempat Resepsi" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Link Google Maps (Tombol Navigasi)</label>
                                        <input type="text" wire:model="maps_url" placeholder="https://maps.app.goo.gl/..." class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                        @error('maps_url') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Interactive Leaflet Map Picker -->
                                    <div class="space-y-4 pt-4 border-t border-stone-850" x-data="{
                                        lat: @entangle('latitude'),
                                        lng: @entangle('longitude'),
                                        map: null,
                                        marker: null,
                                        initMap() {
                                            let initialLat = this.lat ? parseFloat(this.lat) : -6.2276077;
                                            let initialLng = this.lng ? parseFloat(this.lng) : 106.7975416;

                                            this.map = L.map('admin-map-picker', {
                                                scrollWheelZoom: false
                                            }).setView([initialLat, initialLng], 14);

                                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                attribution: '&copy; OpenStreetMap'
                                            }).addTo(this.map);

                                            this.marker = L.marker([initialLat, initialLng], {
                                                draggable: true
                                            }).addTo(this.map);

                                            this.marker.on('dragend', (e) => {
                                                let pos = this.marker.getLatLng();
                                                this.lat = pos.lat.toFixed(7);
                                                this.lng = pos.lng.toFixed(7);
                                            });

                                            this.map.on('click', (e) => {
                                                this.marker.setLatLng(e.latlng);
                                                this.lat = e.latlng.lat.toFixed(7);
                                                this.lng = e.latlng.lng.toFixed(7);
                                            });

                                            this.$watch('lat', value => {
                                                if (this.marker && value) {
                                                    let val = parseFloat(value);
                                                    if (!isNaN(val) && val !== this.marker.getLatLng().lat) {
                                                        this.marker.setLatLng([val, this.marker.getLatLng().lng]);
                                                        this.map.panTo([val, this.marker.getLatLng().lng]);
                                                    }
                                                }
                                            });

                                            this.$watch('lng', value => {
                                                if (this.marker && value) {
                                                    let val = parseFloat(value);
                                                    if (!isNaN(val) && val !== this.marker.getLatLng().lng) {
                                                        this.marker.setLatLng([this.marker.getLatLng().lat, val]);
                                                        this.map.panTo([this.marker.getLatLng().lat, val]);
                                                    }
                                                }
                                            });
                                        }
                                    }" x-init="$nextTick(() => { initMap(); })" wire:ignore>
                                        <div class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Pin Koordinat Lokasi Acara</div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-1">
                                                <label class="text-[9px] uppercase font-bold text-stone-500 tracking-wider">Latitude</label>
                                                <input type="text" x-model="lat" class="w-full px-4 py-2 bg-stone-900/60 border border-stone-850 rounded-lg text-xs text-white focus:outline-none focus:border-[#e6ca65]">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="text-[9px] uppercase font-bold text-stone-500 tracking-wider">Longitude</label>
                                                <input type="text" x-model="lng" class="w-full px-4 py-2 bg-stone-900/60 border border-stone-850 rounded-lg text-xs text-white focus:outline-none focus:border-[#e6ca65]">
                                            </div>
                                        </div>

                                        <div id="admin-map-picker" class="w-full h-64 rounded-xl border border-stone-850 mt-2" style="z-index: 10;"></div>
                                        <span class="text-[9px] text-stone-500 block">Petunjuk: Geser pin pada peta atau klik di mana saja pada peta untuk memperbarui koordinat latitude &amp; longitude secara otomatis.</span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-3.5 bg-gold-gradient hover:opacity-95 text-stone-950 font-bold uppercase tracking-wider text-xs rounded-xl shadow-lg shadow-[#e6ca65]/10 cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </form>
                    @endif

                    <!-- 3. Tab: Konten & Musik -->
                    @if ($activeTab === 'konten')
                        <form wire:submit.prevent="saveSettings" class="space-y-6">
                            <div class="glass-card rounded-2xl p-6 space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Pilihan Tema Undangan (Desain)</h3>
                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Pilih Template</label>
                                    <select wire:model="template" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65] cursor-pointer">
                                        <option value="elegant">Template 1: Elegant Emerald &amp; Gold (Dark Theme)</option>
                                        <option value="genz">Template 2: Modern Rustic Forest (Gen Z Vibe - Light Theme)</option>
                                        <option value="pastel">Template 3: Pastel Sakura (Minimalist Blossom - Soft Pink Theme)</option>
                                        <option value="retro">Template 4: Retro Vintage (Funky Terracotta - Hipster Theme)</option>
                                    </select>
                                    <span class="text-[10px] text-stone-500 block">Pilih gaya visual, tata letak, dan animasi yang ingin digunakan untuk halaman undangan utama.</span>
                                    @error('template') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="glass-card rounded-2xl p-6 space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Konten Kalimat & Sambutan</h3>
                                <div class="space-y-1">
                                    <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Kata Sambutan Pembuka</label>
                                    <textarea wire:model="welcome_message" rows="5" class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65] resize-none"></textarea>
                                    @error('welcome_message') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="glass-card rounded-2xl p-6 space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Musik Latar (Background Music)</h3>
                                <div class="space-y-1">
                                    <label class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Link Musik Latar (URL MP3 / Link YouTube)</label>
                                    <input type="text" wire:model="bg_music_url" placeholder="Contoh: /audio/sah.mp3 atau https://www.youtube.com/watch?v=..." class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                    <span class="text-[10px] text-stone-500 block mt-1">Masukkan URL file MP3 (lokal/internet) atau salin link video YouTube. Sistem akan memainkannya secara otomatis.</span>
                                    @error('bg_music_url') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <button type="submit" class="w-full py-3.5 bg-gold-gradient hover:opacity-95 text-stone-950 font-bold uppercase tracking-wider text-xs rounded-xl shadow-lg shadow-[#e6ca65]/10 cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </form>
                    @endif

                    <!-- 4. Tab: Undang Tamu -->
                    @if ($activeTab === 'tamu')
                        <div class="space-y-6">
                            <!-- Add Guest Form -->
                            <div class="glass-card rounded-2xl p-6 space-y-4">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Buat Link Undangan Tamu Baru</h3>
                                <form wire:submit.prevent="addGuest" class="flex flex-col sm:flex-row gap-3">
                                    <div class="flex-grow space-y-1">
                                        <input type="text" wire:model="newGuestName" placeholder="Ketik nama tamu (contoh: Bpk. Budi Santoso & Istri)" 
                                            class="w-full px-4 py-2.5 bg-stone-900/60 border border-stone-800 rounded-lg text-sm text-white focus:outline-none focus:border-[#e6ca65]">
                                        @error('newGuestName') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="submit" class="px-6 py-2.5 bg-[#e6ca65] hover:opacity-95 text-stone-950 font-bold uppercase tracking-wider text-xs rounded-lg shadow-md cursor-pointer whitespace-nowrap">
                                        Tambah Tamu
                                    </button>
                                </form>
                            </div>

                            <!-- Guest List Table -->
                            <div class="glass-card rounded-2xl p-6 space-y-4">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Daftar Link Undangan Tamu</h3>
                                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 scrollbar-custom">
                                    @forelse ($this->getGuests() as $gst)
                                        @php
                                            $guestUrl = url('/?to=' . urlencode($gst->name));
                                            $waMessage = "Halo *{$gst->name}*,\n\nTanpa mengurangi rasa hormat, perkenankan kami mengundang Anda untuk menghadiri acara pernikahan kami.\n\nBerikut detail undangan & lokasi acara kami:\n{$guestUrl}\n\nMerupakan suatu kehormatan dan kebahagiaan bagi kami apabila Anda berkenan hadir. Terima kasih.";
                                            $waUrl = "https://api.whatsapp.com/send?text=" . rawurlencode($waMessage);
                                        @endphp
                                        <div class="p-4 bg-stone-900/60 border border-stone-800/80 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div class="space-y-2 flex-grow">
                                                <h4 class="font-bold text-sm text-white tracking-wide">{{ $gst->name }}</h4>
                                                <div class="flex items-center space-x-1.5 w-full">
                                                    <input type="text" value="{{ $guestUrl }}" readonly 
                                                        class="w-full px-3 py-1.5 bg-stone-950/80 border border-stone-850 rounded text-stone-400 text-[10px] sm:text-xs focus:outline-none select-all">
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2 shrink-0">
                                                <!-- Alpine-based Copy Button -->
                                                <button @click="navigator.clipboard.writeText('{{ $guestUrl }}'); $el.innerText = 'Tersalin!'; $el.classList.add('bg-emerald-950', 'text-emerald-400', 'border-emerald-500/20'); setTimeout(() => { $el.innerText = 'Salin'; $el.classList.remove('bg-emerald-950', 'text-emerald-400', 'border-emerald-500/20') }, 2000)"
                                                    class="px-3.5 py-2 bg-stone-950 border border-stone-800 text-stone-300 text-xs font-semibold rounded-lg hover:bg-stone-850 hover:text-white transition-colors cursor-pointer">
                                                    Salin
                                                </button>
                                                
                                                <!-- WhatsApp Share Button -->
                                                <a href="{{ $waUrl }}" target="_blank"
                                                    class="px-3.5 py-2 bg-emerald-950/40 border border-emerald-900/30 text-emerald-400 text-xs font-semibold rounded-lg hover:bg-emerald-900 hover:text-white transition-colors flex items-center space-x-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                      <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.949h.004c4.368 0 7.926-3.558 7.93-7.93a7.867 7.867 0 0 0-2.332-5.593zm-2.228 10.3c-.195.555-1.11 1.064-1.624 1.11-.47.04-1.09.214-3.223-.668-2.73-1.134-4.502-3.905-4.639-4.089-.137-.185-1.022-1.357-1.022-2.588 0-1.23.643-1.834.87-2.083.227-.249.493-.312.658-.312.164 0 .328.001.47.008.15.007.35-.057.548.422.198.487.676 1.652.735 1.77.058.117.098.252.019.408-.078.156-.118.252-.234.388-.117.137-.248.305-.355.409-.12.118-.245.247-.105.485.14.238.622 1.022 1.332 1.654.914.814 1.685 1.065 1.923 1.18.239.116.378.099.516-.058.138-.156.598-.698.756-.936.16-.238.319-.2.534-.12.215.08 1.36.643 1.593.758.233.115.388.17.445.269.057.099.057.576-.138 1.13c-.195.554-1.154 1.12-1.696 1.125h-.008z"/>
                                                    </svg>
                                                    <span>WA</span>
                                                </a>

                                                <!-- Delete Button -->
                                                <button wire:click="deleteGuest({{ $gst->id }})" wire:confirm="Hapus tamu ini dari daftar?"
                                                    class="p-2 bg-rose-950/40 hover:bg-rose-950 text-rose-400 hover:text-white rounded-lg transition-colors cursor-pointer"
                                                    aria-label="Hapus Tamu">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-10 border border-dashed border-stone-850 rounded-2xl text-stone-500 text-xs">
                                            Belum ada daftar tamu. Tambahkan tamu di atas untuk mulai membuat link.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- 5. Tab: Daftar Doa Restu (RSVP) -->
                    @if ($activeTab === 'doa')
                        <div class="glass-card rounded-2xl p-6 space-y-6">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#e6ca65] border-b border-stone-850 pb-2">Kelola Doa Restu & Ucapan Tamu</h3>
                            
                            <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 scrollbar-custom">
                                @forelse ($this->getGreetings() as $greet)
                                    <div class="p-4 bg-stone-900/70 border border-stone-800/80 rounded-xl flex justify-between items-start space-x-4">
                                        <div class="space-y-1.5 flex-grow">
                                            <div class="flex items-center space-x-2 flex-wrap">
                                                <h4 class="font-bold text-sm text-white">{{ $greet->name }}</h4>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border"
                                                    :class="{
                                                        'bg-emerald-950/50 border-emerald-500/20 text-emerald-400': '{{ $greet->status }}' === 'hadir',
                                                        'bg-rose-950/50 border-rose-500/20 text-rose-400': '{{ $greet->status }}' === 'tidak_hadir',
                                                        'bg-amber-950/50 border-amber-500/20 text-amber-400': '{{ $greet->status }}' === 'ragu'
                                                    }">
                                                    @if ($greet->status === 'hadir')
                                                        Hadir
                                                    @elseif ($greet->status === 'tidak_hadir')
                                                        Absen
                                                    @else
                                                        Ragu
                                                    @endif
                                                </span>
                                                <span class="text-[10px] text-stone-500">{{ $greet->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-stone-300 text-xs leading-relaxed whitespace-pre-line">{{ $greet->message }}</p>
                                        </div>
                                        <button wire:click="deleteGreeting({{ $greet->id }})" 
                                            wire:confirm="Apakah Anda yakin ingin menghapus ucapan ini?"
                                            class="p-2 bg-rose-950/40 hover:bg-rose-950 text-rose-400 hover:text-white rounded-lg transition-colors cursor-pointer"
                                            aria-label="Hapus Ucapan">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                @empty
                                    <div class="text-center py-10 border border-dashed border-stone-850 rounded-2xl text-stone-500 text-xs">
                                        Belum ada ucapan yang masuk.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    @else
        <!-- Secure Admin Login Screen -->
        <div class="min-h-screen flex items-center justify-center bg-[#030907] px-4">
            <div class="absolute inset-4 border border-[#e6ca65]/10 rounded-2xl pointer-events-none z-10"></div>
            <div class="absolute inset-6 border border-[#e6ca65]/5 rounded-xl pointer-events-none z-10"></div>
            
            <div class="w-full max-w-sm glass-card rounded-3xl p-8 space-y-6 shadow-2xl relative z-20">
                <div class="text-center space-y-2">
                    <div class="w-12 h-12 rounded-full border border-[#e6ca65]/35 bg-[#e6ca65]/5 flex items-center justify-center mx-auto text-[#e6ca65]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <h2 class="font-serif text-2xl font-light text-white tracking-widest uppercase pt-2">Login Admin</h2>
                    <p class="text-xs text-stone-400">Masuk untuk mengelola website undangan</p>
                </div>

                @error('loginError')
                    <div class="p-3.5 bg-rose-950/40 border border-rose-500/20 text-rose-300 text-xs font-semibold rounded-xl text-center">
                        {{ $message }}
                    </div>
                @enderror

                <form wire:submit.prevent="login" class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="email" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Email</label>
                        <input type="email" id="email" wire:model="email"
                            class="w-full px-4 py-2.5 bg-stone-900/50 border border-stone-800 rounded-xl text-white text-sm focus:outline-none focus:border-[#e6ca65]"
                            placeholder="admin@wedding.com" required>
                        @error('email') <span class="text-xs text-red-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Password</label>
                        <input type="password" id="password" wire:model="password"
                            class="w-full px-4 py-2.5 bg-stone-900/50 border border-stone-800 rounded-xl text-white text-sm focus:outline-none focus:border-[#e6ca65]"
                            placeholder="••••••••" required>
                        @error('password') <span class="text-xs text-red-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-gold-gradient text-stone-950 font-bold tracking-[0.25em] text-xs uppercase rounded-xl shadow-xl shadow-[#e6ca65]/10 cursor-pointer flex items-center justify-center space-x-2">
                        <svg wire:loading wire:target="login" class="animate-spin -ml-1 mr-2 h-4 w-4 text-stone-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Masuk</span>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>