@php
    $fieldName  = $name ?? 'university_name';
    $otherName  = $fieldName . '_other';
    $fieldLabel = $label ?? 'University Name';
    $isRequired = $required ?? false;
    $currentVal = $value ?? old($fieldName, '');
    $fieldError = $error ?? $errors->first($fieldName);
    $placeholder = $placeholder ?? 'Select university...';

    $groups = [
        'Research Universities' => [
            'Universiti Malaya (UM)',
            'Universiti Kebangsaan Malaysia (UKM)',
            'Universiti Putra Malaysia (UPM)',
            'Universiti Sains Malaysia (USM)',
            'Universiti Teknologi Malaysia (UTM)',
        ],
        'Public Universities' => [
            'Universiti Teknologi MARA (UiTM)',
            'Universiti Islam Antarabangsa Malaysia (UIAM / IIUM)',
            'Universiti Utara Malaysia (UUM)',
            'Universiti Pendidikan Sultan Idris (UPSI)',
            'Universiti Sains Islam Malaysia (USIM)',
            'Universiti Malaysia Sabah (UMS)',
            'Universiti Malaysia Sarawak (UNIMAS)',
            'Universiti Malaysia Pahang Al-Sultan Abdullah (UMPSA)',
            'Universiti Sultan Zainal Abidin (UniSZA)',
            'Universiti Malaysia Kelantan (UMK)',
            'Universiti Malaysia Terengganu (UMT)',
            'Universiti Malaysia Perlis (UniMAP)',
            'Universiti Tun Hussein Onn Malaysia (UTHM)',
            'Universiti Teknikal Malaysia Melaka (UTeM)',
            'Universiti Pertahanan Nasional Malaysia (UPNM)',
        ],
        'Private Universities' => [
            'Multimedia University (MMU)',
            'Universiti Tenaga Nasional (UNITEN)',
            'Universiti Telekom (UniTel)',
            "Taylor's University",
            'HELP University',
            'Asia Pacific University of Technology & Innovation (APU)',
            'UCSI University',
            'Management & Science University (MSU)',
            'Sunway University',
            'Heriot-Watt University Malaysia',
            'Monash University Malaysia',
            'University of Nottingham Malaysia',
            'University of Southampton Malaysia',
            'Curtin University Malaysia',
            'Swinburne University of Technology Sarawak',
            'Newcastle University Medicine Malaysia (NUMed)',
            'Xiamen University Malaysia Campus (XMUM)',
            'Tunku Abdul Rahman University of Management & Technology (TAR UMT)',
            'SEGi University',
            'MAHSA University',
            'Lincoln University College',
            'Quest International University (QIU)',
            'KDU University College',
            'Inti International University',
            'International Medical University (IMU)',
            'Perdana University',
            'AIMST University',
            'Open University Malaysia (OUM)',
            'Wawasan Open University (WOU)',
            'City University Malaysia',
            'Infrastructure University Kuala Lumpur (IUKL)',
            'Manipal International University (MIU)',
            'Asia e University (AeU)',
            'Albukhary International University (AIU)',
            'Binary University of Management & Entrepreneurship',
            'Limkokwing University of Creative Technology',
            'University of Cyberjaya',
            'Universiti Poly-Tech Malaysia (UPTM)',
            'First City University College',
        ],
    ];

    // Determine if stored value is a custom/other entry (not in predefined list)
    $allUnis = collect($groups)->flatten()->all();
    $isOther = $currentVal !== '' && !in_array($currentVal, $allUnis);
    $initSelect = $isOther ? '__other__' : $currentVal;
    $initOther  = $isOther ? $currentVal : old($fieldName . '_other', '');
@endphp

<div
    x-data="{
        sel: {{ Js::from($initSelect) }},
        other: {{ Js::from($initOther) }},
        get finalVal() { return this.sel === '__other__' ? this.other : this.sel; }
    }"
    class="space-y-1.5"
>
    {{-- Label --}}
    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary">
        {{ $fieldLabel }}@if($isRequired)<span class="text-red-500 ml-0.5">*</span>@endif
    </label>

    {{-- Hidden input that submits the real value --}}
    <input type="hidden" name="{{ $fieldName }}" :value="finalVal" @if($isRequired) x-bind:required="!finalVal" @endif>

    {{-- Select --}}
    <select
        x-model="sel"
        @change="if (sel !== '__other__') other = ''"
        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent transition-colors
               {{ $fieldError ? 'border-danger bg-danger/5' : 'border-border dark:border-dark-border bg-white dark:bg-dark-card text-primary dark:text-dark-primary' }}"
    >
        <option value="" disabled>{{ $placeholder }}</option>

        @foreach($groups as $group => $unis)
            <optgroup label="{{ $group }}">
                @foreach($unis as $uni)
                    <option value="{{ $uni }}">{{ $uni }}</option>
                @endforeach
            </optgroup>
        @endforeach

        <optgroup label="─────────────">
            <option value="__other__">Other (please specify)</option>
        </optgroup>
    </select>

    {{-- "Other" free-text input --}}
    <div x-show="sel === '__other__'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display:none">
        <div class="mt-1.5">
            <label class="block text-sm font-medium text-primary dark:text-dark-primary mb-1">
                Please specify university / institution name @if($isRequired)<span class="text-red-500">*</span>@endif
            </label>
            <input
                type="text"
                x-model="other"
                :required="sel === '__other__'"
                placeholder="e.g. University of Oxford"
                class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 dark:placeholder-dark-secondary/50 focus:border-accent dark:focus:border-dark-accent focus:ring-1 focus:ring-accent/30 dark:focus:ring-dark-accent/30 outline-none transition-colors"
            >
        </div>
    </div>

    @if($fieldError)
        <p class="text-xs text-danger mt-1">{{ $fieldError }}</p>
    @endif
</div>
