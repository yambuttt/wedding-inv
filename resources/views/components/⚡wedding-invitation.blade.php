<?php

use Livewire\Component;
use App\Models\Invitation;
use App\Models\Greeting;
use Livewire\Attributes\Validate;

new class extends Component
{
    public Invitation $invitation;
    public $guestNameQuery = ''; // from URL ?to=

    #[Validate('required|min:3|max:100', message: [
        'required' => 'Nama wajib diisi.',
        'min' => 'Nama minimal 3 karakter.',
        'max' => 'Nama maksimal 100 karakter.'
    ])]
    public $name = '';

    #[Validate('required|min:5|max:1000', message: [
        'required' => 'Ucapan wajib diisi.',
        'min' => 'Ucapan minimal 5 karakter.',
        'max' => 'Ucapan maksimal 1000 karakter.'
    ])]
    public $message = '';

    #[Validate('required|in:hadir,tidak_hadir,ragu', message: [
        'required' => 'Status kehadiran wajib dipilih.'
    ])]
    public $status = 'hadir';

    public function mount($slug = 'sari-raju')
    {
        $this->invitation = Invitation::where('slug', $slug)->firstOrFail();
        $this->guestNameQuery = request()->query('to', '');
        
        // Pre-fill name if "to" query param is present
        if ($this->guestNameQuery) {
            $this->name = $this->guestNameQuery;
        }
    }

    public function submitGreeting()
    {
        $this->validate();

        Greeting::create([
            'invitation_id' => $this->invitation->id,
            'name' => $this->name,
            'message' => $this->message,
            'status' => $this->status,
        ]);

        $this->reset(['message']); // keep name filled for convenience

        session()->flash('success_message', 'Terima kasih atas ucapan dan doa restu Anda!');
    }

    public function getGreetings()
    {
        return $this->invitation->greetings()->latest()->get();
    }
};
?>

@php
    $hasAkad = !empty($invitation->akad_time) || !empty($invitation->akad_location);
    $hasResepsi = !empty($invitation->resepsi_time) || !empty($invitation->resepsi_location);
    
    $wrapperClass = 'bg-[#040d0a] text-stone-100 font-sans selection:bg-[#e6ca65]/30 selection:text-white';
    if ($invitation->template === 'genz') {
        $wrapperClass = 'bg-paper-texture text-[#1e2522] selection:bg-[#2e4d3c]/20 selection:text-[#1e2522]';
    } elseif ($invitation->template === 'pastel') {
        $wrapperClass = 'bg-sakura-texture text-[#4e3a3d] font-montserrat selection:bg-[#d48895]/20 selection:text-[#4e3a3d]';
    } elseif ($invitation->template === 'retro') {
        $wrapperClass = 'bg-retro-pattern text-[#33221c] font-outfit selection:bg-[#be5a38]/20 selection:text-[#33221c]';
    }

    $isYoutube = str_contains($invitation->bg_music_url, 'youtube.com') || str_contains($invitation->bg_music_url, 'youtu.be');
    $youtubeId = '';
    if ($isYoutube) {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $invitation->bg_music_url, $match);
        $youtubeId = $match[1] ?? '';
    }
@endphp

<div x-data="{
        isOpen: false,
        isCoverHidden: false,
        isPlaying: false,
        isYoutube: {{ $isYoutube ? 'true' : 'false' }},
        openInvitation() {
            this.isOpen = true;
            this.playMusic();
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                this.isCoverHidden = true;
            }, 1000);
        },
        playMusic() {
            if (this.isYoutube) {
                if (typeof ytPlayer !== 'undefined' && ytPlayer.playVideo) {
                    ytPlayer.playVideo();
                    this.isPlaying = true;
                } else {
                    setTimeout(() => this.playMusic(), 300);
                }
            } else {
                let audio = $refs.bgAudio;
                if (audio) {
                    audio.play().then(() => {
                        this.isPlaying = true;
                    }).catch(err => {
                        console.log('Music play block:', err);
                    });
                }
            }
        },
        toggleMusic() {
            if (this.isYoutube) {
                if (typeof ytPlayer !== 'undefined') {
                    if (this.isPlaying) {
                        ytPlayer.pauseVideo();
                        this.isPlaying = false;
                    } else {
                        ytPlayer.playVideo();
                        this.isPlaying = true;
                    }
                }
            } else {
                let audio = $refs.bgAudio;
                if (audio) {
                    if (this.isPlaying) {
                        audio.pause();
                        this.isPlaying = false;
                    } else {
                        audio.play();
                        this.isPlaying = true;
                    }
                }
            }
        }
    }"
    x-init="
        document.body.style.overflow = 'hidden';
    "
    class="relative min-h-screen {{ $wrapperClass }} overflow-hidden">

    <!-- Audio Element -->
    @if ($isYoutube && $youtubeId)
        <div id="yt-player" class="hidden"></div>
        <script>
            var tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            var ytPlayer;
            function onYouTubeIframeAPIReady() {
                ytPlayer = new YT.Player('yt-player', {
                    height: '0',
                    width: '0',
                    videoId: '{{ $youtubeId }}',
                    playerVars: {
                        'autoplay': 0,
                        'controls': 0,
                        'loop': 1,
                        'playlist': '{{ $youtubeId }}',
                        'mute': 0
                    }
                });
            }
        </script>
    @elseif (!$isYoutube)
        <audio x-ref="bgAudio" loop preload="auto">
            <source src="{{ $invitation->bg_music_url }}" type="audio/mpeg">
        </audio>
    @endif

    <!-- Floating Audio Control Button -->
    <button @click="toggleMusic()" 
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 scale-70 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full {{ $invitation->template === 'genz' ? 'bg-white border-[#2e4d3c]/50 text-[#2e4d3c] shadow-md' : 'bg-stone-900/90 border-[#e6ca65]/50 text-[#e6ca65] shadow-lg shadow-black/50' }} border flex items-center justify-center cursor-pointer transition-transform duration-300 hover:scale-105 active:scale-95"
        :class="isPlaying ? 'animate-spin [animation-duration:10s]' : ''"
        aria-label="Toggle Background Music">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 0v15m0-15l-10.5 3m10.5-3V8.25m-10.5 3v15M9 12H4.5A1.5 1.5 0 003 13.5v5A1.5 1.5 0 004.5 20H9m0-8v8m10.5-11.25v11.25M19.5 9h-4.5A1.5 1.5 0 0013.5 10.5v5a1.5 1.5 0 001.5 20h4.5m0-11.25v11.25" />
        </svg>
    </button>

    @if ($invitation->template === 'genz')
        <!-- ============================================ -->
        <!-- TEMPLATE 2: MODERN GEN Z RUSTIC FOREST       -->
        <!-- ============================================ -->
        
        <!-- Floating Background Particles (Simulating falling pine needles/leaves) -->
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
            <div class="absolute top-[20%] left-[10%] w-2.5 h-2 rounded-full bg-[#2e4d3c]/15 rotate-12 blur-[0.5px] animate-particle-1"></div>
            <div class="absolute top-[40%] right-[15%] w-3.5 h-3 rounded-full bg-[#2e4d3c]/10 blur-[1px] animate-particle-2"></div>
            <div class="absolute bottom-[30%] left-[25%] w-2 h-2.5 rounded-full bg-[#2e4d3c]/12 -rotate-45 blur-[0.5px] animate-particle-3"></div>
            <div class="absolute top-[70%] left-[80%] w-2 h-1.5 rounded-full bg-[#2e4d3c]/15 rotate-45 blur-[0.5px] animate-particle-1"></div>
        </div>

        <!-- 1. Opening Cover (Envelope Gate) -->
        <div x-show="!isCoverHidden" 
            class="fixed inset-0 z-50 overflow-hidden bg-transparent">
            
            <!-- Vintage double border frame -->
            <div class="absolute inset-4 border-2 border-[#2e4d3c] rounded-2xl pointer-events-none z-10"
                :class="isOpen ? 'opacity-0 transition-opacity duration-1000' : 'opacity-100'"></div>
            <div class="absolute inset-6 border border-dashed border-[#2e4d3c]/30 rounded-xl pointer-events-none z-10"
                :class="isOpen ? 'opacity-0 transition-opacity duration-1000' : 'opacity-100'"></div>

            <!-- Top Half of Envelope -->
            <div class="absolute inset-x-0 top-0 h-1/2 bg-[#faf7f2] bg-paper-texture border-b border-[#2e4d3c]/40 transition-transform duration-1000 ease-in-out flex flex-col justify-end items-center pb-12"
                :class="isOpen ? '-translate-y-full' : 'translate-y-0'">
                <div class="font-special-elite text-[#2e4d3c] uppercase tracking-[0.25em] text-[10px] md:text-xs font-semibold mb-3">Wedding Invitation</div>
                <div class="font-playfair text-4xl md:text-6xl text-[#1e2522] tracking-wider font-bold text-center px-4">
                    {{ $invitation->bride_name_short }} &amp; {{ $invitation->groom_name_short }}
                </div>
            </div>

            <!-- Bottom Half of Envelope -->
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-[#faf7f2] bg-paper-texture border-t border-[#2e4d3c]/40 transition-transform duration-1000 ease-in-out flex flex-col justify-start items-center pt-12"
                :class="isOpen ? 'translate-y-full' : 'translate-y-0'">
                
                <div class="font-special-elite text-stone-600 text-[10px] uppercase tracking-[0.2em] mb-2">Special Invitation For:</div>
                <div class="px-6 py-2.5 rounded-xl bg-white/70 border border-[#2e4d3c]/20 shadow-sm mb-6 max-w-xs text-center">
                    <span class="text-[#2e4d3c] font-signature text-xl tracking-wide block">
                        {{ $guestNameQuery ?: 'Tamu Undangan' }}
                    </span>
                </div>

                <!-- Vintage Forest Wax Seal Open Button -->
                <button @click="openInvitation()" 
                    class="relative z-20 px-8 py-4 bg-[#2e4d3c] hover:bg-[#1f3529] text-white font-bold tracking-[0.2em] text-xs uppercase rounded-full shadow-lg border border-[#fff]/20 cursor-pointer transition-all duration-300 flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>Buka Undangan</span>
                </button>
            </div>

            <!-- Center Stamp Motif -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-28 h-28 rounded-full bg-[#faf7f2] border-2 border-[#2e4d3c] flex flex-col items-center justify-center z-10 transition-all duration-700 pointer-events-none shadow-md"
                :class="isOpen ? 'opacity-0 scale-50' : 'opacity-100 scale-100'">
                <div class="w-[94%] h-[94%] rounded-full border border-dashed border-[#2e4d3c]/40 flex items-center justify-center">
                    <span class="font-playfair text-2xl text-[#2e4d3c] font-bold tracking-widest">{{ substr($invitation->bride_name_short, 0, 1) }}{{ substr($invitation->groom_name_short, 0, 1) }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Main Content View -->
        <div x-show="isOpen" 
            x-transition:enter="transition opacity ease-out duration-1000 delay-500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="max-w-md mx-auto px-4 md:max-w-2xl lg:max-w-3xl relative z-10 space-y-24 pb-24 pt-10">
            
            <!-- Section 1: Hero Banner -->
            <section class="min-h-screen flex flex-col justify-center items-center text-center space-y-8 relative">
                <div class="absolute top-4 left-4 w-6 h-6 border-t border-l border-[#2e4d3c]/20 pointer-events-none"></div>
                <div class="absolute top-4 right-4 w-6 h-6 border-t border-r border-[#2e4d3c]/20 pointer-events-none"></div>
                <div class="absolute bottom-4 left-4 w-6 h-6 border-b border-l border-[#2e4d3c]/20 pointer-events-none"></div>
                <div class="absolute bottom-4 right-4 w-6 h-6 border-b border-r border-[#2e4d3c]/20 pointer-events-none"></div>

                <div class="font-special-elite text-xs tracking-[0.25em] text-[#2e4d3c] font-semibold">Together with our parents</div>
                
                <div class="relative py-8 px-6 w-full">
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-[1px] bg-[#2e4d3c]/30"></div>
                    
                    <h1 class="font-playfair text-5xl md:text-7xl font-black tracking-wide text-[#1e2522] flex flex-col space-y-4">
                        <span class="text-xs md:text-sm tracking-[0.35em] uppercase font-special-elite text-stone-500 mb-2">The Wedding of</span>
                        <span class="text-[#2e4d3c] font-signature text-6xl md:text-8xl leading-none py-2 capitalize">{{ $invitation->bride_name_short }}</span>
                        <span class="font-special-elite text-xl tracking-[0.2em] lowercase italic my-1">&amp;</span>
                        <span class="tracking-wider leading-none">{{ $invitation->groom_name_short }}</span>
                    </h1>
                    
                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-32 h-[1px] bg-[#2e4d3c]/30"></div>
                </div>

                <div class="font-special-elite text-sm text-stone-600 uppercase tracking-[0.2em]">
                    <p>{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
                </div>

                <!-- Countdown Timer -->
                <div x-data="{
                        target: new Date('{{ $invitation->event_date->toIso8601String() }}').getTime(),
                        days: 0,
                        hours: 0,
                        minutes: 0,
                        seconds: 0,
                        update() {
                            let diff = this.target - new Date().getTime();
                            if (diff < 0) return;
                            this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
                        }
                    }"
                    x-init="update(); setInterval(() => update(), 1000)"
                    class="grid grid-cols-4 gap-3 max-w-sm mx-auto pt-4 w-full px-2">
                    <!-- Days -->
                    <div class="flex flex-col items-center bg-white/70 border border-[#2e4d3c]/20 rounded-2xl p-3 shadow-sm backdrop-blur-md">
                        <span class="text-2xl font-bold font-special-elite text-[#2e4d3c]" x-text="days">0</span>
                        <span class="text-[9px] font-special-elite uppercase tracking-widest text-stone-500 mt-1">Hari</span>
                    </div>
                    <!-- Hours -->
                    <div class="flex flex-col items-center bg-white/70 border border-[#2e4d3c]/20 rounded-2xl p-3 shadow-sm backdrop-blur-md">
                        <span class="text-2xl font-bold font-special-elite text-[#2e4d3c]" x-text="hours">0</span>
                        <span class="text-[9px] font-special-elite uppercase tracking-widest text-stone-500 mt-1">Jam</span>
                    </div>
                    <!-- Minutes -->
                    <div class="flex flex-col items-center bg-white/70 border border-[#2e4d3c]/20 rounded-2xl p-3 shadow-sm backdrop-blur-md">
                        <span class="text-2xl font-bold font-special-elite text-[#2e4d3c]" x-text="minutes">0</span>
                        <span class="text-[9px] font-special-elite uppercase tracking-widest text-stone-500 mt-1">Menit</span>
                    </div>
                    <!-- Seconds -->
                    <div class="flex flex-col items-center bg-white/70 border border-[#2e4d3c]/20 rounded-2xl p-3 shadow-sm backdrop-blur-md">
                        <span class="text-2xl font-bold font-special-elite text-[#2e4d3c]" x-text="seconds">0</span>
                        <span class="text-[9px] font-special-elite uppercase tracking-widest text-stone-500 mt-1">Detik</span>
                    </div>
                </div>

                <!-- Custom Vector Pine Trees Divider -->
                <div class="w-full pt-6">
                    <div class="py-4 flex justify-center items-center opacity-85">
                        <svg class="w-48 h-16 text-[#2e4d3c]" viewBox="0 0 200 60" fill="currentColor">
                            <polygon points="40,50 30,35 35,35 25,20 30,20 20,5 30,5 30,0 32,0 32,5 42,5 32,20 37,20 27,35 32,35 22,50" />
                            <polygon points="100,55 85,38 92,38 78,22 85,22 72,5 82,5 82,0 84,0 84,5 94,5 84,22 91,22 77,38 84,38 69,55" />
                            <polygon points="160,50 150,35 155,35 145,20 150,20 140,5 150,5 150,0 152,0 152,5 162,5 152,20 157,20 147,35 152,35 142,50" />
                            <path d="M10 52 Q 50 48, 100 52 T 190 52" stroke="currentColor" stroke-width="1.5" fill="none" />
                        </svg>
                    </div>
                </div>
            </section>

            <!-- Section 2: Ayat / Welcome Quote -->
            <section class="space-y-12 text-center py-6">
                <div class="max-w-md mx-auto vintage-card rounded-3xl p-8 space-y-6 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-28 h-28 opacity-[0.04] text-[#2e4d3c] pointer-events-none">
                        <svg viewBox="0 0 100 100" fill="currentColor">
                            <polygon points="50,10 20,50 40,50 15,80 85,80 60,50 80,50" />
                        </svg>
                    </div>

                    <span class="text-5xl text-[#2e4d3c]/20 font-playfair font-black leading-none block -mt-2">“</span>
                    
                    <p class="text-stone-700 font-playfair italic text-sm leading-relaxed relative z-10 px-2">
                        "Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang."
                    </p>
                    <div class="text-xs font-special-elite text-[#2e4d3c] font-bold tracking-widest">— AR-RUM: 21</div>
                </div>

                <div class="text-stone-700 font-special-elite text-xs max-w-sm mx-auto leading-relaxed px-4 pt-6">
                    {{ $invitation->welcome_message }}
                </div>

                <!-- Couple Profiles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 pt-12">
                    <!-- Bride Profile -->
                    <div class="space-y-5 flex flex-col items-center group">
                        <div class="w-36 h-36 border-2 border-[#2e4d3c] p-1.5 bg-white shadow-sm flex items-center justify-center overflow-hidden transition-transform duration-500 hover:rotate-3">
                            <div class="w-full h-full border border-dashed border-[#2e4d3c]/30 flex items-center justify-center bg-[#faf7f2]">
                                <span class="font-signature text-5xl text-[#2e4d3c] capitalize">{{ substr($invitation->bride_name_short, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-playfair text-2xl font-bold text-[#1e2522] tracking-wider">{{ $invitation->bride_name_full }}</h3>
                            <div class="w-8 h-[1px] bg-[#2e4d3c]/40 mx-auto my-1"></div>
                            <p class="font-special-elite text-[9px] text-stone-500 uppercase tracking-widest">Putri Tercinta dari:</p>
                            <p class="font-playfair text-sm font-semibold text-stone-700">{{ $invitation->bride_father }}</p>
                            <p class="font-playfair text-xs text-stone-500">dan {{ $invitation->bride_mother }}</p>
                        </div>
                    </div>

                    <!-- Groom Profile -->
                    <div class="space-y-5 flex flex-col items-center group">
                        <div class="w-36 h-36 border-2 border-[#2e4d3c] p-1.5 bg-white shadow-sm flex items-center justify-center overflow-hidden transition-transform duration-500 hover:-rotate-3">
                            <div class="w-full h-full border border-dashed border-[#2e4d3c]/30 flex items-center justify-center bg-[#faf7f2]">
                                <span class="font-signature text-5xl text-[#2e4d3c] capitalize">{{ substr($invitation->groom_name_short, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-playfair text-2xl font-bold text-[#1e2522] tracking-wider">{{ $invitation->groom_name_full }}</h3>
                            <div class="w-8 h-[1px] bg-[#2e4d3c]/40 mx-auto my-1"></div>
                            <p class="font-special-elite text-[9px] text-stone-500 uppercase tracking-widest">Putra Tercinta dari:</p>
                            <p class="font-playfair text-sm font-semibold text-stone-700">{{ $invitation->groom_father }}</p>
                            <p class="font-playfair text-xs text-stone-500">dan {{ $invitation->groom_mother }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 3: Event Information -->
            <section class="space-y-12 py-6">
                <div class="text-center space-y-3">
                    <h2 class="font-playfair text-3xl font-bold text-[#1e2522] tracking-widest uppercase">Rangkaian Acara</h2>
                    <div class="w-16 h-[2px] bg-[#2e4d3c] mx-auto"></div>
                </div>

                <div class="grid grid-cols-1 {{ $hasAkad && $hasResepsi ? 'md:grid-cols-2' : 'max-w-md mx-auto' }} gap-8">
                    <!-- Akad Card -->
                    @if ($hasAkad)
                    <div class="vintage-card rounded-3xl p-8 text-center space-y-6 shadow-sm relative overflow-hidden group hover:border-[#2e4d3c] transition-all duration-300">
                        <div class="w-12 h-12 rounded-full border border-[#2e4d3c]/30 bg-[#2e4d3c]/5 flex items-center justify-center mx-auto text-[#2e4d3c]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-playfair text-2xl font-bold text-[#1e2522] tracking-wide">Akad Nikah</h3>
                            <p class="font-special-elite text-xs font-bold text-[#2e4d3c] uppercase tracking-wider">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
                            <p class="font-special-elite text-xs text-stone-500 tracking-wider">{{ $invitation->akad_time }}</p>
                        </div>
                        <div class="pt-2">
                            <p class="font-playfair text-sm font-semibold text-stone-700">{{ $invitation->akad_location }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Resepsi Card -->
                    @if ($hasResepsi)
                    <div class="vintage-card rounded-3xl p-8 text-center space-y-6 shadow-sm relative overflow-hidden group hover:border-[#2e4d3c] transition-all duration-300">
                        <div class="w-12 h-12 rounded-full border border-[#2e4d3c]/30 bg-[#2e4d3c]/5 flex items-center justify-center mx-auto text-[#2e4d3c]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 7.5h.008v.008h-.008V7.5zm0 2.25h.008v.008h-.008V9.75zM12.75 12h.008v.008h-.008V12zm-.75 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V16.5zM18.75 3v18M12.75 21h-6.25c-.621 0-1.125-.504-1.125-1.125V3.545m10.5 0V3h-6v.545m6 0v2.73M11.25 3v2.73m0-2.73a3.3 3.3 0 00-3.3 3.3v13.5" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-playfair text-2xl font-bold text-[#1e2522] tracking-wide">Resepsi</h3>
                            <p class="font-special-elite text-xs font-bold text-[#2e4d3c] uppercase tracking-wider">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
                            <p class="font-special-elite text-xs text-stone-500 tracking-wider">{{ $invitation->resepsi_time }}</p>
                        </div>
                        <div class="pt-2">
                            <p class="font-playfair text-sm font-semibold text-stone-700">{{ $invitation->resepsi_location }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Leaflet Interactive Map -->
                @if ($invitation->latitude && $invitation->longitude)
                    <div class="max-w-xl mx-auto vintage-card p-2 rounded-2xl shadow-sm overflow-hidden my-6">
                        <div id="leaflet-wedding-map-sepia" class="w-full h-64 rounded-xl" style="z-index: 10;"></div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var lat = {{ $invitation->latitude }};
                            var lng = {{ $invitation->longitude }};
                            var map = L.map('leaflet-wedding-map-sepia', {
                                scrollWheelZoom: false
                            }).setView([lat, lng], 15);

                            var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(map);

                            tiles.on('tileload', function() {
                                let containers = document.querySelectorAll('#leaflet-wedding-map-sepia .leaflet-tile-container');
                                containers.forEach(el => {
                                    el.classList.add('leaflet-tile-container-sepia');
                                });
                            });

                            L.circle([lat, lng], {
                                color: '#2e4d3c',
                                fillColor: '#2e4d3c',
                                fillOpacity: 0.3,
                                radius: 120
                            }).addTo(map);

                            L.marker([lat, lng]).addTo(map)
                                .bindPopup("<div class='text-stone-900 font-bold text-xs text-center'>Lokasi Pernikahan</div>")
                                .openPopup();
                        });
                    </script>
                @elseif ($invitation->maps_embed_url)
                    <div class="max-w-xl mx-auto vintage-card p-2 rounded-2xl shadow-sm overflow-hidden my-6">
                        <iframe 
                            src="{{ $invitation->maps_embed_url }}" 
                            class="w-full h-64 rounded-xl border-0 grayscale opacity-75 contrast-[85%] hover:grayscale-0 hover:opacity-100 transition-all duration-700" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                @endif

                <!-- Maps Button -->
                <div class="text-center pt-2">
                    <a href="{{ $invitation->maps_url }}" target="_blank"
                        class="inline-flex items-center space-x-2.5 px-8 py-3.5 bg-white border-2 border-[#2e4d3c] text-[#2e4d3c] font-bold text-xs uppercase tracking-widest rounded-full shadow-sm hover:bg-[#2e4d3c] hover:text-white transition-all cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span>Buka Peta Lokasi</span>
                    </a>
                </div>
            </section>

            <!-- Section 4: Wishes & Greetings (RSVP) -->
            <section class="space-y-12 py-6 border-t border-[#2e4d3c]/20 pt-16">
                <div class="text-center space-y-3">
                    <h2 class="font-playfair text-3xl font-bold text-[#1e2522] tracking-widest uppercase">Doa Restu & RSVP</h2>
                    <p class="font-special-elite text-xs text-stone-500 tracking-wide">Kirimkan konfirmasi kehadiran dan ucapan hangat</p>
                    <div class="w-16 h-[2px] bg-[#2e4d3c] mx-auto"></div>
                </div>

                <!-- RSVP Form Container -->
                <div class="vintage-card rounded-3xl p-6 md:p-8 shadow-sm relative overflow-hidden">
                    @if (session()->has('success_message'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center space-x-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success_message') }}</span>
                        </div>
                    @endif

                    <form wire:submit.prevent="submitGreeting" class="space-y-6">
                        <!-- Guest Name Input -->
                        <div class="space-y-2">
                            <label for="name" class="block font-special-elite text-[10px] font-bold uppercase tracking-[0.2em] text-stone-600">Nama Lengkap</label>
                            <input type="text" id="name" wire:model="name"
                                class="w-full px-4 py-3 bg-white border border-stone-300 rounded-xl text-stone-800 text-sm focus:outline-none focus:ring-1 focus:ring-[#2e4d3c] focus:border-[#2e4d3c] transition-colors"
                                placeholder="Ketik nama Anda..." required>
                            @error('name') 
                                <span class="text-xs text-red-600 font-semibold mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- Attendance Confirmation -->
                        <div class="space-y-2">
                            <label for="status" class="block font-special-elite text-[10px] font-bold uppercase tracking-[0.2em] text-stone-600">Konfirmasi Kehadiran</label>
                            <select id="status" wire:model="status"
                                class="w-full px-4 py-3 bg-white border border-stone-300 rounded-xl text-stone-800 text-sm focus:outline-none focus:ring-1 focus:ring-[#2e4d3c] focus:border-[#2e4d3c] transition-colors cursor-pointer">
                                <option value="hadir">Saya Akan Hadir</option>
                                <option value="tidak_hadir">Maaf, Tidak Bisa Hadir</option>
                                <option value="ragu">Masih Ragu-ragu</option>
                            </select>
                            @error('status') 
                                <span class="text-xs text-red-600 font-semibold mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- Message Textarea -->
                        <div class="space-y-2">
                            <label for="message" class="block font-special-elite text-[10px] font-bold uppercase tracking-[0.2em] text-stone-600">Pesan Doa Restu</label>
                            <textarea id="message" wire:model="message" rows="4"
                                class="w-full px-4 py-3 bg-white border border-stone-300 rounded-xl text-stone-800 text-sm focus:outline-none focus:ring-1 focus:ring-[#2e4d3c] focus:border-[#2e4d3c] transition-colors resize-none"
                                placeholder="Tulis ucapan selamat dan doa terbaik..." required></textarea>
                            @error('message') 
                                <span class="text-xs text-red-600 font-semibold mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full py-4 bg-[#2e4d3c] hover:bg-[#1f3529] text-white font-bold tracking-[0.2em] text-xs uppercase rounded-xl shadow-md transition-all cursor-pointer flex items-center justify-center space-x-2">
                            <svg wire:loading wire:target="submitGreeting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Kirim Ucapan</span>
                        </button>
                    </form>
                </div>

                <!-- Wishes List (Realtime Feed) -->
                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 scrollbar-custom">
                    @forelse ($this->getGreetings() as $greet)
                        <div class="vintage-card rounded-2xl p-5 space-y-3 shadow-sm hover:border-[#2e4d3c] transition-colors">
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-sm text-[#2e4d3c] font-playfair tracking-wide">{{ $greet->name }}</h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold font-special-elite uppercase tracking-wider border"
                                    :class="{
                                        'bg-emerald-50 border-emerald-200 text-emerald-800': '{{ $greet->status }}' === 'hadir',
                                        'bg-rose-50 border-rose-200 text-rose-800': '{{ $greet->status }}' === 'tidak_hadir',
                                        'bg-amber-50 border-amber-200 text-amber-800': '{{ $greet->status }}' === 'ragu'
                                    }">
                                    @if ($greet->status === 'hadir')
                                        Hadir
                                    @elseif ($greet->status === 'tidak_hadir')
                                        Absen
                                    @else
                                        Ragu
                                    @endif
                                </span>
                            </div>
                            <p class="text-stone-700 text-sm leading-relaxed whitespace-pre-line font-playfair">{{ $greet->message }}</p>
                            <div class="font-special-elite text-[9px] text-stone-400 text-right">{{ $greet->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-center py-10 border border-dashed border-[#2e4d3c]/40 rounded-2xl text-stone-500 text-sm font-special-elite">
                            Belum ada ucapan. Jadilah yang pertama memberikan doa restu!
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Forest Tree sketch footer -->
            <footer class="pt-8 text-center">
                <div class="py-4 flex justify-center items-center opacity-70">
                    <svg class="w-48 h-12 text-[#2e4d3c]" viewBox="0 0 200 50" fill="currentColor">
                        <polygon points="30,40 22,28 26,28 18,15 22,15 15,3 22,3 22,0 24,0 24,3 31,3 24,15 28,15 20,28 24,28 16,40" />
                        <polygon points="100,45 88,30 93,30 80,18 85,18 73,5 82,5 82,0 84,0 84,5 93,5 84,18 90,18 78,30 83,30 71,45" />
                        <polygon points="170,40 162,28 166,28 158,15 162,15 155,3 162,3 162,0 164,0 164,3 171,3 164,15 168,15 160,28 164,28 156,40" />
                    </svg>
                </div>
                <p class="font-special-elite text-[10px] text-stone-500">Thank you for sharing our special day.</p>
            </footer>
        </div>
    @elseif ($invitation->template === 'pastel')
        <!-- ============================================ -->
        <!-- TEMPLATE 3: PASTEL SAKURA (MINIMALIST)      -->
        <!-- ============================================ -->
        
        <!-- Floating Cherry Blossom Petals -->
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
            <svg class="absolute top-[10%] left-[10%] w-4.5 h-4.5 text-[#d48895]/25 animate-petal-1" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C12 2 16 6 16 10C16 13.5 12 16 12 16C12 16 8 13.5 8 10C8 6 12 2 12 2Z" />
            </svg>
            <svg class="absolute top-[40%] right-[15%] w-3.5 h-3.5 text-[#d48895]/20 animate-petal-2" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C12 2 16 6 16 10C16 13.5 12 16 12 16C12 16 8 13.5 8 10C8 6 12 2 12 2Z" />
            </svg>
            <svg class="absolute bottom-[30%] left-[25%] w-5 h-5 text-[#d48895]/18 animate-petal-3" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C12 2 16 6 16 10C16 13.5 12 16 12 16C12 16 8 13.5 8 10C8 6 12 2 12 2Z" />
            </svg>
            <svg class="absolute top-[70%] left-[75%] w-4 h-4 text-[#d48895]/22 animate-petal-1" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C12 2 16 6 16 10C16 13.5 12 16 12 16C12 16 8 13.5 8 10C8 6 12 2 12 2Z" />
            </svg>
        </div>

        <!-- 1. Opening Cover (Envelope Gate) -->
        <div x-show="!isCoverHidden" 
            class="fixed inset-0 z-50 overflow-hidden bg-transparent">
            
            <!-- Floral circular frames -->
            <div class="absolute inset-4 border border-[#d48895]/15 rounded-3xl pointer-events-none z-10"
                :class="isOpen ? 'opacity-0 transition-opacity duration-1000' : 'opacity-100'"></div>
            <div class="absolute inset-6 border border-dashed border-[#d48895]/10 rounded-2xl pointer-events-none z-10"
                :class="isOpen ? 'opacity-0 transition-opacity duration-1000' : 'opacity-100'"></div>

            <!-- Top Half of Envelope -->
            <div class="absolute inset-x-0 top-0 h-1/2 bg-[#fffafb] border-b border-[#d48895]/20 transition-transform duration-1000 ease-in-out flex flex-col justify-end items-center pb-12"
                :class="isOpen ? '-translate-y-full' : 'translate-y-0'">
                <div class="font-montserrat text-[#d48895] uppercase tracking-[0.3em] text-[9px] md:text-xs font-semibold mb-3">Wedding Invitation</div>
                <div class="font-pinyon text-5xl md:text-7xl text-[#4e3a3d] tracking-wide text-center px-4">
                    {{ $invitation->bride_name_short }} &amp; {{ $invitation->groom_name_short }}
                </div>
            </div>

            <!-- Bottom Half of Envelope -->
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-[#fffafb] border-t border-[#d48895]/20 transition-transform duration-1000 ease-in-out flex flex-col justify-start items-center pt-12"
                :class="isOpen ? 'translate-y-full' : 'translate-y-0'">
                
                <div class="font-montserrat text-stone-500 text-[9px] uppercase tracking-[0.2em] mb-2 font-medium">Dear Special Guest:</div>
                <div class="px-6 py-2 rounded-2xl bg-[#fffafb] border border-[#d48895]/15 shadow-sm mb-6 max-w-xs text-center">
                    <span class="text-[#d48895] font-pinyon text-2xl tracking-wide block">
                        {{ $guestNameQuery ?: 'Tamu Undangan' }}
                    </span>
                </div>

                <!-- Soft Pink Open Button -->
                <button @click="openInvitation()" 
                    class="relative z-20 px-8 py-3.5 bg-[#d48895] hover:bg-[#c67482] text-white font-montserrat font-bold tracking-[0.2em] text-[10px] uppercase rounded-full shadow-md shadow-[#d48895]/20 border border-white/20 cursor-pointer transition-all duration-300 flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>Open Invitation</span>
                </button>
            </div>

            <!-- Cherry Blossom Stamp Motif -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-28 h-28 rounded-full bg-[#fffafb] border-2 border-[#d48895] flex flex-col items-center justify-center z-10 transition-all duration-700 pointer-events-none shadow-md"
                :class="isOpen ? 'opacity-0 scale-50' : 'opacity-100 scale-100'">
                <div class="w-[92%] h-[92%] rounded-full border border-dashed border-[#d48895]/30 flex flex-col items-center justify-center">
                    <span class="font-pinyon text-3xl text-[#d48895] leading-none pt-2">{{ substr($invitation->bride_name_short, 0, 1) }}&amp;{{ substr($invitation->groom_name_short, 0, 1) }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Main Content View -->
        <div x-show="isOpen" 
            x-transition:enter="transition opacity ease-out duration-1000 delay-500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="max-w-md mx-auto px-4 md:max-w-2xl lg:max-w-3xl relative z-10 space-y-24 pb-24 pt-10">
            
            <!-- Section 1: Hero Banner -->
            <section class="min-h-screen flex flex-col justify-center items-center text-center space-y-8 relative">
                <!-- Soft floral corner dividers -->
                <div class="absolute top-4 left-4 w-8 h-8 border-t border-l border-[#d48895]/20 pointer-events-none"></div>
                <div class="absolute top-4 right-4 w-8 h-8 border-t border-r border-[#d48895]/20 pointer-events-none"></div>
                <div class="absolute bottom-4 left-4 w-8 h-8 border-b border-l border-[#d48895]/20 pointer-events-none"></div>
                <div class="absolute bottom-4 right-4 w-8 h-8 border-b border-r border-[#d48895]/20 pointer-events-none"></div>

                <div class="space-y-4">
                    <p class="font-montserrat text-[#d48895] uppercase tracking-[0.3em] text-[10px] font-semibold">The Wedding of</p>
                    <h1 class="font-pinyon text-6xl md:text-8xl text-[#4e3a3d] tracking-wide leading-tight">
                        {{ $invitation->bride_name_short }} &amp; {{ $invitation->groom_name_short }}
                    </h1>
                </div>

                <div class="w-12 h-[1px] bg-[#d48895]/30"></div>

                <p class="text-[#4e3a3d]/80 text-sm leading-relaxed max-w-sm font-light font-montserrat">
                    {{ $invitation->welcome_message ?: 'Menyambut hari bahagia kami dengan cinta dan restu Anda.' }}
                </p>

                <!-- Countdown Timer -->
                <div x-data="{
                        target: new Date('{{ $invitation->event_date->toIso8601String() }}').getTime(),
                        days: 0,
                        hours: 0,
                        minutes: 0,
                        seconds: 0,
                        update() {
                            let diff = this.target - new Date().getTime();
                            if (diff < 0) return;
                            this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
                        }
                    }"
                    x-init="update(); setInterval(() => update(), 1000)"
                    class="pt-6">
                    <div class="flex space-x-3 md:space-x-4">
                        <div class="sakura-card px-4 py-3 rounded-2xl min-w-[70px] text-center border border-[#d48895]/20">
                            <span class="font-playfair text-xl md:text-2xl font-bold text-[#4e3a3d]" x-text="days">00</span>
                            <span class="font-montserrat text-[8px] uppercase tracking-wider text-stone-500 block mt-1">Hari</span>
                        </div>
                        <div class="sakura-card px-4 py-3 rounded-2xl min-w-[70px] text-center border border-[#d48895]/20">
                            <span class="font-playfair text-xl md:text-2xl font-bold text-[#4e3a3d]" x-text="hours">00</span>
                            <span class="font-montserrat text-[8px] uppercase tracking-wider text-stone-500 block mt-1">Jam</span>
                        </div>
                        <div class="sakura-card px-4 py-3 rounded-2xl min-w-[70px] text-center border border-[#d48895]/20">
                            <span class="font-playfair text-xl md:text-2xl font-bold text-[#4e3a3d]" x-text="minutes">00</span>
                            <span class="font-montserrat text-[8px] uppercase tracking-wider text-stone-500 block mt-1">Menit</span>
                        </div>
                        <div class="sakura-card px-4 py-3 rounded-2xl min-w-[70px] text-center border border-[#d48895]/20">
                            <span class="font-playfair text-xl md:text-2xl font-bold text-[#4e3a3d]" x-text="seconds">00</span>
                            <span class="font-montserrat text-[8px] uppercase tracking-wider text-stone-500 block mt-1">Detik</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 2: Mempelai -->
            <section class="space-y-12">
                <div class="text-center space-y-2">
                    <h2 class="font-playfair text-3xl text-[#4e3a3d] font-bold">Mempelai</h2>
                    <p class="font-montserrat text-[9px] uppercase tracking-widest text-[#d48895] font-semibold">Kami yang Berbahagia</p>
                </div>

                <div class="grid grid-cols-1 gap-10">
                    <!-- Groom Card -->
                    <div class="sakura-card p-8 rounded-3xl border border-[#d48895]/20 space-y-4 text-center">
                        <div class="w-20 h-20 mx-auto rounded-full border-2 border-[#d48895] flex items-center justify-center bg-[#fffafb] shadow-sm">
                            <span class="font-pinyon text-4xl text-[#d48895] pt-1">G</span>
                        </div>
                        <div>
                            <h3 class="font-playfair text-2xl font-bold text-[#4e3a3d]">{{ $invitation->groom_name_full }}</h3>
                            <p class="font-montserrat text-[10px] text-stone-500 mt-1 uppercase tracking-wider">Mempelai Pria</p>
                        </div>
                        <p class="font-montserrat text-xs text-stone-600 leading-relaxed font-light">
                            Putra tercinta dari:<br>
                            <span class="font-medium text-[#4e3a3d]">{{ $invitation->groom_father }}</span> &amp; <span class="font-medium text-[#4e3a3d]">{{ $invitation->groom_mother }}</span>
                        </p>
                    </div>

                    <!-- Bride Card -->
                    <div class="sakura-card p-8 rounded-3xl border border-[#d48895]/20 space-y-4 text-center">
                        <div class="w-20 h-20 mx-auto rounded-full border-2 border-[#d48895] flex items-center justify-center bg-[#fffafb] shadow-sm">
                            <span class="font-pinyon text-4xl text-[#d48895] pt-1">B</span>
                        </div>
                        <div>
                            <h3 class="font-playfair text-2xl font-bold text-[#4e3a3d]">{{ $invitation->bride_name_full }}</h3>
                            <p class="font-montserrat text-[10px] text-stone-500 mt-1 uppercase tracking-wider">Mempelai Wanita</p>
                        </div>
                        <p class="font-montserrat text-xs text-stone-600 leading-relaxed font-light">
                            Putri tercinta dari:<br>
                            <span class="font-medium text-[#4e3a3d]">{{ $invitation->bride_father }}</span> &amp; <span class="font-medium text-[#4e3a3d]">{{ $invitation->bride_mother }}</span>
                        </p>
                    </div>
                </div>
            </section>

            <!-- Section 3: Rangkaian Acara -->
            <section class="space-y-12">
                <div class="text-center space-y-2">
                    <h2 class="font-playfair text-3xl text-[#4e3a3d] font-bold">Rangkaian Acara</h2>
                    <p class="font-montserrat text-[9px] uppercase tracking-widest text-[#d48895] font-semibold">Waktu &amp; Tempat Pelaksanaan</p>
                </div>

                <div class="grid grid-cols-1 {{ $hasAkad && $hasResepsi ? 'md:grid-cols-2' : 'max-w-md mx-auto' }} gap-8">
                    @if ($hasAkad)
                        <!-- Akad Nikah Card -->
                        <div class="sakura-card p-6 rounded-3xl border border-[#d48895]/20 space-y-4 flex flex-col justify-between">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between border-b border-[#d48895]/10 pb-3">
                                    <span class="font-playfair text-lg font-bold text-[#4e3a3d]">Akad Nikah</span>
                                    <span class="px-2.5 py-1 bg-[#d48895]/10 text-[#d48895] rounded-full font-montserrat text-[8px] uppercase tracking-wider font-bold">Sakral</span>
                                </div>
                                <div class="space-y-3 font-montserrat text-xs text-stone-600">
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-[#d48895] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                        <div>
                                            <span class="font-medium text-stone-800 block">Hari &amp; Tanggal</span>
                                            <span class="font-light">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-[#d48895] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <span class="font-medium text-stone-800 block">Waktu</span>
                                            <span class="font-light">{{ $invitation->akad_time ?: '08:00 - Selesai' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-[#d48895] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <div>
                                            <span class="font-medium text-stone-800 block">Lokasi</span>
                                            <span class="font-light">{{ $invitation->akad_location }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($hasResepsi)
                        <!-- Resepsi Card -->
                        <div class="sakura-card p-6 rounded-3xl border border-[#d48895]/20 space-y-4 flex flex-col justify-between">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between border-b border-[#d48895]/10 pb-3">
                                    <span class="font-playfair text-lg font-bold text-[#4e3a3d]">Resepsi</span>
                                    <span class="px-2.5 py-1 bg-[#d48895]/10 text-[#d48895] rounded-full font-montserrat text-[8px] uppercase tracking-wider font-bold">Pesta</span>
                                </div>
                                <div class="space-y-3 font-montserrat text-xs text-stone-600">
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-[#d48895] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                        <div>
                                            <span class="font-medium text-stone-800 block">Hari &amp; Tanggal</span>
                                            <span class="font-light">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-[#d48895] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <span class="font-medium text-stone-800 block">Waktu</span>
                                            <span class="font-light">{{ $invitation->resepsi_time ?: '11:00 - Selesai' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-[#d48895] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <div>
                                            <span class="font-medium text-stone-800 block">Lokasi</span>
                                            <span class="font-light">{{ $invitation->resepsi_location }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Section 4: Peta Lokasi -->
            <section class="space-y-6">
                <div class="text-center space-y-2">
                    <h2 class="font-playfair text-2xl text-[#4e3a3d] font-bold">Lokasi Acara</h2>
                    <div class="w-8 h-0.5 bg-[#d48895]/40 mx-auto"></div>
                </div>

                <div class="sakura-card p-4 rounded-3xl border border-[#d48895]/20 space-y-4">
                    <!-- Leaflet Map Container -->
                    @if ($invitation->latitude && $invitation->longitude)
                        <div class="w-full h-72 rounded-2xl border border-[#d48895]/15 overflow-hidden shadow-inner relative z-10" wire:ignore>
                            <div id="leaflet-wedding-map-sakura" class="w-full h-full"></div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                var lat = {{ $invitation->latitude }};
                                var lng = {{ $invitation->longitude }};
                                var map = L.map('leaflet-wedding-map-sakura', {
                                    scrollWheelZoom: false
                                }).setView([lat, lng], 15);

                                var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(map);

                                tiles.on('tileload', function() {
                                    let containers = document.querySelectorAll('#leaflet-wedding-map-sakura .leaflet-tile-container');
                                    containers.forEach(el => {
                                        el.classList.add('leaflet-tile-container-sakura');
                                    });
                                });

                                L.circle([lat, lng], {
                                    color: '#d48895',
                                    fillColor: '#d48895',
                                    fillOpacity: 0.3,
                                    radius: 120
                                }).addTo(map);

                                L.marker([lat, lng]).addTo(map)
                                    .bindPopup("<div class='text-stone-900 font-bold text-xs text-center'>Lokasi Pernikahan</div>")
                                    .openPopup();
                            });
                        </script>
                    @elseif ($invitation->maps_embed_url)
                        <div class="w-full h-72 rounded-2xl border border-[#d48895]/15 overflow-hidden shadow-inner relative z-10">
                            <iframe 
                                src="{{ $invitation->maps_embed_url }}" 
                                class="w-full h-full border-0 grayscale opacity-75 contrast-[85%] hover:grayscale-0 hover:opacity-100 transition-all duration-700" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        @if($invitation->maps_url)
                            <a href="{{ $invitation->maps_url }}" target="_blank" 
                                class="flex-1 py-3 bg-[#d48895] hover:bg-[#c67482] text-white text-center rounded-xl font-bold tracking-wide text-xs transition-colors duration-300 flex items-center justify-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-12v15m-12-3.75h18" />
                                </svg>
                                <span>Buka Google Maps</span>
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Section 5: Doa Restu & RSVP -->
            <section class="space-y-8">
                <div class="text-center space-y-2">
                    <h2 class="font-playfair text-3xl text-[#4e3a3d] font-bold">Doa Restu &amp; RSVP</h2>
                    <p class="font-montserrat text-[9px] uppercase tracking-widest text-[#d48895] font-semibold">Kirimkan Ucapan Selamat Anda</p>
                </div>

                <div class="sakura-card p-6 rounded-3xl border border-[#d48895]/20">
                    @if (session()->has('success_message'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center space-x-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success_message') }}</span>
                        </div>
                    @endif

                    <form wire:submit.prevent="submitGreeting" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-1">
                                <label class="font-montserrat text-[9px] uppercase font-bold text-stone-500 tracking-wider">Nama Anda</label>
                                <input type="text" wire:model="name" placeholder="Masukkan nama..." 
                                    class="w-full px-4 py-2.5 bg-white border border-[#d48895]/20 rounded-xl text-xs text-[#4e3a3d] focus:outline-none focus:border-[#d48895] focus:ring-1 focus:ring-[#d48895]">
                                @error('name') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="font-montserrat text-[9px] uppercase font-bold text-stone-500 tracking-wider">Konfirmasi Kehadiran</label>
                                <select wire:model="status" 
                                    class="w-full px-4 py-2.5 bg-white border border-[#d48895]/20 rounded-xl text-xs text-[#4e3a3d] focus:outline-none focus:border-[#d48895] focus:ring-1 focus:ring-[#d48895] cursor-pointer">
                                    <option value="hadir">Hadir</option>
                                    <option value="tidak_hadir">Tidak Hadir</option>
                                    <option value="ragu">Masih Ragu</option>
                                </select>
                                @error('status') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="font-montserrat text-[9px] uppercase font-bold text-stone-500 tracking-wider">Ucapan &amp; Doa Restu</label>
                                <textarea wire:model="message" rows="4" placeholder="Tuliskan ucapan Anda..." 
                                    class="w-full px-4 py-2.5 bg-white border border-[#d48895]/20 rounded-xl text-xs text-[#4e3a3d] focus:outline-none focus:border-[#d48895] focus:ring-1 focus:ring-[#d48895] resize-none"></textarea>
                                @error('message') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <button type="submit" 
                            class="w-full py-3.5 bg-[#d48895] hover:bg-[#c67482] text-white font-montserrat font-bold uppercase tracking-wider text-xs rounded-xl shadow-md shadow-[#d48895]/10 cursor-pointer transition-colors duration-300">
                            Kirim Doa Restu
                        </button>
                    </form>
                </div>

                <!-- Wishes Wall -->
                <div class="space-y-4 max-h-96 overflow-y-auto scrollbar-custom pr-2">
                    @forelse ($this->getGreetings() as $greet)
                        <div class="sakura-card p-5 rounded-2xl border border-[#d48895]/15 space-y-2">
                            <div class="flex items-center justify-between border-b border-[#d48895]/10 pb-2">
                                <span class="font-playfair text-sm font-bold text-[#4e3a3d]">{{ $greet->name }}</span>
                                <span class="px-2 py-0.5 rounded-full font-montserrat text-[8px] uppercase tracking-wider font-bold">
                                    @if ($greet->status == 'hadir')
                                        <span class="text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Hadir</span>
                                    @elseif ($greet->status == 'tidak_hadir')
                                        <span class="text-red-600 bg-red-50 px-2 py-0.5 rounded-full">Tidak Hadir</span>
                                    @else
                                        <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Ragu</span>
                                    @endif
                                </span>
                            </div>
                            <p class="text-stone-600 text-xs leading-relaxed font-light font-montserrat whitespace-pre-line">{{ $greet->message }}</p>
                            <div class="font-montserrat text-[8px] text-stone-400 text-right">{{ $greet->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-center py-10 border border-dashed border-[#d48895]/30 rounded-2xl text-stone-400 text-xs font-montserrat">
                            Belum ada ucapan. Jadilah yang pertama memberikan doa restu!
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Sakura footer -->
            <footer class="pt-8 text-center">
                <p class="font-pinyon text-2xl text-[#d48895]">Terima Kasih</p>
                <p class="font-montserrat text-[8px] text-stone-400 tracking-widest mt-1">Sari &amp; Raju Wedding</p>
            </footer>
        </div>

    @elseif ($invitation->template === 'retro')
        <!-- ============================================ -->
        <!-- TEMPLATE 4: RETRO VINTAGE (HIPSTER CARD)     -->
        <!-- ============================================ -->
        
        <!-- 1. Opening Cover (Envelope Gate) -->
        <div x-show="!isCoverHidden" 
            class="fixed inset-0 z-50 overflow-hidden bg-transparent">
            
            <!-- Funky retro star frames -->
            <div class="absolute inset-4 border-4 border-[#be5a38] pointer-events-none z-10"
                :class="isOpen ? 'opacity-0 transition-opacity duration-1000' : 'opacity-100'"></div>
            <div class="absolute inset-6 border-2 border-dashed border-[#cca043]/60 pointer-events-none z-10"
                :class="isOpen ? 'opacity-0 transition-opacity duration-1000' : 'opacity-100'"></div>

            <!-- Top Half of Envelope -->
            <div class="absolute inset-x-0 top-0 h-1/2 bg-[#f8f4eb] border-b-4 border-[#be5a38] transition-transform duration-1000 ease-in-out flex flex-col justify-end items-center pb-12"
                :class="isOpen ? '-translate-y-full' : 'translate-y-0'">
                <div class="font-syne text-[#be5a38] uppercase tracking-[0.25em] text-[10px] md:text-xs font-bold mb-3">Wedding Invitation</div>
                <div class="font-abril text-4xl md:text-6xl text-[#33221c] tracking-wider text-center px-4">
                    {{ $invitation->bride_name_short }} &amp; {{ $invitation->groom_name_short }}
                </div>
            </div>

            <!-- Bottom Half of Envelope -->
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-[#f8f4eb] border-t-4 border-[#be5a38] transition-transform duration-1000 ease-in-out flex flex-col justify-start items-center pt-12"
                :class="isOpen ? 'translate-y-full' : 'translate-y-0'">
                
                <div class="font-syne text-[#33221c] text-[9px] uppercase tracking-[0.2em] mb-2 font-bold">Special Invitation For:</div>
                <div class="px-6 py-2 bg-[#f8f4eb] border-3 border-[#be5a38] shadow-sm mb-6 max-w-xs text-center retro-border">
                    <span class="text-[#33221c] font-syne font-bold text-sm tracking-wide block">
                        {{ $guestNameQuery ?: 'TAMU UNDANGAN' }}
                    </span>
                </div>

                <!-- Terracotta Retro Open Button -->
                <button @click="openInvitation()" 
                    class="relative z-20 px-8 py-4 bg-[#be5a38] hover:bg-[#a64828] text-white font-syne font-bold tracking-[0.2em] text-[10px] uppercase rounded-none border-2 border-black cursor-pointer shadow-[3px_3px_0_0_#cca043] hover:shadow-[1px_1px_0_0_#cca043] transition-all duration-300 flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>OPEN INVITATION</span>
                </button>
            </div>

            <!-- Funky Daisy Wax Seal Center -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-28 h-28 rounded-full bg-[#f8f4eb] border-4 border-[#be5a38] flex flex-col items-center justify-center z-10 transition-all duration-700 pointer-events-none shadow-[4px_4px_0_0_#cca043]"
                :class="isOpen ? 'opacity-0 scale-50' : 'opacity-100 scale-100'">
                <div class="w-[90%] h-[90%] rounded-full border border-dashed border-[#cca043] flex flex-col items-center justify-center">
                    <span class="font-abril text-2xl text-[#be5a38] tracking-widest">{{ substr($invitation->bride_name_short, 0, 1) }}{{ substr($invitation->groom_name_short, 0, 1) }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Main Content View -->
        <div x-show="isOpen" 
            x-transition:enter="transition opacity ease-out duration-1000 delay-500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="max-w-md mx-auto px-4 md:max-w-2xl lg:max-w-3xl relative z-10 space-y-24 pb-24 pt-10">
            
            <!-- Section 1: Hero Banner -->
            <section class="min-h-screen flex flex-col justify-center items-center text-center space-y-8 relative">
                <div class="absolute top-4 left-4 w-10 h-10 border-t-4 border-l-4 border-[#be5a38] pointer-events-none"></div>
                <div class="absolute top-4 right-4 w-10 h-10 border-t-4 border-r-4 border-[#be5a38] pointer-events-none"></div>
                <div class="absolute bottom-4 left-4 w-10 h-10 border-b-4 border-l-4 border-[#be5a38] pointer-events-none"></div>
                <div class="absolute bottom-4 right-4 w-10 h-10 border-b-4 border-r-4 border-[#be5a38] pointer-events-none"></div>

                <div class="space-y-4">
                    <p class="font-syne text-[#be5a38] uppercase tracking-[0.25em] text-xs font-bold">WE ARE GETTING MARRIED</p>
                    <h1 class="font-abril text-5xl md:text-7xl text-[#33221c] tracking-wider leading-tight drop-shadow-[2px_2px_0_#cca043]">
                        {{ $invitation->bride_name_short }} &amp; {{ $invitation->groom_name_short }}
                    </h1>
                </div>

                <div class="w-16 h-1 bg-[#be5a38]"></div>

                <p class="text-[#33221c] text-sm leading-relaxed max-w-sm font-outfit">
                    {{ $invitation->welcome_message ?: 'Join us for the celebration of our wedding!' }}
                </p>

                <!-- Countdown Timer -->
                <div x-data="{
                        target: new Date('{{ $invitation->event_date->toIso8601String() }}').getTime(),
                        days: 0,
                        hours: 0,
                        minutes: 0,
                        seconds: 0,
                        update() {
                            let diff = this.target - new Date().getTime();
                            if (diff < 0) return;
                            this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
                        }
                    }"
                    x-init="update(); setInterval(() => update(), 1000)"
                    class="pt-6">
                    <div class="flex space-x-3 md:space-x-4">
                        <div class="polaroid-card text-center min-w-[75px]">
                            <span class="font-abril text-2xl text-[#be5a38]" x-text="days">00</span>
                            <span class="font-syne text-[8px] uppercase font-bold text-stone-500 block mt-1">DAYS</span>
                        </div>
                        <div class="polaroid-card text-center min-w-[75px]">
                            <span class="font-abril text-2xl text-[#be5a38]" x-text="hours">00</span>
                            <span class="font-syne text-[8px] uppercase font-bold text-stone-500 block mt-1">HOURS</span>
                        </div>
                        <div class="polaroid-card text-center min-w-[75px]">
                            <span class="font-abril text-2xl text-[#be5a38]" x-text="minutes">00</span>
                            <span class="font-syne text-[8px] uppercase font-bold text-stone-500 block mt-1">MINS</span>
                        </div>
                        <div class="polaroid-card text-center min-w-[75px]">
                            <span class="font-abril text-2xl text-[#be5a38]" x-text="seconds">00</span>
                            <span class="font-syne text-[8px] uppercase font-bold text-stone-500 block mt-1">SECS</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 2: Mempelai -->
            <section class="space-y-12">
                <div class="text-center space-y-2">
                    <h2 class="font-abril text-4xl text-[#33221c] tracking-wide">The Happy Couple</h2>
                    <p class="font-syne text-xs uppercase tracking-widest text-[#be5a38] font-bold">MEET THE BRIDE &amp; GROOM</p>
                </div>

                <div class="grid grid-cols-1 gap-12">
                    <!-- Groom Card -->
                    <div class="polaroid-card space-y-6">
                        <div class="w-full h-48 bg-[#cca043]/10 border-2 border-[#be5a38] flex items-center justify-center">
                            <svg class="w-20 h-20 text-[#be5a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div class="text-center space-y-2">
                            <h3 class="font-abril text-2xl text-[#33221c]">{{ $invitation->groom_name_full }}</h3>
                            <p class="font-syne text-[10px] text-[#be5a38] uppercase font-bold tracking-wider">THE GROOM</p>
                            <p class="font-outfit text-xs text-stone-600 leading-relaxed max-w-xs mx-auto pt-2 border-t border-stone-100">
                                Beloved Son of:<br>
                                <span class="font-bold text-[#be5a38]">{{ $invitation->groom_father }}</span> &amp; <span class="font-bold text-[#be5a38]">{{ $invitation->groom_mother }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Bride Card -->
                    <div class="polaroid-card space-y-6">
                        <div class="w-full h-48 bg-[#cca043]/10 border-2 border-[#be5a38] flex items-center justify-center">
                            <svg class="w-20 h-20 text-[#be5a38]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div class="text-center space-y-2">
                            <h3 class="font-abril text-2xl text-[#33221c]">{{ $invitation->bride_name_full }}</h3>
                            <p class="font-syne text-[10px] text-[#be5a38] uppercase font-bold tracking-wider">THE BRIDE</p>
                            <p class="font-outfit text-xs text-stone-600 leading-relaxed max-w-xs mx-auto pt-2 border-t border-stone-100">
                                Beloved Daughter of:<br>
                                <span class="font-bold text-[#be5a38]">{{ $invitation->bride_father }}</span> &amp; <span class="font-bold text-[#be5a38]">{{ $invitation->bride_mother }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 3: Rangkaian Acara -->
            <section class="space-y-12">
                <div class="text-center space-y-2">
                    <h2 class="font-abril text-4xl text-[#33221c] tracking-wide">Event Schedule</h2>
                    <p class="font-syne text-xs uppercase tracking-widest text-[#be5a38] font-bold">JOIN OUR HAPPY CELEBRATION</p>
                </div>

                <div class="grid grid-cols-1 {{ $hasAkad && $hasResepsi ? 'md:grid-cols-2' : 'max-w-md mx-auto' }} gap-8">
                    @if ($hasAkad)
                        <!-- Akad Nikah Card -->
                        <div class="polaroid-card flex flex-col justify-between space-y-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between border-b-2 border-[#be5a38] pb-3">
                                    <span class="font-abril text-xl text-[#33221c]">Akad Nikah</span>
                                    <span class="px-3 py-1 bg-[#be5a38] text-white font-syne text-[8px] uppercase font-bold">SOLEMN</span>
                                </div>
                                <div class="space-y-3 font-outfit text-xs text-stone-600">
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-[#be5a38] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                        <div>
                                            <span class="font-bold text-stone-800 block uppercase text-[10px]">Date</span>
                                            <span class="font-light">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-[#be5a38] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <span class="font-bold text-stone-800 block uppercase text-[10px]">Time</span>
                                            <span class="font-light">{{ $invitation->akad_time ?: '08:00 - Finish' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-[#be5a38] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <div>
                                            <span class="font-bold text-stone-800 block uppercase text-[10px]">Venue</span>
                                            <span class="font-light">{{ $invitation->akad_location }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($hasResepsi)
                        <!-- Resepsi Card -->
                        <div class="polaroid-card flex flex-col justify-between space-y-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between border-b-2 border-[#be5a38] pb-3">
                                    <span class="font-abril text-xl text-[#33221c]">Wedding Reception</span>
                                    <span class="px-3 py-1 bg-[#be5a38] text-white font-syne text-[8px] uppercase font-bold">PARTY</span>
                                </div>
                                <div class="space-y-3 font-outfit text-xs text-stone-600">
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-[#be5a38] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                        <div>
                                            <span class="font-bold text-stone-800 block uppercase text-[10px]">Date</span>
                                            <span class="font-light">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-[#be5a38] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <span class="font-bold text-stone-800 block uppercase text-[10px]">Time</span>
                                            <span class="font-light">{{ $invitation->resepsi_time ?: '11:00 - Selesai' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-[#be5a38] shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <div>
                                            <span class="font-bold text-stone-800 block uppercase text-[10px]">Venue</span>
                                            <span class="font-light">{{ $invitation->resepsi_location }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Section 4: Peta Lokasi -->
            <section class="space-y-6">
                <div class="text-center space-y-2">
                    <h2 class="font-abril text-3xl text-[#33221c]">Venue Location</h2>
                    <div class="w-12 h-1 bg-[#be5a38] mx-auto"></div>
                </div>

                <div class="polaroid-card space-y-4">
                    <!-- Leaflet Map Container -->
                    @if ($invitation->latitude && $invitation->longitude)
                        <div class="w-full h-72 border-3 border-[#be5a38] overflow-hidden relative z-10" wire:ignore>
                            <div id="leaflet-wedding-map-retro" class="w-full h-full"></div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                var lat = {{ $invitation->latitude }};
                                var lng = {{ $invitation->longitude }};
                                var map = L.map('leaflet-wedding-map-retro', {
                                    scrollWheelZoom: false
                                }).setView([lat, lng], 15);

                                var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(map);

                                tiles.on('tileload', function() {
                                    let containers = document.querySelectorAll('#leaflet-wedding-map-retro .leaflet-tile-container');
                                    containers.forEach(el => {
                                        el.classList.add('leaflet-tile-container-retro');
                                    });
                                });

                                L.circle([lat, lng], {
                                    color: '#be5a38',
                                    fillColor: '#be5a38',
                                    fillOpacity: 0.3,
                                    radius: 120
                                }).addTo(map);

                                L.marker([lat, lng]).addTo(map)
                                    .bindPopup("<div class='text-stone-900 font-bold text-xs text-center'>Lokasi Pernikahan</div>")
                                    .openPopup();
                            });
                        </script>
                    @elseif ($invitation->maps_embed_url)
                        <div class="w-full h-72 border-3 border-[#be5a38] overflow-hidden relative z-10">
                            <iframe 
                                src="{{ $invitation->maps_embed_url }}" 
                                class="w-full h-full border-0 grayscale opacity-75 contrast-[85%] hover:grayscale-0 hover:opacity-100 transition-all duration-700" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    @endif

                    @if($invitation->maps_url)
                        <a href="{{ $invitation->maps_url }}" target="_blank" 
                            class="block w-full py-4 bg-[#be5a38] hover:bg-[#a64828] text-white text-center rounded-none font-syne font-bold tracking-wider text-xs border-2 border-black shadow-[3px_3px_0_0_#cca043] transition-all duration-300">
                            OPEN IN GOOGLE MAPS
                        </a>
                    @endif
                </div>
            </section>

            <!-- Section 5: Doa Restu & RSVP -->
            <section class="space-y-8">
                <div class="text-center space-y-2">
                    <h2 class="font-abril text-4xl text-[#33221c]">RSVP &amp; Wishes</h2>
                    <p class="font-syne text-xs uppercase tracking-widest text-[#be5a38] font-bold">SEND YOUR WARM BLESSINGS</p>
                </div>

                <div class="polaroid-card">
                    @if (session()->has('success_message'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center space-x-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success_message') }}</span>
                        </div>
                    @endif

                    <form wire:submit.prevent="submitGreeting" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-1">
                                <label class="font-syne text-[10px] uppercase font-bold text-stone-600 tracking-wider">Your Name</label>
                                <input type="text" wire:model="name" placeholder="Enter name..." 
                                    class="w-full px-4 py-3 bg-[#f8f4eb] border-2 border-stone-850 text-xs text-[#33221c] focus:outline-none focus:border-[#be5a38] font-outfit">
                                @error('name') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="font-syne text-[10px] uppercase font-bold text-stone-600 tracking-wider">Attendance Status</label>
                                <select wire:model="status" 
                                    class="w-full px-4 py-3 bg-[#f8f4eb] border-2 border-stone-850 text-xs text-[#33221c] focus:outline-none focus:border-[#be5a38] font-outfit cursor-pointer">
                                    <option value="hadir">Will Attend</option>
                                    <option value="tidak_hadir">Will Not Attend</option>
                                    <option value="ragu">Unsure</option>
                                </select>
                                @error('status') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="font-syne text-[10px] uppercase font-bold text-stone-600 tracking-wider">Wishes &amp; Message</label>
                                <textarea wire:model="message" rows="4" placeholder="Write your message here..." 
                                    class="w-full px-4 py-3 bg-[#f8f4eb] border-2 border-stone-850 text-xs text-[#33221c] focus:outline-none focus:border-[#be5a38] font-outfit resize-none"></textarea>
                                @error('message') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <button type="submit" 
                            class="w-full py-4 bg-[#be5a38] hover:bg-[#a64828] text-white font-syne font-bold uppercase tracking-wider text-xs border-2 border-black shadow-[3px_3px_0_0_#cca043] transition-all duration-300">
                            SEND RSVP
                        </button>
                    </form>
                </div>

                <!-- Wishes Wall -->
                <div class="space-y-4 max-h-96 overflow-y-auto scrollbar-custom pr-2">
                    @forelse ($this->getGreetings() as $greet)
                        <div class="polaroid-card space-y-2">
                            <div class="flex items-center justify-between border-b-2 border-stone-100 pb-2">
                                <span class="font-abril text-lg text-[#be5a38]">{{ $greet->name }}</span>
                                <span class="px-2.5 py-0.5 border-2 border-black font-syne text-[8px] uppercase font-bold bg-[#f8f4eb]">
                                    @if ($greet->status == 'hadir')
                                        <span class="text-green-700">ATTENDING</span>
                                    @elseif ($greet->status == 'tidak_hadir')
                                        <span class="text-red-700">NOT ATTENDING</span>
                                    @else
                                        <span class="text-amber-700">UNSURE</span>
                                    @endif
                                </span>
                            </div>
                            <p class="text-stone-600 text-xs leading-relaxed font-outfit whitespace-pre-line">{{ $greet->message }}</p>
                            <div class="font-syne text-[8px] text-stone-400 text-right">{{ $greet->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-center py-10 border-2 border-dashed border-[#be5a38] text-stone-500 text-xs font-syne">
                            NO WISHES YET. BE THE FIRST TO SEND BLESSINGS!
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Retro footer -->
            <footer class="pt-8 text-center border-t-2 border-[#be5a38]/20">
                <p class="font-abril text-3xl text-[#be5a38] drop-shadow-[1px_1px_0_#cca043]">THANK YOU</p>
                <p class="font-syne text-[9px] text-stone-500 tracking-widest mt-1 uppercase">Sari &amp; Raju Wedding Celebration</p>
            </footer>
        </div>

    @else
        <!-- ============================================ -->
        <!-- TEMPLATE 1: ELEGANT EMERALD & GOLD (DEFAULT) -->
        <!-- ============================================ -->

        <!-- Floating Background Particles (Simulating Gold Dust) -->
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
            <div class="absolute top-[20%] left-[10%] w-2.5 h-2.5 rounded-full bg-[#e6ca65]/35 blur-[1px] animate-particle-1"></div>
            <div class="absolute top-[40%] right-[15%] w-3.5 h-3.5 rounded-full bg-[#e6ca65]/20 blur-[2px] animate-particle-2"></div>
            <div class="absolute bottom-[30%] left-[25%] w-2.5 h-2.5 rounded-full bg-[#e6ca65]/25 blur-[1px] animate-particle-3"></div>
            <div class="absolute top-[70%] left-[80%] w-1.5 h-1.5 rounded-full bg-[#e6ca65]/35 blur-[1px] animate-particle-1"></div>
        </div>

        <!-- 1. Opening Cover (Envelope Gate) -->
        <div x-show="!isCoverHidden" 
            class="fixed inset-0 z-50 overflow-hidden bg-transparent">
            
            <div class="absolute inset-4 border border-[#e6ca65]/20 rounded-2xl pointer-events-none z-10"
                :class="isOpen ? 'opacity-0 transition-opacity duration-1000' : 'opacity-100'"></div>
            <div class="absolute inset-6 border border-[#e6ca65]/10 rounded-xl pointer-events-none z-10"
                :class="isOpen ? 'opacity-0 transition-opacity duration-1000' : 'opacity-100'"></div>

            <!-- Top Half of Envelope -->
            <div class="absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-[#05110d] to-[#030907] border-b border-[#e6ca65]/30 transition-transform duration-1000 ease-in-out flex flex-col justify-end items-center pb-12"
                :class="isOpen ? '-translate-y-full' : 'translate-y-0'">
                <div class="text-[#e6ca65] uppercase tracking-[0.35em] text-[10px] md:text-xs font-semibold mb-3">Undangan Pernikahan</div>
                <div class="font-serif text-4xl md:text-6xl text-[#e6ca65] tracking-widest font-light text-center px-4">
                    {{ $invitation->bride_name_short }} &amp; {{ $invitation->groom_name_short }}
                </div>
            </div>

            <!-- Bottom Half of Envelope -->
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-[#05110d] to-[#030907] border-t border-[#e6ca65]/30 transition-transform duration-1000 ease-in-out flex flex-col justify-start items-center pt-12"
                :class="isOpen ? 'translate-y-full' : 'translate-y-0'">
                
                <div class="text-stone-400 text-[10px] uppercase tracking-[0.22em] mb-2 font-medium">Kepada Yth. Bapak/Ibu/Saudara/i:</div>
                <div class="px-6 py-2.5 rounded-xl bg-white/[0.02] border border-[#e6ca65]/15 shadow-inner mb-6 max-w-xs text-center">
                    <span class="text-[#e6ca65] font-serif italic text-xl tracking-wide block">
                        {{ $guestNameQuery ?: 'Tamu Undangan' }}
                    </span>
                </div>

                <button @click="openInvitation()" 
                    class="relative z-20 px-8 py-4 bg-gold-gradient text-stone-950 font-bold tracking-[0.25em] text-xs uppercase rounded-full shadow-2xl shadow-[#e6ca65]/20 border border-[#fff]/40 cursor-pointer animate-pulse-gold transition-all duration-300 flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-stone-950">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>Buka Undangan</span>
                </button>
            </div>

            <!-- Decorative Floral Watermark/Frame in center -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-28 h-28 rounded-full bg-[#030907] border border-[#e6ca65]/60 flex flex-col items-center justify-center z-10 transition-all duration-700 pointer-events-none shadow-xl shadow-black/80"
                :class="isOpen ? 'opacity-0 scale-50' : 'opacity-100 scale-100'">
                <div class="w-[96%] h-[96%] rounded-full border border-dashed border-[#e6ca65]/30 flex items-center justify-center">
                    <span class="font-serif text-3xl text-[#e6ca65] font-light tracking-widest">{{ substr($invitation->bride_name_short, 0, 1) }}&amp;{{ substr($invitation->groom_name_short, 0, 1) }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Main Content View -->
        <div x-show="isOpen" 
            x-transition:enter="transition opacity ease-out duration-1000 delay-500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="max-w-md mx-auto px-4 md:max-w-2xl lg:max-w-3xl relative z-10 space-y-24 pb-24 pt-10">
            
            <!-- Section 1: Hero Banner -->
            <section class="min-h-screen flex flex-col justify-center items-center text-center space-y-8 relative">
                <div class="absolute top-4 left-4 w-6 h-6 border-t border-l border-[#e6ca65]/30 pointer-events-none"></div>
                <div class="absolute top-4 right-4 w-6 h-6 border-t border-r border-[#e6ca65]/30 pointer-events-none"></div>
                <div class="absolute bottom-4 left-4 w-6 h-6 border-b border-l border-[#e6ca65]/30 pointer-events-none"></div>
                <div class="absolute bottom-4 right-4 w-6 h-6 border-b border-r border-[#e6ca65]/30 pointer-events-none"></div>

                <div class="text-[10px] uppercase tracking-[0.4em] text-[#e6ca65] font-semibold">The Wedding of</div>
                
                <div class="relative py-8 px-6">
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-[1px] bg-gradient-to-r from-transparent via-[#e6ca65]/50 to-transparent"></div>
                    
                    <h1 class="font-serif text-5xl md:text-7xl font-extralight tracking-wide text-white flex flex-col space-y-4">
                        <span class="font-script text-6xl md:text-8xl text-[#e6ca65] leading-none py-2 capitalize">{{ $invitation->bride_name_short }}</span>
                        <span class="text-stone-500 font-serif text-xl tracking-[0.3em] lowercase italic my-1">&amp;</span>
                        <span class="font-serif text-stone-100 tracking-widest leading-none">{{ $invitation->groom_name_short }}</span>
                    </h1>
                    
                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-24 h-[1px] bg-gradient-to-r from-transparent via-[#e6ca65]/50 to-transparent"></div>
                </div>

                <div class="text-xs text-stone-400 uppercase tracking-[0.25em]">
                    <p>{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
                </div>

                <!-- Countdown Timer -->
                <div x-data="{
                        target: new Date('{{ $invitation->event_date->toIso8601String() }}').getTime(),
                        days: 0,
                        hours: 0,
                        minutes: 0,
                        seconds: 0,
                        update() {
                            let diff = this.target - new Date().getTime();
                            if (diff < 0) return;
                            this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
                        }
                    }"
                    x-init="update(); setInterval(() => update(), 1000)"
                    class="grid grid-cols-4 gap-3 max-w-sm mx-auto pt-4 w-full px-2">
                    <div class="flex flex-col items-center bg-stone-900/60 border border-[#e6ca65]/20 rounded-2xl p-3 shadow-lg shadow-black/30 backdrop-blur-md">
                        <span class="text-2xl font-bold text-[#e6ca65]" x-text="days">0</span>
                        <span class="text-[9px] uppercase tracking-widest text-stone-400 mt-1 font-semibold">Hari</span>
                    </div>
                    <div class="flex flex-col items-center bg-stone-900/60 border border-[#e6ca65]/20 rounded-2xl p-3 shadow-lg shadow-black/30 backdrop-blur-md">
                        <span class="text-2xl font-bold text-[#e6ca65]" x-text="hours">0</span>
                        <span class="text-[9px] uppercase tracking-widest text-stone-400 mt-1 font-semibold">Jam</span>
                    </div>
                    <div class="flex flex-col items-center bg-stone-900/60 border border-[#e6ca65]/20 rounded-2xl p-3 shadow-lg shadow-black/30 backdrop-blur-md">
                        <span class="text-2xl font-bold text-[#e6ca65]" x-text="minutes">0</span>
                        <span class="text-[9px] uppercase tracking-widest text-stone-400 mt-1 font-semibold">Menit</span>
                    </div>
                    <div class="flex flex-col items-center bg-stone-900/60 border border-[#e6ca65]/20 rounded-2xl p-3 shadow-lg shadow-black/30 backdrop-blur-md">
                        <span class="text-2xl font-bold text-[#e6ca65]" x-text="seconds">0</span>
                        <span class="text-[9px] uppercase tracking-widest text-stone-400 mt-1 font-semibold">Detik</span>
                    </div>
                </div>

                <div class="pt-12 flex flex-col items-center space-y-2">
                    <span class="text-[10px] uppercase tracking-[0.2em] text-stone-500">Scroll Down</span>
                    <div class="w-[1px] h-12 bg-[#e6ca65]/20 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 bg-[#e6ca65] animate-line-grow"></div>
                    </div>
                </div>
            </section>

            <!-- Section 2: Ayat / Welcome Quote -->
            <section class="space-y-12 text-center py-6">
                <div class="max-w-md mx-auto glass-card rounded-3xl p-8 space-y-6 shadow-xl relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-36 h-36 opacity-5 text-[#e6ca65] pointer-events-none">
                        <svg viewBox="0 0 100 100" fill="currentColor">
                            <path d="M50 0 C45 25 25 45 0 50 C25 55 45 75 50 100 C55 75 75 55 100 50 C75 45 55 25 50 0 Z" />
                        </svg>
                    </div>

                    <span class="text-5xl text-[#e6ca65]/20 font-serif leading-none block -mt-2">“</span>
                    
                    <p class="text-stone-300 text-sm leading-relaxed italic relative z-10 px-2">
                        "Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang."
                    </p>
                    <div class="text-xs text-[#e6ca65]/80 font-medium tracking-[0.15em]">— AR-RUM: 21</div>
                </div>

                <div class="text-stone-300 text-sm max-w-sm mx-auto leading-relaxed px-4 pt-6">
                    {{ $invitation->welcome_message }}
                </div>

                <!-- Mempelai Profile -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 pt-12">
                    <div class="space-y-5 flex flex-col items-center group">
                        <div class="w-36 h-36 rounded-full border border-[#e6ca65]/40 p-1.5 bg-stone-900/60 shadow-xl flex items-center justify-center overflow-hidden transition-transform duration-500 hover:rotate-3">
                            <div class="w-full h-full rounded-full border border-dashed border-[#e6ca65]/20 flex items-center justify-center bg-[#071712]">
                                <span class="font-script text-5xl text-[#e6ca65]">{{ substr($invitation->bride_name_short, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-serif text-2xl font-semibold text-white tracking-wider">{{ $invitation->bride_name_full }}</h3>
                            <div class="w-8 h-[1px] bg-[#e6ca65]/40 mx-auto my-1"></div>
                            <p class="text-[10px] text-stone-500 uppercase tracking-widest">Putri Tercinta dari:</p>
                            <p class="text-sm font-medium text-stone-300">{{ $invitation->bride_father }}</p>
                            <p class="text-xs text-stone-400">dan {{ $invitation->bride_mother }}</p>
                        </div>
                    </div>

                    <div class="space-y-5 flex flex-col items-center group">
                        <div class="w-36 h-36 rounded-full border border-[#e6ca65]/40 p-1.5 bg-stone-900/60 shadow-xl flex items-center justify-center overflow-hidden transition-transform duration-500 hover:-rotate-3">
                            <div class="w-full h-full rounded-full border border-dashed border-[#e6ca65]/20 flex items-center justify-center bg-[#071712]">
                                <span class="font-script text-5xl text-[#e6ca65]">{{ substr($invitation->groom_name_short, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-serif text-2xl font-semibold text-white tracking-wider">{{ $invitation->groom_name_full }}</h3>
                            <div class="w-8 h-[1px] bg-[#e6ca65]/40 mx-auto my-1"></div>
                            <p class="text-[10px] text-stone-500 uppercase tracking-widest">Putra Tercinta dari:</p>
                            <p class="text-sm font-medium text-stone-300">{{ $invitation->groom_father }}</p>
                            <p class="text-xs text-stone-400">dan {{ $invitation->groom_mother }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 3: Event Information -->
            <section class="space-y-12 py-6">
                <div class="text-center space-y-3">
                    <h2 class="font-serif text-3xl font-light text-white tracking-widest uppercase">Rangkaian Acara</h2>
                    <div class="w-16 h-px bg-gradient-to-r from-transparent via-[#e6ca65] to-transparent mx-auto"></div>
                </div>

                <div class="grid grid-cols-1 {{ $hasAkad && $hasResepsi ? 'md:grid-cols-2' : 'max-w-md mx-auto' }} gap-8">
                    @if ($hasAkad)
                    <div class="glass-card rounded-3xl p-8 text-center space-y-6 shadow-xl relative overflow-hidden group hover:border-[#e6ca65]/35 transition-all duration-300">
                        <div class="w-12 h-12 rounded-full border border-[#e6ca65]/35 bg-[#e6ca65]/5 flex items-center justify-center mx-auto text-[#e6ca65]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-serif text-2xl font-medium text-white tracking-wide">Akad Nikah</h3>
                            <p class="text-xs font-bold text-[#e6ca65] uppercase tracking-wider">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
                            <p class="text-xs text-stone-400 tracking-wider">{{ $invitation->akad_time }}</p>
                        </div>
                        <div class="pt-2">
                            <p class="text-sm font-semibold text-stone-200">{{ $invitation->akad_location }}</p>
                        </div>
                    </div>
                    @endif

                    @if ($hasResepsi)
                    <div class="glass-card rounded-3xl p-8 text-center space-y-6 shadow-xl relative overflow-hidden group hover:border-[#e6ca65]/35 transition-all duration-300">
                        <div class="w-12 h-12 rounded-full border border-[#e6ca65]/35 bg-[#e6ca65]/5 flex items-center justify-center mx-auto text-[#e6ca65]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 7.5h.008v.008h-.008V7.5zm0 2.25h.008v.008h-.008V9.75zM12.75 12h.008v.008h-.008V12zm-.75 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V16.5zM18.75 3v18M12.75 21h-6.25c-.621 0-1.125-.504-1.125-1.125V3.545m10.5 0V3h-6v.545m6 0v2.73M11.25 3v2.73m0-2.73a3.3 3.3 0 00-3.3 3.3v13.5" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-serif text-2xl font-medium text-white tracking-wide">Resepsi</h3>
                            <p class="text-xs font-bold text-[#e6ca65] uppercase tracking-wider">{{ $invitation->event_date->translatedFormat('l, d F Y') }}</p>
                            <p class="text-xs text-stone-400 tracking-wider">{{ $invitation->resepsi_time }}</p>
                        </div>
                        <div class="pt-2">
                            <p class="text-sm font-semibold text-stone-200">{{ $invitation->resepsi_location }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Leaflet Interactive Map -->
                @if ($invitation->latitude && $invitation->longitude)
                    <div class="max-w-xl mx-auto glass-card p-2 rounded-2xl shadow-xl overflow-hidden my-6">
                        <div id="leaflet-wedding-map" class="w-full h-64 rounded-xl" style="z-index: 10;"></div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var lat = {{ $invitation->latitude }};
                            var lng = {{ $invitation->longitude }};
                            var map = L.map('leaflet-wedding-map', {
                                scrollWheelZoom: false
                            }).setView([lat, lng], 15);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(map);

                            L.circle([lat, lng], {
                                color: '#e6ca65',
                                fillColor: '#e6ca65',
                                fillOpacity: 0.4,
                                radius: 120
                            }).addTo(map);

                            L.marker([lat, lng]).addTo(map)
                                .bindPopup("<div class='text-stone-900 font-bold text-xs text-center'>Lokasi Pernikahan</div>")
                                .openPopup();
                        });
                    </script>
                @elseif ($invitation->maps_embed_url)
                    <div class="max-w-xl mx-auto glass-card p-2 rounded-2xl shadow-xl overflow-hidden my-6">
                        <iframe 
                            src="{{ $invitation->maps_embed_url }}" 
                            class="w-full h-64 rounded-xl border-0 grayscale invert opacity-75 contrast-[85%] hover:grayscale-0 hover:invert-0 hover:opacity-100 transition-all duration-700" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                @endif

                <!-- Maps Button -->
                <div class="text-center pt-2">
                    <a href="{{ $invitation->maps_url }}" target="_blank"
                        class="inline-flex items-center space-x-2.5 px-8 py-3.5 bg-stone-900/80 border border-[#e6ca65]/35 text-white font-semibold text-xs uppercase tracking-widest rounded-full shadow-lg hover:bg-stone-950 hover:border-[#e6ca65] transition-all cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-[#e6ca65]">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span>Buka Peta Lokasi</span>
                    </a>
                </div>
            </section>

            <!-- Section 4: Wishes & Greetings (RSVP) -->
            <section class="space-y-12 py-6 border-t border-stone-800/60 pt-16">
                <div class="text-center space-y-3">
                    <h2 class="font-serif text-3xl font-light text-white tracking-widest uppercase">Doa Restu & RSVP</h2>
                    <p class="text-xs text-stone-400 tracking-wide">Kirimkan konfirmasi kehadiran dan doa terbaik Anda</p>
                    <div class="w-16 h-px bg-gradient-to-r from-transparent via-[#e6ca65] to-transparent mx-auto"></div>
                </div>

                <!-- RSVP Form Container -->
                <div class="glass-card rounded-3xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
                    @if (session()->has('success_message'))
                        <div class="mb-6 p-4 bg-emerald-950/80 border border-emerald-500/30 text-emerald-300 text-xs font-semibold rounded-2xl flex items-center space-x-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-400 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success_message') }}</span>
                        </div>
                    @endif

                    <form wire:submit.prevent="submitGreeting" class="space-y-6">
                        <!-- Guest Name Input -->
                        <div class="space-y-2">
                            <label for="name" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Lengkap</label>
                            <input type="text" id="name" wire:model="name"
                                class="w-full px-4 py-3 bg-stone-900/50 border border-stone-800 rounded-xl text-white text-sm focus:outline-none focus:ring-1 focus:ring-[#e6ca65] focus:border-[#e6ca65] transition-colors"
                                placeholder="Ketik nama Anda..." required>
                            @error('name') 
                                <span class="text-xs text-red-400 font-semibold mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- Attendance Confirmation -->
                        <div class="space-y-2">
                            <label for="status" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Konfirmasi Kehadiran</label>
                            <select id="status" wire:model="status"
                                class="w-full px-4 py-3 bg-stone-900/50 border border-stone-800 rounded-xl text-white text-sm focus:outline-none focus:ring-1 focus:ring-[#e6ca65] focus:border-[#e6ca65] transition-colors cursor-pointer">
                                <option value="hadir">Saya Akan Hadir</option>
                                <option value="tidak_hadir">Maaf, Tidak Bisa Hadir</option>
                                <option value="ragu">Masih Ragu-ragu</option>
                            </select>
                            @error('status') 
                                <span class="text-xs text-red-400 font-semibold mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- Message Textarea -->
                        <div class="space-y-2">
                            <label for="message" class="block text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Pesan Doa Restu</label>
                            <textarea id="message" wire:model="message" rows="4"
                                class="w-full px-4 py-3 bg-stone-900/50 border border-stone-800 rounded-xl text-white text-sm focus:outline-none focus:ring-1 focus:ring-[#e6ca65] focus:border-[#e6ca65] transition-colors resize-none"
                                placeholder="Tulis ucapan selamat dan doa restu terbaik untuk kedua mempelai..." required></textarea>
                            @error('message') 
                                <span class="text-xs text-red-400 font-semibold mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full py-4 bg-gold-gradient hover:opacity-95 text-stone-950 font-bold tracking-[0.25em] text-xs uppercase rounded-xl shadow-xl shadow-[#e6ca65]/10 transition-all cursor-pointer flex items-center justify-center space-x-2">
                            <svg wire:loading wire:target="submitGreeting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-stone-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Kirim Ucapan</span>
                        </button>
                    </form>
                </div>

                <!-- Wishes List Feed -->
                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 scrollbar-custom">
                    @forelse ($this->getGreetings() as $greet)
                        <div class="glass-card rounded-2xl p-5 space-y-3 shadow-lg hover:border-[#e6ca65]/25 transition-colors">
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-sm text-[#e6ca65] tracking-wide">{{ $greet->name }}</h4>
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
                            </div>
                            <p class="text-stone-300 text-sm leading-relaxed whitespace-pre-line">{{ $greet->message }}</p>
                            <div class="text-[9px] text-stone-500 text-right">{{ $greet->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-center py-10 border border-dashed border-[#e6ca65]/20 rounded-2xl text-stone-500 text-sm">
                            Belum ada ucapan. Jadilah yang pertama memberikan doa restu!
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    @endif
</div>