@extends('layouts.app')

@section('title', $game->title)
@section('og_image', 'https://placehold.co/1200x630/BB5F1A/FFFFFF/jpeg?font=roboto&text=' . urlencode($game->description))
@section('meta_description', Str::limit($game->description, 150))

@section('content')

<style>
    /* Royal Liquid Glass Theme Enhancements for Bootstrap */
    .royal-glass-card {
        background: var(--glass-bg-dark, rgba(11, 12, 16, 0.85));
        backdrop-filter: var(--glass-blur, blur(20px));
        -webkit-backdrop-filter: var(--glass-blur, blur(20px));
        border: 1px solid var(--glass-border, rgba(255, 215, 0, 0.4));
        border-radius: 1.25rem;
        box-shadow: var(--liquid-shadow, 0 8px 32px 0 rgba(0, 0, 0, 0.8)),
                    inset 0 1px 5px var(--liquid-highlight, rgba(255, 255, 255, 0.2));
        transition: all 0.3s ease-in-out;
    }

    .royal-glass-card:hover {
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.9),
                    0 0 20px rgba(212, 175, 55, 0.3),
                    inset 0 1px 8px rgba(255, 255, 255, 0.3);
    }

    .badge-royal {
        background: rgba(138, 21, 21, 0.6);
        color: var(--royal-gold, #ffd700);
        border: 1px solid var(--royal-gold, #ffd700);
        box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
        letter-spacing: 1px;
    }

    .board-container-frame {
        background: linear-gradient(135deg, #f0d3a8 0%, #d9a565 100%);
        border: 4px solid #8b4513;
        border-radius: 12px;
        box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.4),
                    0 15px 35px rgba(0, 0, 0, 0.6),
                    0 0 20px rgba(212, 175, 55, 0.2);
    }

    .progress-bar-royal-track {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(212, 175, 55, 0.2);
        height: 8px;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar-royal-fill {
        background: linear-gradient(90deg, #8a1515, #d4af37, #ffd700);
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
        transition: width 0.3s ease;
    }

    .btn-royal-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.08);
        color: var(--royal-gold-light, #fff2cc);
        border: 1px solid rgba(212, 175, 55, 0.3);
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .btn-royal-icon:hover {
        background: rgba(212, 175, 55, 0.25);
        color: var(--royal-gold, #ffd700);
        border-color: var(--royal-gold, #ffd700);
        transform: translateY(-2px);
        box-shadow: 0 0 12px rgba(212, 175, 55, 0.5);
    }

    .btn-royal-play {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #d4af37, #8a1515);
        color: #ffffff;
        border: 1px solid var(--royal-gold, #ffd700);
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.6);
        transition: all 0.25s ease;
    }

    .btn-royal-play:hover {
        transform: scale(1.08);
        box-shadow: 0 0 25px rgba(255, 215, 0, 0.9);
        color: #fff;
    }

    .chip-stat {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(212, 175, 55, 0.25);
        color: var(--royal-gold-light, #fff2cc);
        font-size: 0.825rem;
        padding: 0.35rem 0.75rem;
        border-radius: 0.75rem;
    }

    .moves-container::-webkit-scrollbar {
        width: 6px;
    }
    .moves-container::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.2);
    }
    .moves-container::-webkit-scrollbar-thumb {
        background: var(--royal-gold, #ffd700);
        border-radius: 10px;
    }

    .move-btn {
        background: transparent;
        border: none;
        color: var(--royal-gold-light, #fff2cc);
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        font-family: monospace;
        text-align: left;
        width: 100%;
        transition: all 0.2s ease;
    }

    .move-btn:hover {
        background: rgba(212, 175, 55, 0.2);
        color: var(--royal-gold, #ffd700);
    }

    .move-btn.active-step {
        background: linear-gradient(90deg, rgba(138, 21, 21, 0.8), rgba(212, 175, 55, 0.4));
        color: var(--royal-gold, #ffd700) !important;
        font-weight: bold;
        border: 1px solid var(--royal-gold, #ffd700);
    }

    .social-share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: var(--royal-gold-light, #fff2cc);
        transition: all 0.3s ease;
    }

    .social-share-btn:hover {
        transform: translateY(-3px) scale(1.1);
        color: #fff;
        border-color: var(--royal-gold, #ffd700);
        box-shadow: 0 0 12px rgba(212, 175, 55, 0.5);
    }

    @keyframes pulseGlow {
        0% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.7); }
        70% { box-shadow: 0 0 0 14px rgba(212, 175, 55, 0); }
        100% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0); }
    }
    .pulse-glow {
        animation: pulseGlow 2s infinite;
    }
</style>

<div class="container py-3">

    {{-- Kicker Badge --}}
    <div class="d-flex justify-content-center mb-4">
        <span class="badge badge-royal rounded-pill px-3 py-2 text-uppercase d-inline-flex align-items-center gap-2">
            <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.062 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.286-3.958z"></path>
            </svg>
            Kỳ Phổ Ván Đấu
        </span>
    </div>

    <div class="row g-4">

        {{-- Left / Main Xiangqi Board Column --}}
        <div class="col-12 col-lg-8">
            <div class="royal-glass-card p-3 p-md-4">

                {{-- Board Graphic Container --}}
                <div class="board-container-frame mx-auto p-1 p-sm-2" style="max-width: 420px;">
                    <div id="xiangqi-board" style="width: 100%"></div>
                </div>

                {{-- Progress Bar --}}
                <div class="progress-bar-royal-track mx-auto mt-4" style="max-width: 420px;">
                    <div id="progress-bar" class="progress-bar-royal-fill h-100" style="width: 0%"></div>
                </div>

                {{-- Controls Toolbar --}}
                <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 gap-sm-3 mt-4">
                    <div class="d-flex align-items-center gap-1 gap-sm-2 p-1 rounded-pill" style="background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(212, 175, 55, 0.2);">
                        <button id="btn-start" class="btn btn-royal-icon" data-tippy-content="Về đầu">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                        </button>
                        <button id="btn-prev" class="btn btn-royal-icon" data-tippy-content="Lùi 1 bước">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>

                        <button id="btn-autoplay" class="btn btn-royal-play pulse-glow mx-1" data-tippy-content="Phát tự động">
                            <svg id="icon-play" style="width: 24px; height: 24px;" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"></path></svg>
                            <svg id="icon-pause" class="d-none" style="width: 24px; height: 24px;" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"></path></svg>
                        </button>

                        <button id="btn-next" class="btn btn-royal-icon" data-tippy-content="Tới 1 bước">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                        <button id="btn-end" class="btn btn-royal-icon" data-tippy-content="Tới cuối">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                        </button>
                    </div>

                    {{-- Autoplay Speed Selector Dropdown --}}
                    <div x-data="{
                            open: false,
                            speed: '1000',
                            options: [
                                { value: '4000', label: '0.25x' },
                                { value: '2000', label: '0.5x' },
                                { value: '1000', label: '1.0x' },
                                { value: '666', label: '1.5x' },
                                { value: '500', label: '2.0x' }
                            ],
                            get currentLabel() {
                                return this.options.find(opt => opt.value === this.speed).label;
                            },
                            updateSpeed(newSpeed) {
                                this.speed = newSpeed;
                                this.open = false;
                                $nextTick(() => {
                                    let selectEl = document.getElementById('autoplay-speed');
                                    selectEl.value = newSpeed;
                                    selectEl.dispatchEvent(new Event('change'));
                                });
                            }
                        }"
                        @click.away="open = false"
                        class="position-relative">

                        <button @click="open = !open"
                                type="button"
                                class="btn btn-dark d-flex align-items-center justify-content-between gap-2 px-3 py-2 border rounded-3 fw-bold text-warning"
                                style="min-width: 90px; border-color: rgba(212, 175, 55, 0.4) !important;">
                            <span x-text="currentLabel">1.0x</span>
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open"
                             x-transition
                             class="position-absolute end-0 bottom-100 mb-2 rounded-3 royal-glass-card overflow-hidden shadow-lg"
                             style="display: none; width: 120px; z-index: 1050; background: rgba(11, 12, 16, 0.95);">
                            <div class="py-1">
                                <template x-for="option in options" :key="option.value">
                                    <button @click="updateSpeed(option.value)"
                                            type="button"
                                            :class="{'text-warning fw-bold bg-dark': speed === option.value, 'text-light': speed !== option.value}"
                                            class="w-100 text-start px-3 py-2 border-0 bg-transparent text-decoration-none d-flex align-items-center justify-content-between small">
                                        <span x-text="option.label"></span>
                                        <svg x-show="speed === option.value" style="width: 14px; height: 14px;" class="text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <select id="autoplay-speed" class="d-none">
                            <option value="4000">0.25x</option>
                            <option value="2000">0.5x</option>
                            <option value="1000" selected>1.0x</option>
                            <option value="666">1.5x</option>
                            <option value="500">2.0x</option>
                        </select>
                    </div>
                </div>

                {{-- Step Counter --}}
                <div class="text-center font-monospace fw-bold mt-3 text-gold-light small">
                    Bước: <span id="current-step" class="text-warning fs-5 ms-1">0</span> / <span id="total-steps" class="ms-1">0</span>
                </div>
            </div>
        </div>

        {{-- Right / Game Details & Notation Column --}}
        <div class="col-12 col-lg-4 d-flex flex-column gap-4">

            {{-- Info Card --}}
            <div class="royal-glass-card p-4">
                <h1 class="h4 text-warning fw-bold mb-2 text-uppercase">{{ $game->title }}</h1>
                <div style="height: 3px; width: 50px; background: linear-gradient(90deg, var(--royal-gold), var(--royal-red)); mb-3" class="rounded mb-3"></div>

                {{-- Stat chips --}}
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="chip-stat d-inline-flex align-items-center gap-1">
                        <svg style="width: 14px; height: 14px;" class="text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        {{ number_format($game->views) }} lượt xem
                    </span>
                    <span class="chip-stat d-inline-flex align-items-center gap-1">
                        <svg style="width: 14px; height: 14px;" class="text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ $game->created_at->format('d/m/Y') }}
                    </span>
                    <span class="chip-stat d-inline-flex align-items-center gap-1">
                        <svg style="width: 14px; height: 14px;" class="text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <span id="stat-total-moves">0</span> nước đi
                    </span>
                </div>

                {{-- Author Info --}}
                <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom border-secondary border-opacity-25">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" style="width: 42px; height: 42px; background: linear-gradient(135deg, #8a1515, #d4af37); border: 2px solid var(--royal-gold);">
                        {{ substr($game->user->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="fw-bold text-light small">{{ $game->user->name }}</div>
                        <div class="text-muted extra-small" style="font-size: 0.75rem;">Tác giả ván cờ</div>
                    </div>
                </div>

                @if($game->description)
                    <div class="text-light small mb-4 opacity-75">
                        <p class="m-0">{{ $game->description }}</p>
                    </div>
                @endif

                @auth
                    @if(auth()->id() === $game->user_id)
                    <div class="mb-3">
                        <a href="{{ route('admin.games.edit', $game->slug) }}" class="btn btn-danger w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Chỉnh sửa ván cờ
                        </a>
                    </div>
                    @endif
                @endauth

                {{-- Social Share Section --}}
                <div class="pt-3 border-top border-secondary border-opacity-25">
                    <div class="text-uppercase text-muted fw-bold extra-small mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">Chia sẻ ván cờ</div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="social-share-btn" data-tippy-content="Chia sẻ Facebook">
                            <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path></svg>
                        </a>

                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($game->title) }}" target="_blank" class="social-share-btn" data-tippy-content="Chia sẻ X (Twitter)">
                            <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>
                        </a>

                        <a href="https://www.threads.net/intent/post?text={{ urlencode($game->title . ' ' . url()->current()) }}" target="_blank" class="social-share-btn" data-tippy-content="Chia sẻ Threads">
                            <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 192 192"><path d="M141.537 88.9883C140.71 88.5919 139.87 88.2104 139.019 87.8451C137.537 60.5382 122.616 44.905 97.5619 44.745C97.4484 44.7443 97.3355 44.7443 97.222 44.7443C82.2364 44.7443 69.7731 51.1409 62.102 62.7807L75.881 72.2328C81.6116 63.5383 90.6052 61.6848 97.2286 61.6848C97.3051 61.6848 97.3819 61.6848 97.4576 61.6855C105.707 61.7381 111.932 64.1366 115.961 68.814C118.893 72.2193 120.854 76.925 121.825 82.8638C114.511 81.6207 106.601 81.2385 98.145 81.7233C74.3247 83.0954 59.0111 96.9879 60.0396 116.292C60.5615 126.084 65.4397 134.508 73.775 140.011C80.8224 144.663 89.899 146.938 99.3323 146.423C111.79 145.74 121.563 140.987 128.381 132.296C133.559 125.696 136.834 117.143 138.28 106.366C144.217 109.949 148.617 114.664 151.047 120.332C155.179 129.967 155.42 145.8 142.501 158.708C131.182 170.016 117.576 174.908 97.0135 175.059C74.2042 174.89 56.9538 167.575 45.7381 153.317C35.2355 139.966 29.8077 120.682 29.6052 96C29.8077 71.3178 35.2355 52.0336 45.7381 38.6827C56.9538 24.4249 74.2039 17.11 97.0132 16.9405C119.988 17.1113 137.539 24.4614 149.184 38.708C154.894 45.6981 159.199 54.6488 162.037 64.9503L178.184 60.6422C174.744 47.9622 169.331 37.0357 161.965 28.1872C147.036 10.146 124.965 0.217327 97.0132 0C64.714 0.238473 43.606 9.88283 29.597 27.6974C15.8608 45.1633 8.85075 68.618 8.60522 96C8.85075 123.382 15.8608 146.837 29.597 164.303C43.606 182.117 64.714 191.761 97.0135 192C124.935 191.782 146.873 181.865 161.68 163.791C178.077 143.774 175.433 121.229 166.726 100.916C161.854 89.545 153.308 80.5342 141.537 88.9883ZM98.4405 129.507C88.0005 130.095 77.1544 125.409 76.6189 115.343C76.2234 107.925 82.3506 102.321 96.195 101.405C104.28 100.869 111.411 101.353 118.232 102.731C117.067 112.585 111.954 120.301 105.148 124.9C103.111 126.276 100.887 127.284 98.4405 129.507Z"/></svg>
                        </a>

                        <button onclick="navigator.clipboard.writeText(window.location.href); Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Đã sao chép liên kết!', showConfirmButton: false, timer: 2000});" class="social-share-btn" data-tippy-content="Sao chép liên kết">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Moves Notation List Card --}}
            <div class="royal-glass-card p-4 flex-grow-1 d-flex flex-column" style="min-height: 380px; max-height: 480px;">
                <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
                    <h2 class="h6 text-warning fw-bold mb-0 d-flex align-items-center gap-2">
                        <svg style="width: 18px; height: 18px;" class="text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Biên Bản Ván Cờ
                    </h2>

                    <select id="notation-lang" class="form-select form-select-sm bg-dark text-warning border rounded-pill" style="width: auto; font-size: 0.75rem; border-color: rgba(212, 175, 55, 0.4) !important;">
                        <option value="vi" selected>Tiếng Việt</option>
                        <option value="en">English</option>
                        <option value="ja">日本語</option>
                        <option value="ko">한국어</option>
                        <option value="zh">中文</option>
                    </select>
                </div>

                <div id="moves-list" class="moves-container flex-grow-1 overflow-auto pe-2 d-grid align-content-start gap-1" style="grid-template-columns: auto 1fr 1fr;">
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/xiangqiboard.css?v=1') }}" />
<script src="{{ asset('js/xiangqiboard.js?v=2') }}"></script>
<script src="{{ asset('js/xiangqi.js') }}"></script>

<script>
    $(document).ready(function() {
        const dbInitialFen = @json($game->initial_fen);
        const movesData = @json($game->moves) || [];

        let startFen = dbInitialFen;
        if (!startFen || startFen === 'start') {
            startFen = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR w - - 0 1';
        }

        const fenHistory = [startFen];
        let gameLogic = null;

        try {
            gameLogic = new Xiangqi(startFen);
        } catch (e) {
            gameLogic = new Xiangqi();
            fenHistory[0] = gameLogic.fen();
        }

        // ==========================================
        // NOTATION: shared move parsing + per-language formatting
        // ==========================================
        const NOTATION_LANGS = ['vi', 'en', 'ja', 'ko', 'zh'];

        const pieceNameMaps = {
            vi: { k: 'Tướng', a: 'Sĩ', b: 'Tượng', e: 'Tượng', v: 'Tượng', n: 'Mã', h: 'Mã', r: 'Xe', c: 'Pháo', p: 'Chốt' },
            en: { k: 'General', a: 'Advisor', b: 'Elephant', e: 'Elephant', v: 'Elephant', n: 'Horse', h: 'Horse', r: 'Chariot', c: 'Cannon', p: 'Soldier' },
            ja: { k: '将', a: '士', b: '象', e: '象', v: '象', n: '馬', h: '馬', r: '車', c: '砲', p: '兵' },
            ko: { k: '장', a: '사', b: '상', e: '상', v: '상', n: '마', h: '마', r: '차', c: '포', p: '병' },
            zh: { k: '將', a: '士', b: '象', e: '象', v: '象', n: '馬', h: '馬', r: '車', c: '炮', p: '兵' }
        };

        const actionWordMaps = {
            vi: { level: ' bình ', advance: ' tấn ', retreat: ' thoái ' },
            en: { level: ' level ', advance: ' advances ', retreat: ' retreats ' },
            ja: { level: '平', advance: '進', retreat: '退' },
            ko: { level: '평', advance: '진', retreat: '퇴' },
            zh: { level: '平', advance: '進', retreat: '退' }
        };

        const errorMessages = {
            vi: { invalidMove: 'Lỗi nước đi', dataError: 'Lỗi dữ liệu' },
            en: { invalidMove: 'Invalid move', dataError: 'Data error' },
            ja: { invalidMove: '不正な手', dataError: 'データエラー' },
            ko: { invalidMove: '잘못된 수', dataError: '데이터 오류' },
            zh: { invalidMove: '无效着法', dataError: '数据错误' }
        };

        const noMovesText = {
            vi: 'Chưa có nước đi hợp lệ nào.',
            en: 'No valid moves yet.',
            ja: 'まだ有効な手がありません。',
            ko: '아직 유효한 수가 없습니다.',
            zh: '暂无有效着法。'
        };

        // Parses a chess move into language-agnostic components (piece, files, action, target).
        function parseMoveComponents(move, color) {
            const pieceKey = move.piece.toLowerCase();
            const isRed = (color === 'w' || color === 'r');

            const fCode = move.from.charCodeAt(0);
            const fRank = parseInt(move.from.charAt(1));
            const fFile = isRed ? (10 - (fCode - 96)) : (fCode - 96);

            const tCode = move.to.charCodeAt(0);
            const tRank = parseInt(move.to.charAt(1));
            const tFile = isRed ? (10 - (tCode - 96)) : (tCode - 96);

            let actionType = 'level';
            if (fRank !== tRank) {
                if (isRed) actionType = tRank > fRank ? 'advance' : 'retreat';
                else actionType = tRank < fRank ? 'advance' : 'retreat';
            }

            let target = tFile;
            if (actionType !== 'level') {
                const isStep = ['r', 'c', 'p', 'k'].includes(pieceKey);
                if (isStep) target = Math.abs(tRank - fRank);
            }

            return { pieceKey, fFile, target, actionType };
        }

        // Formats a move as notation text in the given language ('vi', 'en', 'ja', 'ko', 'zh').
        function formatNotation(move, color, lang) {
            if (!move) return '...';

            const { pieceKey, fFile, target, actionType } = parseMoveComponents(move, color);
            const pieceName = pieceNameMaps[lang][pieceKey] || move.piece.toUpperCase();
            const action = actionWordMaps[lang][actionType];

            return `${pieceName} ${fFile}${action}${target}`;
        }

        function toVietnameseNotation(move, color) { return formatNotation(move, color, 'vi'); }
        function toEnglishNotation(move, color) { return formatNotation(move, color, 'en'); }
        function toJapaneseNotation(move, color) { return formatNotation(move, color, 'ja'); }
        function toKoreanNotation(move, color) { return formatNotation(move, color, 'ko'); }
        function toChineseNotation(move, color) { return formatNotation(move, color, 'zh'); }

        const notationFormatters = {
            vi: toVietnameseNotation,
            en: toEnglishNotation,
            ja: toJapaneseNotation,
            ko: toKoreanNotation,
            zh: toChineseNotation
        };

        let currentLang = 'vi';
        const notationHistories = { vi: [], en: [], ja: [], ko: [], zh: [] };

        if (Array.isArray(movesData)) {
            movesData.forEach(move => {
                if (!move) return;
                try {
                    const color = gameLogic.turn();
                    const result = gameLogic.move({ from: move.from, to: move.to });

                    if (result) {
                        fenHistory.push(gameLogic.fen());
                        NOTATION_LANGS.forEach(lang => {
                            notationHistories[lang].push(notationFormatters[lang](result, color));
                        });
                    } else {
                        fenHistory.push(gameLogic.fen());
                        NOTATION_LANGS.forEach(lang => {
                            notationHistories[lang].push(errorMessages[lang].invalidMove);
                        });
                    }
                } catch (e) {
                    fenHistory.push(gameLogic.fen());
                    NOTATION_LANGS.forEach(lang => {
                        notationHistories[lang].push(errorMessages[lang].dataError);
                    });
                }
            });
        }

        let board = null;
        let currentStep = 0;
        const totalSteps = fenHistory.length - 1;

        function getBoardFen(fen) {
            if (!fen || fen === 'start') return 'start';
            return fen.split(' ')[0];
        }

        board = Xiangqiboard('xiangqi-board', {
            position: getBoardFen(fenHistory[0]),
            showNotation: true,
            draggable: false,
            pieceTheme: '{{ asset('img/xiangqipieces/wikipedia/{piece}.svg') }}'
        });

        $(window).on('resize', function() {
            if (board) {
                board.resize();
            }
        });

        function renderMovesList() {
            const listContainer = $('#moves-list');
            listContainer.empty();

            $('#stat-total-moves').text(totalSteps);

            if (totalSteps === 0) {
                listContainer.html(`<div class="grid-column-span-2 text-center text-muted fst-italic mt-4">${noMovesText[currentLang]}</div>`);
                $('#total-steps').text(0);
                return;
            }

            $('#total-steps').text(totalSteps);

            const activeHistory = notationHistories[currentLang];

            for (let i = 1; i <= totalSteps; i++) {
                const isRed = (i % 2 !== 0);
                const stepNumber = Math.ceil(i / 2);

                if (isRed) {
                    listContainer.append(`<div class="text-start text-muted opacity-50 user-select-none py-1 small" style="width: 24px;">${stepNumber}.</div>`);
                }

                const moveText = activeHistory[i - 1];
                const dotColor = isRed ? '#e63946' : '#a0a0a0';
                const moveBtn = $(`<button class="d-flex align-items-center gap-2 move-btn" data-step="${i}"><span class="d-inline-block rounded-circle flex-shrink-0" style="width: 6px; height: 6px; background-color: ${dotColor};"></span>${moveText}</button>`);
                listContainer.append(moveBtn);
            }

            highlightActiveStep();
        }

        function updateProgressBar() {
            const pct = totalSteps > 0 ? (currentStep / totalSteps) * 100 : 0;
            $('#progress-bar').css('width', pct + '%');
        }

        function highlightActiveStep() {
            $('.move-btn').removeClass('active-step');
            if (currentStep > 0) {
                $(`.move-btn[data-step="${currentStep}"]`).addClass('active-step');
            }
        }

        function goToStep(step) {
            if (step < 0) step = 0;
            if (step > totalSteps) step = totalSteps;

            currentStep = step;
            $('#current-step').text(currentStep);
            updateProgressBar();
            highlightActiveStep();
            board.position(getBoardFen(fenHistory[currentStep]));
        }

        // ==========================================
        // TÍNH NĂNG AUTOPLAY (PHÁT TỰ ĐỘNG)
        // ==========================================
        let playInterval = null;
        let isPlaying = false;

        function startAutoplay() {
            if (totalSteps === 0) return;

            if (currentStep >= totalSteps) {
                goToStep(0);
            }

            isPlaying = true;
            $('#icon-play').addClass('d-none');
            $('#icon-pause').removeClass('d-none');

            const btnPlay = $('#btn-autoplay');
            btnPlay.css('background', 'linear-gradient(135deg, #e63946, #8a1515)');

            if (btnPlay[0]._tippy) btnPlay[0]._tippy.setContent('Tạm dừng');

            const speed = parseInt($('#autoplay-speed').val());

            playInterval = setInterval(() => {
                if (currentStep < totalSteps) {
                    goToStep(currentStep + 1);
                } else {
                    stopAutoplay();
                }
            }, speed);
        }

        function stopAutoplay() {
            if (!isPlaying) return;

            isPlaying = false;
            clearInterval(playInterval);
            $('#icon-pause').addClass('d-none');
            $('#icon-play').removeClass('d-none');

            const btnPlay = $('#btn-autoplay');
            btnPlay.css('background', 'linear-gradient(135deg, #d4af37, #8a1515)');

            if (btnPlay[0]._tippy) btnPlay[0]._tippy.setContent('Phát tự động');
        }

        $('#btn-autoplay').click(function() {
            if (isPlaying) stopAutoplay();
            else startAutoplay();
        });

        $('#autoplay-speed').change(function() {
            if (isPlaying) {
                stopAutoplay();
                startAutoplay();
            }
        });

        // ==========================================
        // GÁN SỰ KIỆN ĐIỀU HƯỚNG BẰNG TAY
        // ==========================================
        $('#btn-start').click(() => { stopAutoplay(); goToStep(0); });
        $('#btn-prev').click(() => { stopAutoplay(); goToStep(currentStep - 1); });
        $('#btn-next').click(() => { stopAutoplay(); goToStep(currentStep + 1); });
        $('#btn-end').click(() => { stopAutoplay(); goToStep(totalSteps); });

        $('#moves-list').on('click', '.move-btn', function() {
            stopAutoplay();
            goToStep(parseInt($(this).data('step')));
        });

        $('#notation-lang').change(function() {
            currentLang = $(this).val();
            renderMovesList();
        });

        $(document).keydown(function(e) {
            if (e.keyCode == 37) { stopAutoplay(); goToStep(currentStep - 1); }
            if (e.keyCode == 39) { stopAutoplay(); goToStep(currentStep + 1); }
            if (e.keyCode == 32 && e.target === document.body) {
                e.preventDefault();
                if (isPlaying) stopAutoplay();
                else startAutoplay();
            }
        });

        renderMovesList();
    });
</script>
@endsection
