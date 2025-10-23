{{-- Language Switcher Component --}}
<div class="language-switcher dropdown">
    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __('app.common.select_language') }}">
        <i class="fas fa-globe"></i>
        @switch($currentLocale ?? app()->getLocale())
            @case('vi')
                🇻🇳 VI
                @break
            @case('en')  
                🇺🇸 EN
                @break
            @case('ko')
                🇰🇷 KO
                @break
            @case('ja')
                🇯🇵 JA
                @break
            @case('zh')
                🇨🇳 ZH
                @break
            @default
                🇻🇳 VI
        @endswitch
    </button>
    
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
        @php
            $localeNames = [
                'vi' => ['name' => 'Tiếng Việt', 'flag' => '🇻🇳'],
                'en' => ['name' => 'English', 'flag' => '🇺🇸'],
                'ko' => ['name' => '한국어', 'flag' => '🇰🇷'],
                'ja' => ['name' => '日本語', 'flag' => '🇯🇵'],
                'zh' => ['name' => '中文', 'flag' => '🇨🇳'],
            ];
            $currentPath = request()->path();
            $currentLocale = $currentLocale ?? app()->getLocale();
        @endphp
        
        @foreach($availableLocales ?? config('app.available_locales', ['vi', 'en', 'ko', 'ja', 'zh']) as $locale)
            @if($locale !== $currentLocale)
                <a class="dropdown-item" href="{{ $locale === 'vi' ? '/' . $currentPath : '/' . $locale . '/' . $currentPath }}" 
                   title="{{ $localeNames[$locale]['name'] }}">
                    {{ $localeNames[$locale]['flag'] }} {{ $localeNames[$locale]['name'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>

{{-- Custom CSS for language switcher --}}
<style>
.language-switcher {
    display: inline-block;
}

.language-switcher .btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    border-color: #6c757d;
    color: #6c757d;
}

.language-switcher .btn:hover,
.language-switcher .btn:focus {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #fff;
}

.language-switcher .dropdown-menu {
    min-width: 140px;
    font-size: 0.9rem;
}

.language-switcher .dropdown-item {
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.language-switcher .dropdown-item:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .language-switcher .btn {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
}
</style>