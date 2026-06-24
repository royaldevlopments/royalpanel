@extends('layouts.royal', ['navbar' => 'advanced', 'sideEditor' => false])

@section('title')
    Royal Advanced
@endsection

@section('content')
    <form action="{{ route('admin.royal.advanced') }}" method="POST" class="content-box">
        <div class="header">
            <p>Advanced settings</p>
            <span class="description-text">Change Royal advanced settings.</span>
        </div>

        <x-royal.form-wrapper 
            title="Customize Royal Theme" 
            description="Change Royal advanced settings."
        >
            <x-royal.input-field 
                id="royal:copyright" 
                :value="$copyright" 
                label="Copyright Text"
            />
            <div class="input-field">
                <label for="royal:defaultMode">Default mode</label>
                <select
                    id="royal:defaultMode"
                    name="royal:defaultMode"
                >
                    <option value="darkmode">Darkmode</option>
                    <option value="lightmode" @if(old('royal:defaultMode', $defaultMode) == 'lightmode') selected @endif>Lightmode</option>
                </select>
            </div>
            <div class="input-field">
                <label for="royal:searchComponent">Search bar</label>
                <select
                    id="royal:searchComponent"
                    name="royal:searchComponent"
                >
                    <option value="1" @if(old('royal:searchComponent', $searchComponent) == 1) selected @endif>Server select bar</option>
                    <option value="2" @if(old('royal:searchComponent', $searchComponent) == 2) selected @endif>Searchbar</option>
                </select>
                <small>Choose between server select bar or search bar in the sidebar.</small>
            </div>
            <div class="input-field">
                <label for="royal:profileType">Profile Style</label>
                <select
                    id="royal:profileType"
                    name="royal:profileType"
                >
                    <option value="boring">Boring Avatars</option>
                    <option value="avataaars" @if(old('royal:profileType', $profileType) == 'avataaars') selected @endif>Avataaars Neutral</option>
                    <option value="bottts" @if(old('royal:profileType', $profileType) == 'bottts') selected @endif>Bottts Neutral</option>
                    <option value="identicon" @if(old('royal:profileType', $profileType) == 'identicon') selected @endif>Identicon</option>
                    <option value="initials" @if(old('royal:profileType', $profileType) == 'initials') selected @endif>Initials</option>
                    <option value="gravatar" @if(old('royal:profileType', $profileType) == 'gravatar') selected @endif>Gravatar</option>
                </select>
            </div>
            <hr />
            <x-royal.switch 
                id="royal:lowResourcesAlert"
                name="royal:lowResourcesAlert"
                :value="$lowResourcesAlert"
                label="Low Resources Alert"
            />
            <x-royal.input-field 
                id="royal:alertLink" 
                :value="$alertLink" 
                label="Low Resources Alert Link"
                helpText="The link users will be directed to when clicking the 'Upgrade Server' button in the low resources alert."
            />
            <x-royal.switch
                id="royal:ipFlag"
                name="royal:ipFlag"
                :value="$ipFlag"
                label="IP Flag"
            />
            <x-royal.switch
                id="royal:modeToggler"
                name="royal:modeToggler"
                :value="$modeToggler"
                label="Dark/light mode Toggler"
            />
            <x-royal.switch
                id="royal:langSwitch"
                name="royal:langSwitch"
                :value="$langSwitch"
                label="Language Switcher"
            />
            @php
                $languageOptionsArray = json_decode($languageOptions, true) ?? [['key' => 'en', 'name' => 'English']];
                $activeLanguageKeys = array_column($languageOptionsArray, 'key');
            @endphp

            <script>const languages = @json($activeLanguageKeys);</script>

            <div class="input-field">
                <label for="royal:defaultLang">Default Language</label>
                <select
                    id="royal:defaultLang"
                    name="royal:defaultLang"
                >
                    @foreach($languageOptionsArray as $lang)
                        <option value="{{ $lang['key'] }}" @if(old('royal:defaultLang', $defaultLang) == $lang['key']) selected @endif>{{ $lang['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="input-field">
                <label>Available Languages</label>
                <div class="languages-options">
                    @foreach($languages as $key => $value)
                        <label>
                            <input 
                                type="checkbox" 
                                name="royal:languageOptions[]" 
                                value="{{ $key }}" 
                                @if(in_array($key, $activeLanguageKeys)) checked @endif
                            />
                            <span></span>
                            {{ $value }}
                        </label>
                    @endforeach
                </div>
            </div>

            <x-royal.switch
                id="royal:dashboardPage"
                name="royal:dashboardPage"
                :value="$dashboardPage"
                label="Dashboard Page"
            />
            <x-royal.switch
                id="royal:registration"
                name="royal:registration"
                :value="$registration"
                label="User Registration"
            />
            <hr />
            <hr />
            <p class="subtitle">Discord Bot</p>
            <x-royal.input-field
                id="royal:discordBotToken"
                :value="$discordBotToken"
                label="Discord Bot Token"
                helpText="From Discord Developer Portal → Bot → Token."
            />
            <x-royal.input-field
                id="royal:discordGuildId"
                :value="$discordGuildId"
                label="Discord Guild ID"
                helpText="Right-click your server → Copy ID."
            />
            <x-royal.input-field
                id="royal:discordAdminRoleId"
                :value="$discordAdminRoleId"
                label="Admin Role ID"
                helpText="Users with this role can use admin commands. Leave empty for server admins only."
            />
            <x-royal.input-field
                id="royal:botToken"
                :value="$botToken"
                label="Panel Bot API Token"
                helpText="Auto-generated. Keep secret."
            />
            <x-royal.switch
                id="royal:enforceDiscordLink"
                name="royal:enforceDiscordLink"
                :value="$enforceDiscordLink"
                label="Enforce Discord Link"
                helpText="Show banner to unlinked users."
            />
        </x-royal.form-wrapper>

        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
    <div class="header">
        <p>Royal Presets</p>
        <span class="description-text">Import and export presets.</span>
    </div>
    <form action="{{ route('admin.royal.advanced.preset') }}" method="POST" id="royal-preset-form" style="margin-top:40px;">
            {!! csrf_field() !!}
        <x-royal.form-wrapper 
            title="Import / Export Royal Preset"
            description="Export or import Royal advanced settings presets."
        >
            <p>Keep in mind: although we try to keep our products safe with input sanitization, importing presets may still break your installation. Only use presets from people you trust or from official sources (such as our GitHub repository).</p>

            <div style="display:flex; gap:10px;">
                <button type="button" class="button button-primary" onclick="exportRoyalPreset()">
                    Export preset
                </button>

                <button type="button" class="button button-secondary" onclick="document.getElementById('preset-file-input').click()">
                    Import preset
                </button>

                <!-- Hidden file input -->
                <input type="file" id="preset-file-input" accept=".royal" style="display:none" />
                <input type="hidden" id="royal-preset-json" name="preset_json" value='@json($royalSettings)' />
            </div>
        </x-royal.form-wrapper>
    </form>

<script>
    // EXPORT FUNCTION
    function exportRoyalPreset() {
        const json = document.getElementById('royal-preset-json').value.trim();
        const blob = new Blob([json], { type: "application/json" });
        const url = URL.createObjectURL(blob);

        const a = document.createElement("a");
        a.href = url;
        a.download = "preset.royal";
        a.click();

        URL.revokeObjectURL(url);
    }

    // IMPORT FUNCTION
    document.getElementById("preset-file-input").addEventListener("change", function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            try {
                const text = e.target.result;

                // Optional: validate JSON
                JSON.parse(text);

                // Put JSON into textarea
                document.getElementById("royal-preset-json").value = text;

                // Auto-submit form
                document.getElementById("royal-preset-form").submit();
            } catch (err) {
                alert("Invalid preset file: not valid JSON.");
            }
        };
        reader.readAsText(file);
    });
</script>
@endsection